<?php
/**
 * XPress_MVC_Server class
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Thiago Benvenuto
 * @license    GPLv2
 * @since      0.1.0
 */

/**
 * Core class used to implement the XPress MVC server. Inspired by WP REST API Server class.
 *
 * @since 0.1.0
 */
class XPress_MVC_Server {

	/**
	 * Alias for GET transport method.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	const READABLE = 'GET';

	/**
	 * Alias for POST transport method.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	const CREATABLE = 'POST';

	/**
	 * Alias for POST, PUT, PATCH transport methods together.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	const EDITABLE = 'POST, PUT, PATCH';

	/**
	 * Alias for DELETE transport method.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	const DELETABLE = 'DELETE';

	/**
	 * Alias for GET, POST, PUT, PATCH & DELETE transport methods together.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	const ALLMETHODS = 'GET, POST, PUT, PATCH, DELETE';

	/**
	 * Endpoints registered to the server.
	 *
	 * @since 0.1.0
	 * @var array
	 */
	protected $endpoints = array();

	/**
	 * Options defined for the routes.
	 *
	 * @since 0.1.0
	 * @var array
	 */
	protected $route_options = array();

	/**
	 * Routes registered by their route ids.
	 *
	 * @since 0.2.0
	 * @var array
	 */
	protected $route_ids = array();

	/**
	 * Instantiates the XPress server.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
	}

	/**
	 * Converts an error to a response object.
	 *
	 * This iterates over all error codes and messages to change it into a flat
	 * array. This enables simpler client behaviour, as it is represented as a
	 * list in JSON rather than an object/map.
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Error $error WP_Error instance.
	 * @return XPress_MVC_Response List of associative arrays with code and message keys.
	 */
	protected function error_to_response( $error ) {
		$error_data = $error->get_error_data();

		if ( is_array( $error_data ) && isset( $error_data['status'] ) ) {
			$status = $error_data['status'];
		} else {
			$status = 500;
		}

		$response = new XPress_MVC_Response( $error, $status );

		return $response;
	}

	public function get_clean_path() {
		global $wp_rewrite;

		$path = '/';

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$url_elements = parse_url( $_SERVER['REQUEST_URI'] );
			$request_path = $url_elements['path'] ?: '/';

			$path = str_replace( $wp_rewrite->index . '/', '', untrailingslashit( $request_path ) );

			// Compare raw option with filtered home_url to check for prefix added by plugins like multilanguage support, etc.
			$home_original = get_option( 'home' );
			$home_filtered = home_url();

			if ( $home_original !== $home_filtered ) {
				// If the raw is different than filtered, get the delta.
				$extra_segment = untrailingslashit( str_replace( $home_original, '', $home_filtered ) );

				// If delta is in begin of string, remove it.
				$position = strpos( $path, $extra_segment );
				if ( $position === 0 ) {
					$path = substr( $path, mb_strlen( $extra_segment ) );
				}
			}
		}

