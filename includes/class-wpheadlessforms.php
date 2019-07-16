<?php
/**
 * WPHeadlessForms main class
 *
 * @package WPHeadlessForms
 * @since   0.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main WPHeadlessForms class for plugin setup
 *
 * @since 0.0.1
 */
class WPHeadlessForms {
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * The REST API controller instance.
	 *
	 * @var WPHF_REST_Controller
	 */
	public $rest_api_controller = null;

	/**
	 * The current form object
	 *
	 * @var WPHF_Form
	 */
	public $form = null;

	/**
	 * Access this plugin’s working instance
	 * Explaination at https://gist.github.com/thefuxia/3804204
	 *
	 * @since   0.0.1
	 * @return  A WPHeadlessForms instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 0.0.1
	 */
	public function __construct() {}

	/**
	 * Plugin setup.
	 *
	 * @since 0.0.1
	 */
	public function init() {
		$this->includes();
		$this->actions();
		$this->fields();
	}

	/**
	 * Plugin install function.
	 *
	 * @wp-hook activation_hook
	 * @since 0.0.1
	 */
	public static function install() {
		if ( ! class_exists( 'acf_pro' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html__( 'Please install and Activate ACF Pro.', 'acf-pro-addon' ), 'Plugin dependency check', array( 'back_link' => true ) );
		}
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivate function.
	 *
	 * @wp-hook deactivation_hook
	 * @since 0.0.1
	 */
	public static function deactivate() {
		unregister_post_type( WPHF_Post_Types::$forms_post_type );
		unregister_post_type( WPHF_Post_Types::$entries_post_type );
		flush_rewrite_rules();
	}

	/**
	 * Include plugin functions and classes.
	 *
	 * @since 0.0.1
	 */
	public function includes() {
		require_once WPHF_PLUGIN_DIR . '/templates/email.php';
		require_once WPHF_PLUGIN_DIR . '/includes/class-wphf-post-types.php';
		require_once WPHF_PLUGIN_DIR . '/includes/class-wphf-meta-boxes.php';
		require_once WPHF_PLUGIN_DIR . '/includes/class-wphf-form.php';
		require_once WPHF_PLUGIN_DIR . '/includes/class-wphf-submit.php';
		require_once WPHF_PLUGIN_DIR . '/includes/class-wphf-rest-controller.php';
		require_once WPHF_PLUGIN_DIR . '/includes/acf-fields.php';
	}

	/**
	 * Initialize ACF field group.
	 *
	 * @since 0.0.2
	 */
	public function fields() {
		wphf_create_fields();
	}

	/**
	 * Call plugin functions and classes on WP actions.
	 *
	 * @since 0.0.1
	 */
	public function actions() {
		add_action( 'init', array( 'WPHF_Post_Types', 'init' ) );
		add_action( 'rest_api_init', array( &$this, 'wphf_register_rest_routes' ) );

		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( 'WPHF_Meta_Boxes', 'init' ) );
		}
	}

	/**
	 * Init the REST controller class
	 *
	 * @since 0.0.1
	 */
	public function wphf_register_rest_routes() {
		$this->rest_api_controller = new WPHF_REST_Controller();
		$this->rest_api_controller->register_routes();
	}

	/**
	 * Gets and returns a WPHF_Form if it exists
	 *
	 * @param Int $post_id ID of WP_Post.
	 * @since 0.0.1
	 */
	public function get_form( $post_id ) {
		$id = (int) $post_id;

		if ( null !== $this->form ) {
			if ( empty( $id ) || $this->form->id === $id ) {
				return $this->form;
			}
		}

		if ( empty( $id ) ) {
			return false;
		}

		$form = $this->get_forms(
			array(
				'p'         => $id,
				'post_type' => WPHF_Post_Types::$forms_post_type,
			)
		);

		if ( ! empty( $form ) ) {
			$this->form = $form[0];
			return $this->form;
		}

		return false;
	}

	/**
	 * Gets form objects from database
	 *
	 * @param Array $args Arguments to pass to get_posts().
	 * @return Array of WPHF_Form objects.
	 * @since 0.0.1
	 */
	public function get_forms( $args = null ) {
		$args['post_type'] = WPHF_Post_Types::$forms_post_type;

		$posts = get_posts( $args );

		$forms = array();

		foreach ( $posts as $post ) {
			$forms[] = new WPHF_Form( $post );
		}

		$forms = apply_filters( 'wphf_get_forms', $forms );

		return $forms;
	}
}
