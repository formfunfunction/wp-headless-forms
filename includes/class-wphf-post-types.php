<?php
/**
 * Registers custom post types
 *
 * @package WPHeadlessForms
 * @since 0.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Custom post types class
 */
class WPHF_Post_Types {
	/**
	 * Name of the forms post type
	 *
	 * @var string
	 */
	public static $forms_post_type = 'forms';

	/**
	 * Name of the entries post type
	 *
	 * @var string
	 */
	public static $entries_post_type = 'form-entries';

	/**
	 * Initialise post types
	 *
	 * @wp-hook init
	 */
	public static function init() {
		do_action( 'wphf_register_post_types' );
		self::register_form_post_type();
		self::register_entries_post_type();
	}

	/**
	 * Registers the forms post type if it isn't already registered
	 */
	private static function register_form_post_type() {
		if ( post_type_exists( self::$forms_post_type ) ) {
			return;
		}

		$post_type_args = apply_filters(
			'wphf_register_post_type_' . self::$forms_post_type,
			array(
				'labels'   => array(
					'name'          => __( 'Forms', 'fff-rest-contact-form' ),
					'singular_name' => __( 'Form', 'fff-rest-contact-form' ),
				),
				'public'   => true,
				'supports' => array( 'title' ),
			)
		);

		register_post_type( self::$forms_post_type, $post_type_args );
	}

	/**
	 * Registers the forms post type if it isn't already registered
	 */
	private static function register_entries_post_type() {
		if ( post_type_exists( self::$entries_post_type ) ) {
			return;
		}

		$post_type_args = apply_filters(
			'wphf_register_post_type_' . self::$entries_post_type,
			array(
				'labels'   => array(
					'name'          => __( 'Form Entires', 'fff-rest-contact-form' ),
					'singular_name' => __( 'Form Entry', 'fff-rest-contact-form' ),
				),
				'public'   => false,
				'show_ui'  => true,
				'supports' => false,
			)
		);

		register_post_type( self::$entries_post_type, $post_type_args );
	}
}
