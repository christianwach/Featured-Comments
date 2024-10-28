<?php
/**
 * Featured Comments
 *
 * Plugin Name:       Featured Comments
 * Description:       Lets the admin add "featured" or "buried" css class to selected comments. Handy to highlight comments that add value to your post. Also includes a Featured Comments widget.
 * Plugin URI:        http://pippinsplugins.com/featured-comments
 * GitHub Plugin URI: https://github.com/christianwach/Featured-Comments
 * Author:            Pippin Williamson
 * Author URI:        http://pippinsplugins.com
 * Version:           2.0.1a
 * Contributors:      mordauk, Utkarsh
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Text Domain:       featured-comments
 * Domain path:       /languages
 *
 * @package Featured_Comments
 * @link    https://github.com/christianwach/Featured-Comments
 * @license GPL v2 or later
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 * Online: http://www.gnu.org/licenses/gpl.txt
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Featured Comments class.
 *
 * A class that encapsulates this plugin's functionality.
 *
 * @since 1.0
 */
final class Featured_Comments {

	/**
	 * Instance.
	 *
	 * @since 1.0
	 * @var Featured_Comments
	 */
	private static $instance;

	/**
	 * Array of actions.
	 *
	 * @since 1.0
	 * @var array
	 */
	private static $actions = [];

