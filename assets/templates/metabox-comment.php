<?php
/**
 * Comment Metabox template.
 *
 * Handles markup for the Comment Metabox on "Edit Comment" screens.
 *
 * @package Featured_Comments
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<?php wp_nonce_field( plugin_basename( FEATURED_COMMENTS_FILE ), 'featured_comments_nonce' ); ?>

<p>
	<input type="checkbox" id="featured" name="featured" value="true"<?php checked( true, $featured ); ?>/> <label for="featured"><?php esc_html_e( 'Featured', 'featured-comments' ); ?></label>  <input type="checkbox" id="buried" name="buried" value="true"<?php checked( true, $buried ); ?>/> <label for="buried"><?php esc_html_e( 'Buried', 'featured-comments' ); ?></label>
</p>
