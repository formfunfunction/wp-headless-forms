<?php
/**
 * REST API Controller
 *
 * @package WPHeadlessForms
 * @since   0.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * WPHF REST API Class
 *
 * @since   0.0.1
 */
class WPHF_REST_Controller {

	/**
	 * Endpoint namespace for WP_REST_API
	 *
	 * @var String
	 */
	public $namespace = '/forms/v1';

	/**
	 * API Endpoint
	 *
	 * @var String
	 */
	public $resource_name = 'forms';

	/**
	 * Constructor. Empty for now.
	 */
	public function __construct() {}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 0.0.1
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->resource_name,
			array(
				// Here we register the readable endpoint for collections.
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_forms' ),
					'permission_callback' => array( $this, 'get_forms_permissions_check' ),
				),
				// Register our schema callback.
				'schema' => array( $this, 'get_form_schema' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->resource_name . '/(?P<id>[\d]+)',
			array(
				// Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_form' ),
					'permission_callback' => array( $this, 'get_form_permissions_check' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'submit_form' ),
					'permission_callback' => array( $this, 'submit_form_permissions_check' ),
				),
				// Register our schema callback.
				'schema' => array( $this, 'get_form_schema' ),
			)
		);
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function submit_form_permissions_check( $request ) {
		return true;
	}

	/**
	 * Verifies and reates a new form submission.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function submit_form( $request ) {
		$id = (int) $request['id'];

		$args = $request->get_params();

		$wphf_instance = WPHeadlessForms::get_instance();

		$form = $wphf_instance->get_form( $id );

		if ( empty( $form ) ) {
			return rest_ensure_response( array() );
		}

		$response = $form->submit( $args );

		// Return all of our post response data.
		return rest_ensure_response( $response );
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_forms_permissions_check( $request ) {
		return true;
	}

	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_forms( $request ) {
		$instance = WPHeadlessForms::get_instance();
		$forms    = $instance->get_forms();

		$data = array();

		if ( empty( $forms ) ) {
			return rest_ensure_response( $data );
		}

		foreach ( $forms as $form ) {
			$response = $this->prepare_form_for_response( $form, $request );
			$data[]   = $this->prepare_response_for_collection( $response );
		}

		// Return all of our comment response data.
		return rest_ensure_response( $data );
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_form_permissions_check( $request ) {
		return true;
	}

	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_form( $request ) {
		$instance = WPHeadlessForms::get_instance();

		$id   = (int) $request['id'];
		$form = $instance->get_form( $id );

		if ( empty( $form ) ) {
			return rest_ensure_response( array() );
		}

		$response = $this->prepare_form_for_response( $form, $request );

		return $response;
	}

	/**
	 * Matches the post data to the schema we want.
	 *
	 * @param WPHF_Form       $form The WPHF_Form object being prepared.
	 * @param WP_REST_Request $request Current request.
	 */
	public function prepare_form_for_response( $form, $request ) {
		$post_data = array();

		$schema = $this->get_form_schema( $request );

		if ( isset( $schema['properties']['id'] ) ) {
			$post_data['id'] = $form->get_id();
		}

		if ( isset( $schema['properties']['fields'] ) ) {
			$post_data['fields'] = $form->get_fields();
		}

		if ( isset( $schema['properties']['nonce'] ) ) {
			$post_data['nonce'] = $form->get_nonce();
		}

		return rest_ensure_response( $post_data );
	}

	/**
	 * Prepare a response for inserting into collection of forms.
	 *
	 * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
	 *
	 * @param WP_REST_Response $response Response object.
	 * @return array Response data, ready for insertion into collection data.
	 */
	public function prepare_response_for_collection( $response ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}

		$data   = (array) $response->get_data();
		$server = rest_get_server();

		if ( method_exists( $server, 'get_compact_response_links' ) ) {
			$links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
		} else {
			$links = call_user_func( array( $server, 'get_response_links' ), $response );
		}

		if ( ! empty( $links ) ) {
			$data['_links'] = $links;
		}

		return $data;
	}

	/**
	 * Get our sample schema for a post.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_form_schema( $request ) {
		$schema = array(
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => 'post',
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => array(
				'id'     => array(
					'description' => esc_html__( 'Unique identifier for the form.', 'fff-rest-contact-form' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'fields' => array(
					'description' => esc_html__( 'Fields registered in the form.', 'fff-rest-contact-form' ),
					'type'        => 'array',
				),
				'nonce'  => array(
					'description' => esc_html__( 'Nonce for validation.', 'fff-rest-contact-form' ),
					'type'        => 'string',
				),
			),
		);

		return $schema;
	}
}
