<?php
/**
 * Handles form submissions & validations
 *
 * @package WPHeadlessForms
 * @since 0.0.1
 */

/**
 * Form submission class
 */
class WPHF_Submit {

	/**
	 * Nonce included in the submission
	 *
	 * @var String
	 */
	protected $nonce = null;

	/**
	 * Form object assigned to WPHeadlessForms instance
	 *
	 * @var WPHF_Form
	 */
	protected $form = null;

	/**
	 * Field values in new form entry
	 *
	 * @var Array
	 */
	protected $submitted_fields = null;

	/**
	 * Stores any validation errors
	 *
	 * @var Array
	 */
	protected $errors = null;

	/**
	 * Stores stanitized fields for entry into database and emails
	 *
	 * @var Array
	 */
	protected $clean_fields = array();

	/**
	 * Constructor.
	 *
	 * @param Array $args Array of submitted fields.
	 */
	public function __construct( $args ) {
		// Get form from global WPHeadlessForms instance.
		$this->form = WPHeadlessForms::get_instance()->form;

		// Check for nonce.
		if ( isset( $args['nonce'] ) ) {
			$this->nonce = $args['nonce'];
		}

		$this->submitted_fields = $args;

		$this->validate();
	}

	/**
	 * Validate an array of submitted form data
	 *
	 * @since 0.0.1
	 */
	protected function validate() {
		// Verify nonce.
		if ( ! $this->form->verify_nonce( $this->nonce ) ) {
			$this->prepare_error( 'nonce' );
			return false;
		}

		// Loop through fields in form object.
		foreach ( $this->form->fields as $field ) {
			// Check if the field exists in the submission.
			$exists = ! empty( $this->submitted_fields[ $field['name'] ] );
			$value  = null;

			// Check if field isn't supplied but required.
			if ( ! empty( $field['required'] ) && ! $exists ) {
				$this->prepare_error( 'required', $field );
			}

			// If email, validate email address.
			if ( $exists && 'email' === $field['field_type'] ) {
				$value = is_email( $this->submitted_fields[ $field['name'] ] );
				if ( ! $value ) {
					$this->prepare_error( 'invalid', $field );
				}
			}

			// If text field, sanatize.
			if ( $exists && 'text' === $field['field_type'] ) {
				$value = sanitize_text_field( $this->submitted_fields[ $field['name'] ] );
			}

			// If textarea field, sanatize.
			if ( $exists && 'text_area' === $field['field_type'] ) {
				$value = sanitize_textarea_field( $this->submitted_fields[ $field['name'] ] );
			}

			// Add clean field to array for submission.
			$this->clean_fields = array_merge( $this->clean_fields, array( $field['name'] => $value ) );
		}
	}

	/**
	 * Checks for any validation errors.
	 *
	 * @since 0.0.1
	 */
	public function is_valid() {
		if ( count( $this->errors ) > 0 ) {
			return false;
		}
		return true;
	}

	/**
	 * Returns any validation errors.
	 *
	 * @since 0.0.1
	 */
	public function get_validation_errors() {
		return $this->errors;
	}

	/**
	 * Creates array of helpful error information
	 *
	 * @param String $type Type of error.
	 * @param Array  $field Field array containing issue.
	 * @since 0.0.1
	 */
	public function prepare_error( $type, $field = null ) {
		$message = 'An unknown error occurred processing your form.';

		if ( 'required' === $type ) {
			$message = $field['name'] . ' is required.';
		}

		if ( 'nonce' === $type ) {
			$message = 'Nonce is not valid.';
		}

		if ( 'invalid' === $type ) {
			$message = $field['name'] . ' is not valid.';
		}

		if ( 'mail' === $type ) {
			$message = 'An error occured delivering the email. Please try again.';
		}

		$this->errors[] = array(
			'type'    => $type,
			'message' => $message,
			'field'   => $field,
		);
	}

	/**
	 * Actually create the form entries
	 *
	 * @since 0.0.1
	 */
	public function send() {
		if ( $this->is_valid() ) {
			$this->send_mail();
		}

		if ( $this->is_valid() ) {
			$this->create_post();
		}

		return $this->prepare_response();
	}

	/**
	 * Sends an email
	 *
	 * @since 0.0.1
	 */
	protected function send_mail() {
		$to_addresss = get_field( 'recipient', $this->form->id );

		add_filter( 'wp_mail_content_type', 'set_html_content_type' );
		$notification = wp_mail(
			$to_addresss,
			'Contact from submission from ' . $this->clean_fields['name'],
			wphf_notification_email_body( $this->clean_fields )
		);

		if ( ! $notification ) {
			$this->prepare_error( 'mail' );
			return;
		}

		$confirmation = wp_mail(
			$this->clean_fields['email'],
			'Thanks for your enquiry',
			wphf_confirmation_email_body( $this->clean_fields )
		);
		remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

		if ( ! $confirmation ) {
			$this->prepare_error( 'mail' );
		}
	}

	/**
	 * Create entry post in DB
	 */
	protected function create_post() {
		$post_data = array(
			'post_title' => 'Contact from submission from ' . $this->clean_fields['name'],
			'post_type'  => 'form-entries',
		);

		$post = wp_insert_post( $post_data );

		if ( 0 === $post ) {
			$this->prepare_error( 'post' );
		} else {
			update_post_meta( $post, '_fields', $this->clean_fields );
		}
	}

	/**
	 * Prepare response for API. Should probably be moced to rest controller.
	 */
	public function prepare_response() {
		return array(
			'success'   => count( $this->errors ) === 0,
			'form_data' => $this->clean_fields,
			'errors'    => $this->get_validation_errors(),
		);
	}
}
