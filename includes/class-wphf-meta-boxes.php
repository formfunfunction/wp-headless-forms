<?php
/**
 * Registers admin meta boxes
 *
 * @package WPHeadlessForms
 * @since 0.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin meta boxes class
 */
class WPHF_Meta_Boxes {
	/**
	 * Initialize meta boxes action.
	 *
	 * @since 0.0.1
	 */
	public static function init() {
		self::init_fields_meta_box();
	}

	/**
	 * Initialize the fields meta box
	 */
	public static function init_fields_meta_box() {
		add_meta_box(
			'wphf-form-response-meta',
			'Form response',
			array( __CLASS__, 'wphf_fields_meta_box_markup' ),
			WPHF_Post_Types::$entries_post_type,
			'normal',
			'high',
			null
		);
	}

	/**
	 * The fields metabox markup
	 *
	 * @since 0.0.1
	 */
	public static function wphf_fields_meta_box_markup() {
		global $post;
		$fields = get_post_meta( $post->ID, '_fields', true ); ?>
		<table class="widefat message-fields striped">
			<tbody>
			<?php foreach ( (array) $fields as $key => $value ) : ?>
				<tr>
					<td class="field-title"><?php echo esc_html( $key ); ?></td>
					<td class="field-value"><?php echo nl2br( esc_html( $value ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}
