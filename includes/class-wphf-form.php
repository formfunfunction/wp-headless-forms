<?php
/**
 * Form object abstraction class
 *
 * @package WPHeadlessForms
 * @since   0.0.1
 */

/**
 * WPHF_Form class
 *
 * @since 0.0.1
 */
class WPHF_Form {
	/**
	 * ID of form - same as post_id
	 *
	 * @var Int
	 */
	public $id = null;

	/**
	 * Array of WPHF_Field objects assigned to form
	 *
	 * @var Array
	 */
	public $fields = null;

	/**
	 * Array of WPHF_Field objects assigned to form
	 *
	 * @var Array
	 */
	public $nonce = null;

	/**
	 * Original WP_Post object
	 *
	 * @var Array
	 */
	protected $wp_post_object = null;

	/**
	 * Constructor.
	 *
	 * @param WP_Post $post WP_Post object.
	 * @return WPHF_Form|bool Current form instance or false
	 */
	public function __construct( $post ) {
		if ( $post instanceof WP_Post ) {
			$this->id             = $post->ID;
			$this->wp_post_object = $post;

			$this->set_fields_array();
			$this->set_nonce();
		}
	}

	/**
	 * Returns the fields array.
	 *
	 * @return Array
	 * @since 0.0.1
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Gets the form data
	 *
	 * @return WPHF_Form
	 * @since 0.0.1
	 */
	public function get_form() {
		return $this;
	}

	/**
	 * Returns the post id.
	 *
	 * @return Int
	 * @since 0.0.1
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Field array setup
	 *
	 * @since 0.0.1
	 */
	protected function set_fields_array() {
		// Get fields from post.
		$fields = get_field( 'fields', $this->id );
		$fields = array_map(
			function ( $field, $id ) {
				if ( $field['acf_fc_layout'] ) {
					$field['field_type'] = $field['acf_fc_layout'];
					unset( $field['acf_fc_layout'] );
				}
				// Todo: make uniqid on field save rather than here.
				$field['id'] = $id;
				return $field;
			},
			$fields,
			array_keys( $fields )
		);

		$this->fields = $fields;
	}

	/**
	 * Create and set nonce
	 *
	 * @since 0.0.1
	 */
	public function set_nonce() {
		if ( empty( $this->nonce ) ) {
			$this->nonce = wp_create_nonce( 'fffcf_submission_' . $this->id );
		}
	}

	/**
	 * Get nonce
	 *
	 * @since 0.0.1
	 */
	public function get_nonce() {
		return $this->nonce;
	}

	/**
	 * Create and set nonce
	 *
	 * @param String $nonce WP nonce to validate.
	 * @since 0.0.1
	 */
	public function verify_nonce( $nonce ) {
		return wp_verify_nonce( $nonce, 'fffcf_submission_' . $this->id );
	}

	/**
	 * Create and set nonce
	 *
	 * @param Array $args Array of entries to submit.
	 * @since 0.0.1
	 */
	public function submit( $args ) {
		if ( empty( $args ) ) {
			return;
		}

		$submission = new WPHF_Submit( $args );

		if ( $submission->is_valid() ) {
			return $submission->send();
		} else {
			return $submission->prepare_response();
		}
	}
}