		return apply_filters( 'xpress_mvc_clean_path', $path, $url_elements['path'] );
	}

	/**
	 * Tries to server the request using one of the registered routes.
	 * If none are suitable, just give control back to WordPress.
	 *
	 * Matches the current server URI to a route and runs the first matching
	 * callback.
	 *
	 * @since 0.1.0
	 *
	 * @see XPress_MVC_Server::dispatch()
	 *
	 * @param string $path Optional. The request route. If not set, the route will be guessed from $_SERVER.
	 *                     Default null.
	 * @return false|null Null if not served and a HEAD request, false otherwise.
	 */
	public function serve_request( $path = null ) {
		global $template, $wp_query;

		if ( empty( $path ) ) {
			$path = $this->get_clean_path();
		}

		$request = new XPress_MVC_Request( $_SERVER['REQUEST_METHOD'], $path );

		$request->set_query_params( wp_unslash( $_GET ) );
		$request->set_body_params( wp_unslash( $_POST ) );
		$request->set_file_params( $_FILES );
		$request->set_headers( $this->get_headers( wp_unslash( $_SERVER ) ) );
		$request->set_body( $this->get_raw_data() );

		/*
		 * HTTP method override for clients that can't use PUT/PATCH/DELETE. First, we check
		 * $_GET['_method']. If that is not set, we check for the HTTP_X_HTTP_METHOD_OVERRIDE
		 * header.
		 */
		if ( isset( $_REQUEST['_method'] ) ) {
			$request->set_method( $_REQUEST['_method'] );
		} elseif ( isset( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ) {
			$request->set_method( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );
		}

		// Dispatch the request.
		$result = $this->dispatch( $request );

		// No route found that matches the $path. Return control to WordPress.
		if ( $result instanceof XPress_MVC_No_Route ) {
			return;
		}

		// Normalize to either WP_Error or XPress_MVC_Response...
		$result = xpress_mvc_ensure_response( $result );

		/**
		 * Filters the XPress MVC response.
		 *
		 * Allows modification of the response before returning.
		 *
		 * @since 0.1.0
		 *
		 * @param XPress_MVC_Response  $result  Result to send to the client. Usually a XPress_MVC_Response.
		 * @param XPress_MVC_Server $this    Server instance.
		 * @param WP_REST_Request   $request Request used to generate the response.
		 */
		$result = xpress_mvc_ensure_response( apply_filters( 'xpress_mvc_post_dispatch', $result, $this, $request ) );

		// Send extra data from response objects.
		$headers = $result->get_headers();
		$this->send_headers( $headers );

		$code = $result->get_status();
		if ( $code == 404 ) {
			// If 404, sets 404 in main query to ensure 404 http status is sent.
			$wp_query->query_vars['error'] = '404';
			$wp_query->query_vars['name'] = '';
			$wp_query->is_404 = true;
		}
		$this->set_status( $code );

		// Creates the view model global to be consumed by the templates.
		$GLOBALS['xpress_viewmodel'] = $result->get_data();

		// Define the template to be rendered.
		$template_name = $result->template ?: $result->get_matched_handler()['route_id'];
		$template = $this->get_template( $template_name );
		$GLOBALS['xpress_template'] = $template;

		// Simple filter to ensure our template is rendered by the template loader.
		add_filter( 'template_include', function( $template ) {
			global $xpress_template;

			if ( isset( $xpress_template ) && ! empty( $xpress_template ) ) {
				$template = $xpress_template;
			}

			return $template;
		}, 9999 );

		// Delegate rendering of template back to WordPress system.
		require_once( ABSPATH . WPINC . '/template-loader.php' );
		die();
	}

	/**
	 * Gets the template file to be rendered as the view for the request.
	 *
	 * @param  string $template_name The name of the template file, without folder or extension.
	 * @return string                Full path of the template to be rendered.
	 */
	public function get_template( $template_name ) {
		/**
		 * Filters the path where to look for templates.
		 */
		$paths = apply_filters( 'xpress_mvc_template_paths', array( 'xpress/templates' ) );

		// Remove extension, just in case.
		$template_name = str_replace( '.php', '', $template_name );

		// Builds the array of options of templates to look for.
		$templates = array();
		foreach ( $paths as $path ) {
			$templates[] = trailingslashit( $path ) . $template_name . '.php' ;
		}

		// Always check theme root.
		$templates[] = $template_name . '.php' ;

		// Let WordPress system find the best suitable template for us.
		$template = get_query_template( $template_name, $templates );

		// Show a notice to the developer if the templates are missing.
		if ( empty( $template ) ) {
			$template_list = '[ ' . join( ', ', $templates ) . ' ]';
			if ( defined( 'WP_ENV' ) && WP_ENV === 'development' ) {
				_doing_it_wrong( 'XPress_MVC_Server->get_template()', __( sprintf( 'Templates %s are missing.', $template_list ) ), '0.1.0' );
			}
			return false;
		}

		return $template;
	}

	/**
	 * Registers a route to the server.
	 *
	 * @since 0.1.0
	 *
	 * @param string $route_id   The route ID.
	 * @param string $route      The REST route.
	 * @param array  $args Route arguments.
	 * @param bool   $override   Optional. Whether the route should be overridden if it already exists.
	 *                           Default false.
	 */
	public function register_route( $route_id, $route, $args, $override = false ) {
		if ( empty( $route_id ) ) {
			_doing_it_wrong( 'xpress_register_route', __( 'Routes must be have a unique identifier.' ), '0.1.0' );
			return false;
		}

		if ( empty( $route ) ) {
			_doing_it_wrong( 'xpress_register_route', __( 'Route must be specified.' ), '0.1.0' );
			return false;
		}

		if ( isset( $args['args'] ) ) {
			$common_args = $args['args'];
			unset( $args['args'] );
		} else {
			$common_args = array();
		}

		if ( isset( $args['callback'] ) ) {
			// Upgrade a single set to multiple.
			$args = array( $args );
		}

		$defaults = array(
			'route_id' => $route_id,
			'methods'  => 'GET',
			'callback' => null,
			'args'     => array(),
		);
		foreach ( $args as $key => &$arg_group ) {
			if ( ! is_numeric( $key ) ) {
				// Route option, skip here.
				continue;
			}

			$arg_group = array_merge( $defaults, $arg_group );
			$arg_group['args'] = array_merge( $common_args, $arg_group['args'] );
		}

		$route = '/' . trim( $route, '/' );

		if ( $override || ! array_key_exists( $route, $this->endpoints ) || empty( $this->endpoints[ $route ] ) ) {
			$this->endpoints[ $route ] = $args;
		} else {
			$this->endpoints[ $route ] = array_merge( $this->endpoints[ $route ], $args );
		}

		$this->route_ids[ $route_id ] = $route;
	}

	/**
	 * Unregisters a route
	 *
	 * @since 0.2.0
	 *
	 * @param string $route_id   The route ID.
	 */
	function unregister_route( $route_id ) {
		foreach ( $this->endpoints as $path => $routes ) {
			foreach ($routes as $key => $route ) {
				if ( $route_id == $route['route_id'] ) {
					unset( $this->endpoints[ $path ][ $key ] );
				}
			}
			if ( 0 === count( $this->endpoints[ $path ] ) ) {
				unset( $this->endpoints[ $path ] );
			}
		}
		if ( isset( $this->route_ids[ $route_id ] ) ) {
			unset( $this->route_ids[ $route_id ] );
		}
	}

	/**
	 * Retrieves the route map.
	 *
	 * The route map is an associative array with path regexes as the keys. The
	 * value is an indexed array with the callback function/method as the first
	 * item, and a bitmask of HTTP methods as the second item (see the class
	 * constants).
	 *
	 * Each route can be mapped to more than one callback by using an array of
	 * the indexed arrays. This allows mapping e.g. GET requests to one callback
	 * and POST requests to another.
	 *
	 * Note that the path regexes (array keys) must have @ escaped, as this is
	 * used as the delimiter with preg_match()
	 *
	 * @since 0.1.0
	 *
	 * @return array `'/path/regex' => array( $callback, $bitmask )` or
	 *               `'/path/regex' => array( array( $callback, $bitmask ), ...)`.
	 */
	public function get_routes() {

		/**
		 * Filters the array of available endpoints.
		 *
		 * @since 0.1.0
		 *
		 * @param array $endpoints The available endpoints. An array of matching regex patterns, each mapped
		 *                         to an array of callbacks for the endpoint. These take the format
		 *                         `'/path/regex' => array( $callback, $bitmask )` or
		 *                         `'/path/regex' => array( array( $callback, $bitmask ).
		 */
		$endpoints = apply_filters( 'xpress_mvc_endpoints', $this->endpoints );

		// Normalise the endpoints.
		$defaults = array(
			'methods'       => '',
			'accept_json'   => false,
			'accept_raw'    => false,
			'show_in_index' => true,
			'args'          => array(),
		);

		foreach ( $endpoints as $route => &$handlers ) {

			if ( isset( $handlers['callback'] ) ) {
				// Single endpoint, add one deeper.
				$handlers = array( $handlers );
			}

			if ( ! isset( $this->route_options[ $route ] ) ) {
				$this->route_options[ $route ] = array();
			}

			foreach ( $handlers as $key => &$handler ) {

				if ( ! is_numeric( $key ) ) {
					// Route option, move it to the options.
					$this->route_options[ $route ][ $key ] = $handler;
					unset( $handlers[ $key ] );
					continue;
				}

				$handler = wp_parse_args( $handler, $defaults );

				// Allow comma-separated HTTP methods.
				if ( is_string( $handler['methods'] ) ) {
					$methods = explode( ',', $handler['methods'] );
				} elseif ( is_array( $handler['methods'] ) ) {
					$methods = $handler['methods'];
				} else {
					$methods = array();
				}

				$handler['methods'] = array();

				foreach ( $methods as $method ) {
					$method = strtoupper( trim( $method ) );
					$handler['methods'][ $method ] = true;
				}
			}
		} // End foreach().

		return $endpoints;
	}

	/**
	 * Matches the request to a callback and call it.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Request to attempt dispatching.
	 * @return XPress_MVC_Response Response returned by the callback.
	 */
	public function dispatch( $request ) {
		/**
		 * Filters the pre-calculated result of a dispatch request.
		 *
		 * Allow hijacking the request before dispatching by returning a non-empty. The returned value
		 * will be used to serve the request instead.
		 *
		 * @since 0.1.0
		 *
		 * @param mixed             $result  Response to replace the requested version with. Can be anything
		 *                                   a normal endpoint can return, or null to not hijack the request.
		 * @param XPress_MVC_Server $this    Server instance.
		 * @param WP_REST_Request   $request Request used to generate the response.
		 */
		$result = apply_filters( 'xpress_mvc_pre_dispatch', null, $this, $request );

		if ( ! empty( $result ) ) {
			return $result;
		}

		$method = $request->get_method();
		$path   = $request->get_route();

		foreach ( $this->get_routes() as $route => $handlers ) {
			$match = preg_match( '@^' . $route . '$@i', $path, $matches );

			if ( ! $match ) {
				continue;
			}

			$args = array();
			foreach ( $matches as $param => $value ) {
				if ( ! is_int( $param ) ) {
					$args[ $param ] = $value;
				}
			}

			foreach ( $handlers as $handler ) {
				$callback  = $handler['callback'];
				$response = null;

				// Fallback to GET method if no HEAD method is registered.
				$checked_method = $method;
				if ( 'HEAD' === $method && empty( $handler['methods']['HEAD'] ) ) {
					$checked_method = 'GET';
				}
				if ( empty( $handler['methods'][ $checked_method ] ) ) {
					continue;
				}

				// Convert Controller->method syntax to valid callable
				$callback = $this->make_callable( $callback );

				if ( ! is_callable( $callback ) ) {
					$response = new WP_Error( 'rest_invalid_handler', __( 'The handler for the route is invalid' ), array(
						'status' => 500,
					) );
				}

				if ( ! is_wp_error( $response ) ) {
					// Remove the redundant preg_match argument.
					unset( $args[0] );

					$request->set_url_params( $args );
					$request->set_attributes( $handler );

					$defaults = array();

					foreach ( $handler['args'] as $arg => $options ) {
						if ( isset( $options['default'] ) ) {
							$defaults[ $arg ] = $options['default'];
						}
					}

					$request->set_default_params( $defaults );
					$request->process_params();
				}

				/**
				 * Filters the response before executing any REST API callbacks.
				 *
				 * Allows plugins to perform additional validation after a
				 * request is initialized and matched to a registered route,
				 * but before it is executed.
				 *
				 * Note that this filter will not be called for requests that
				 * fail to authenticate or match to a registered route.
				 *
				 * @since 0.1.0
				 *
				 * @param XPress_MVC_Response  $response Result to send to the client. Usually a XPress_MVC_Response.
				 * @param XPress_MVC_Server $handler  ResponseHandler instance.
				 * @param WP_REST_Request   $request  Request used to generate the response.
				 */
				$response = apply_filters( 'xpress_mvc_request_before_callbacks', $response, $handler, $request );

				if ( ! is_wp_error( $response ) ) {
					// Check permission specified on the route.
					if ( ! empty( $handler['permission_callback'] ) ) {
						$permission = call_user_func( $handler['permission_callback'], $request );

						if ( is_wp_error( $permission ) ) {
							$response = $permission;
						} elseif ( false === $permission || null === $permission ) {
							$response = new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to do that.' ), array(
								'status' => 403,
							) );
						}
					}
				}

				if ( ! is_wp_error( $response ) ) {
					/**
					 * Filters the REST dispatch request result.
					 *
					 * Allow plugins to override dispatching the request.
					 *
					 * @since 0.1.0
					 *
					 * @param bool            $dispatch_result Dispatch result, will be used if not empty.
					 * @param WP_REST_Request $request         Request used to generate the response.
					 * @param string          $route           Route matched for the request.
					 * @param array           $handler         Route handler used for the request.
					 */
					$dispatch_result = apply_filters( 'xpress_mvc_dispatch_request', null, $request, $route, $handler );

					// Allow plugins to halt the request via this filter.
					if ( null !== $dispatch_result ) {
						$response = $dispatch_result;
					} else {
						$response = call_user_func( $callback, $request );
					}
				}

				/**
				 * Filters the response immediately after executing any REST API
				 * callbacks.
				 *
				 * Allows plugins to perform any needed cleanup, for example,
				 * to undo changes made during the {@see 'xpress_mvc_request_after_callbacks'}
				 * filter.
				 *
				 * Note that this filter will not be called for requests that
				 * fail to authenticate or match to a registered route.
				 *
				 * Note that an endpoint's `permission_callback` can still be
				 * called after this filter - see `rest_send_allow_header()`.
				 *
				 * @since 0.1.0
				 *
				 * @param XPress_MVC_Response  $response Result to send to the client. Usually a XPress_MVC_Response.
				 * @param XPress_MVC_Server $handler  ResponseHandler instance.
				 * @param WP_REST_Request   $request  Request used to generate the response.
				 */
				$response = apply_filters( 'xpress_mvc_request_after_callbacks', $response, $handler, $request );

				if ( is_wp_error( $response ) ) {
					$response = $this->error_to_response( $response );
				} else {
					$response = xpress_mvc_ensure_response( $response );
				}

				$response->set_matched_route( $route );
				$response->set_matched_handler( $handler );

				return $response;
			} // End foreach().
		} // End foreach().

		return new XPress_MVC_No_Route;
	}

	/**
	 * Sends an HTTP status code.
	 *
	 * @since 0.1.0
	 *
	 * @param int $code HTTP status.
	 */
	protected function set_status( $code ) {
		status_header( $code );
	}

	/**
	 * Sends an HTTP header.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key Header key.
	 * @param string $value Header value.
	 */
	public function send_header( $key, $value ) {
		/*
		 * Sanitize as per RFC2616 (Section 4.2):
		 *
		 * Any LWS that occurs between field-content MAY be replaced with a
		 * single SP before interpreting the field value or forwarding the
		 * message downstream.
		 */
		$value = preg_replace( '/\s+/', ' ', $value );
		header( sprintf( '%s: %s', $key, $value ) );
	}

	/**
	 * Sends multiple HTTP headers.
	 *
	 * @since 0.1.0
	 *
	 * @param array $headers Map of header name to header value.
	 */
	public function send_headers( $headers ) {
		foreach ( $headers as $key => $value ) {
			$this->send_header( $key, $value );
		}
	}

	/**
	 * Removes an HTTP header from the current response.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key Header key.
	 */
	public function remove_header( $key ) {
		if ( function_exists( 'header_remove' ) ) {
			// In PHP 5.3+ there is a way to remove an already set header.
			header_remove( $key );
		} else {
			// In PHP 5.2, send an empty header, but only as a last resort to
			// override a header already sent.
			foreach ( headers_list() as $header ) {
				if ( 0 === stripos( $header, "$key:" ) ) {
					$this->send_header( $key, '' );
					break;
				}
			}
		}
	}

	/**
	 * Retrieves the raw request entity (body).
	 *
	 * @since 0.1.0
	 *
	 * @global string $HTTP_RAW_POST_DATA Raw post data.
	 *
	 * @return string Raw request data.
	 */
	public static function get_raw_data() {
		global $HTTP_RAW_POST_DATA;

		/*
		 * A bug in PHP < 5.2.2 makes $HTTP_RAW_POST_DATA not set by default,
		 * but we can do it ourself.
		 */
		if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
			$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
		}

		return $HTTP_RAW_POST_DATA;
	}

	/**
	 * Extracts headers from a PHP-style $_SERVER array.
	 *
	 * @since 0.1.0
	 *
	 * @param array $server Associative array similar to `$_SERVER`.
	 * @return array Headers extracted from the input.
	 */
	public function get_headers( $server ) {
		$headers = array();

		// CONTENT_* headers are not prefixed with HTTP_.
		$additional = array(
			'CONTENT_LENGTH' => true,
			'CONTENT_MD5'    => true,
			'CONTENT_TYPE'   => true,
		);

		foreach ( $server as $key => $value ) {
			if ( strpos( $key, 'HTTP_' ) === 0 ) {
				$headers[ substr( $key, 5 ) ] = $value;
			} elseif ( isset( $additional[ $key ] ) ) {
				$headers[ $key ] = $value;
			}
		}

		return $headers;
	}

	/**
	 * Builds a permalink for a given route id and an array of arguments.
	 *
	 * @since 0.1.0
	 *
	 * @param string $route_id  Route to build the permalink.
	 * @param array  $arguments An array where each argument is a key and contains the value to be used in the route url.
	 * @return string|null      The route permalink with the arguments populated or null if invalid $route_id.
	 */
	public function get_route_permalink( $route_id, $arguments = array() ) {
		if ( array_key_exists( $route_id, $this->route_ids ) && ! empty( $this->route_ids[ $route_id ] ) ) {
			$route = $this->route_ids[ $route_id ];
		} else {
			return null;
		}

		// Regex to find WP REST API parameters in route urls.
		// The parameter name is returned in group 2 of a match.
		$argument_regex = "/(\(\?P?[\<'](\w+)[\>'][^\/]+\))/";

		preg_match_all( $argument_regex, $route, $matches, PREG_SET_ORDER, 0 );

		// Replace found parameters with their equivalent value from $arguments.
		foreach ( $matches as $match ) {
			if ( count( $match ) < 3 )
				continue;

			$to_replace   = $match[0];
			$replace_with = $arguments[ $match[2] ] ?: $match[2];

			unset( $arguments[ $match[2] ] );

			$route = str_replace( $to_replace, $replace_with, $route );
		}

		// If still has values in $arguments, add them to the query string.
		if ( count( $arguments ) > 0 ) {
			$route = add_query_arg( $arguments, $route );
		}

		// Build the permalink.
		return site_url( $route );
	}

	/**
	 * Ensure Controller->method syntax is callable.
	 *
	 * @since 0.2.0
	 *
	 * @param callable $function  Callable to convert.
	 * @return callable      Converted callable.
	 */
	public function make_callable( $function ) {
		if ( false !== strpos( $function, '->' ) ) {
			$callable = explode( '->', $function );
			$class = new $callable[0];
			$method = $callable[1];
			$function = array( $class, $method );
		}
		return $function;
	}
}