	/**
	 * Main Featured Comments Instance.
	 *
	 * Ensures that only one instance of Featured Comments exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @staticvar array $instance
	 * @see wp_featured_comments_load()
	 *
	 * @return Featured_Comments $instance The one true Featured_Comments instance.
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new Featured_Comments();
			self::$instance->includes();
			self::$instance->init();
			self::$instance->load_textdomain();

			/**
			 * Fires when this plugin is loaded.
			 *
			 * @since 1.0
			 */
			do_action( 'featured_comments_loaded' );

		}

		return self::$instance;

	}

	/**
	 * Loads widget class file.
	 *
	 * @since 1.0
	 */
	private function includes() {
		include_once dirname( __FILE__ ) . '/widget.php';
	}

	/**
	 * Initialise Filters & Actions.
	 *
	 * @since 1.0
	 */
	private function init() {

		self::$actions = [
			'feature'   => __( 'Feature', 'featured-comments' ),
			'unfeature' => __( 'Unfeature', 'featured-comments' ),
			'bury'      => __( 'Bury', 'featured-comments' ),
			'unbury'    => __( 'Unbury', 'featured-comments' ),
		];

		// Back end.
		add_action( 'edit_comment', [ $this, 'save_meta_box_postdata' ] );
		add_action( 'admin_menu', [ $this, 'add_meta_box' ] );
		add_action( 'wp_ajax_feature_comments', [ $this, 'ajax' ] );
		add_filter( 'comment_text', [ $this, 'comment_text' ], 10, 3 );
		add_filter( 'comment_row_actions', [ $this, 'comment_row_actions' ] );

		add_action( 'wp_print_scripts', [ $this, 'print_scripts' ] );
		add_action( 'admin_print_scripts', [ $this, 'print_scripts' ] );
		add_action( 'wp_print_styles', [ $this, 'print_styles' ] );
		add_action( 'admin_print_styles', [ $this, 'print_styles' ] );

		// Front end.
		add_filter( 'comment_class', [ $this, 'comment_class' ] );

	}

	/**
	 * Initialise translation.
	 *
	 * @since 1.0
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters( 'featured_comments_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'featured-comments' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'featured-comments', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/featured-comments/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/featured-comments folder.
			load_textdomain( 'featured-comments', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/featured-comments/languages/ folder.
			load_textdomain( 'featured-comments', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'featured-comments', false, $lang_dir );
		}

	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0
	 */
	public function print_scripts() {

		if ( ! current_user_can( 'moderate_comments' ) ) {
			return;
		}

		wp_enqueue_script(
			'featured_comments',
			plugin_dir_url( __FILE__ ) . 'feature-comments.js',
			[ 'jquery' ],
			filemtime( dirname( __FILE__ ) . '/feature-comments.js' ),
			true
		);

		$vars = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		];

		wp_localize_script( 'featured_comments', 'featured_comments', $vars );

	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.0
	 */
	public function print_styles() {

		if ( current_user_can( 'moderate_comments' ) ) {
			?>
			<style>
				.feature-comments.unfeature, .feature-comments.unbury { display:none; }
				.feature-comments { cursor:pointer; }
				.featured.feature-comments.feature { display:none; }
				.featured.feature-comments.unfeature { display:inline; }
				.buried.feature-comments.bury { display:none; }
				.buried.feature-comments.unbury { display:inline; }
				#the-comment-list tr.featured { background-color: #dfd; }
				#the-comment-list tr.buried { opacity: 0.5; }
			</style>
			<?php
		}

	}

	/**
	 * AJAX callback.
	 *
	 * @since 1.0
	 */
	public function ajax() {

		// Since this is an AJAX request, check security.
		$result = check_ajax_referer( 'featured_comments', false, false );
		if ( false === $result ) {
			die;
		}

		// Bail if no action.
		$action = isset( $_POST['do'] ) ? sanitize_text_field( wp_unslash( $_POST['do'] ) ) : '';
		if ( ! isset( $action ) ) {
			die;
		}

		// Bail if not one of our actions.
		$actions = array_keys( self::$actions );
		if ( ! in_array( $action, $actions, true ) ) {
			die;
		}

		// Bail if no Comment ID.
		$comment_id = isset( $_POST['comment_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['comment_id'] ) ) : '';
		if ( empty( $comment_id ) ) {
			die;
		}

		// Check capabilities.
		if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
			die;
		}

		// Bail if no Comment.
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			die;
		}

		switch ( $action ) {
			case 'feature':
				update_comment_meta( $comment_id, 'featured', '1' );
				break;

			case 'unfeature':
				update_comment_meta( $comment_id, 'featured', '0' );
				break;

			case 'bury':
				update_comment_meta( $comment_id, 'buried', '1' );
				break;

			case 'unbury':
				update_comment_meta( $comment_id, 'buried', '0' );
				break;
		}

		die( esc_attr( wp_create_nonce( 'featured_comments' ) ) );

	}

	/**
	 * Modify Comment text.
	 *
	 * @since 1.0
	 *
	 * @param string $comment_text The Comment text.
	 * @return string $comment_text The modified Comment text.
	 */
	public function comment_text( $comment_text ) {

		if ( is_admin() || ! current_user_can( 'moderate_comments' ) ) {
			return $comment_text;
		}

		global $comment;

		$comment_id = $comment->comment_ID;
		$data_id    = ' data-comment_id=' . $comment_id;

		$current_status = implode( ' ', self::comment_class() );

		$output = '<div class="feature-bury-comments">';
		foreach ( self::$actions as $action => $label ) {
			$output .= "<a class='feature-comments {$current_status} {$action}' data-do='{$action}' {$data_id} data-nonce='" . wp_create_nonce( 'featured_comments' ) . "' title='{$label}'>{$label}</a> ";
		}
		$output .= '</div>';

		return $comment_text . $output;

	}

	/**
	 * Modify Comment row actions.
	 *
	 * @since 1.0
	 *
	 * @param string $actions The Comment row actions.
	 * @return string $actions The modified Comment row actions.
	 */
	public function comment_row_actions( $actions ) {

		global $comment, $post, $approve_nonce;

		$comment_id = $comment->comment_ID;

		$data_id = ' data-comment_id=' . $comment->comment_ID;

		$current_status = implode( ' ', self::comment_class() );

		$o  = '';
		$o .= "<a data-do='unfeature' {$data_id} data-nonce='" . wp_create_nonce( 'featured_comments' ) . "' class='feature-comments unfeature {$current_status} dim:the-comment-list:comment-{$comment->comment_ID}:unfeatured:e7e7d3:e7e7d3:new=unfeatured vim-u' title='" . esc_attr__( 'Unfeature this comment', 'featured-comments' ) . "'>" . __( 'Unfeature', 'featured-comments' ) . '</a>';
		$o .= "<a data-do='feature' {$data_id} data-nonce='" . wp_create_nonce( 'featured_comments' ) . "' class='feature-comments feature {$current_status} dim:the-comment-list:comment-{$comment->comment_ID}:unfeatured:e7e7d3:e7e7d3:new=featured vim-a' title='" . esc_attr__( 'Feature this comment', 'featured-comments' ) . "'>" . __( 'Feature', 'featured-comments' ) . '</a>';
		$o .= ' | ';
		$o .= "<a data-do='unbury' {$data_id} data-nonce='" . wp_create_nonce( 'featured_comments' ) . "' class='feature-comments unbury {$current_status} dim:the-comment-list:comment-{$comment->comment_ID}:unburied:e7e7d3:e7e7d3:new=unburied vim-u' title='" . esc_attr__( 'Unbury this comment', 'featured-comments' ) . "'>" . __( 'Unbury', 'featured-comments' ) . '</a>';
		$o .= "<a data-do='bury' {$data_id}  data-nonce='" . wp_create_nonce( 'featured_comments' ) . "' class='feature-comments bury {$current_status} dim:the-comment-list:comment-{$comment->comment_ID}:unburied:e7e7d3:e7e7d3:new=buried vim-a' title='" . esc_attr__( 'Bury this comment', 'featured-comments' ) . "'>" . __( 'Bury', 'featured-comments' ) . '</a>';
		$o  = "<span class='$current_status'>$o</span>";

		$actions['feature_comments'] = $o;

		return $actions;

	}

	/**
	 * Adds meta box.
	 *
	 * @since 1.0
	 */
	public function add_meta_box() {

		add_meta_box(
			'comment_meta_box',
			__( 'Featured Comments', 'featured-comments' ),
			[ $this, 'comment_meta_box' ],
			'comment',
			'normal'
		);

	}

	/**
	 * Renders the meta box.
	 *
	 * @since 1.0
	 */
	public function comment_meta_box() {

		global $comment;
		$comment_id = $comment->comment_ID;

		wp_nonce_field( plugin_basename( __FILE__ ), 'featured_comments_nonce' );
		echo '<p>';
		echo '<input id = "featured" type="checkbox" name="featured" value="true"' . checked( true, self::is_comment_featured( $comment_id ), false ) . '/>';
		echo ' <label for="featured">' . esc_html__( 'Featured', 'featured-comments' ) . '</label>&nbsp;';
		echo '<input id = "buried" type="checkbox" name="buried" value="true"' . checked( true, self::is_comment_buried( $comment_id ), false ) . '/>';
		echo ' <label for="buried">' . esc_html__( 'Buried', 'featured-comments' ) . '</label>';
		echo '</p>';

	}

	/**
	 * Saves data from meta box.
	 *
	 * @since 1.0
	 *
	 * @param integer $comment_id The ID of the Comment.
	 */
	public function save_meta_box_postdata( $comment_id ) {

		// Bail if no nonce in POST.
		$nonce = isset( $_POST['featured_comments_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['featured_comments_nonce'] ) ) : '';
		if ( ! isset( $nonce ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
			comment_footer_die( __( 'You are not allowed to edit comments on this post.', 'featured-comments' ) );
		}

		update_comment_meta( $comment_id, 'featured', isset( $_POST['featured'] ) ? '1' : '0' );
		update_comment_meta( $comment_id, 'buried', isset( $_POST['buried'] ) ? '1' : '0' );

	}

	/**
	 * Modifies the Comment classes.
	 *
	 * @since 1.0
	 *
	 * @param array $classes The array of Comment classes.
	 * @return array $classes The modified array of Comment classes.
	 */
	public function comment_class( $classes = [] ) {
		global $comment;

		$comment_id = $comment->comment_ID;

		if ( self::is_comment_featured( $comment_id ) ) {
			$classes[] = 'featured';
		}

		if ( self::is_comment_buried( $comment_id ) ) {
			$classes [] = 'buried';
		}

		return $classes;

	}

	/**
	 * Checks of a Comment is featured.
	 *
	 * @since 1.0
	 *
	 * @param integer $comment_id The ID of the Comment.
	 * @return bool True if featured, false otherwise.
	 */
	private function is_comment_featured( $comment_id ) {

		if ( '1' === get_comment_meta( $comment_id, 'featured', true ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Checks of a Comment is buried.
	 *
	 * @since 1.0
	 *
	 * @param integer $comment_id The ID of the Comment.
	 * @return bool True if buried, false otherwise.
	 */
	private static function is_comment_buried( $comment_id ) {

		if ( '1' === get_comment_meta( $comment_id, 'buried', true ) ) {
			return true;
		}

		return false;

	}

}

/**
 * Loads plugin and returns instance.
 *
 * @since 1.0
 *
 * @return Featured_Comments The plugin instance.
 */
function wp_featured_comments_load() {
	return Featured_Comments::instance();
}

// Load Featured Comments.
wp_featured_comments_load();
