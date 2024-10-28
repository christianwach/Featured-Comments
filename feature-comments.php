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

// Set plugin version here.
define( 'FEATURED_COMMENTS_VERSION', '2.0.1a' );

// Store reference to this file.
if ( ! defined( 'FEATURED_COMMENTS_FILE' ) ) {
	define( 'FEATURED_COMMENTS_FILE', __FILE__ );
}

// Store URL to this plugin's directory.
if ( ! defined( 'FEATURED_COMMENTS_URL' ) ) {
	define( 'FEATURED_COMMENTS_URL', plugin_dir_url( FEATURED_COMMENTS_FILE ) );
}

// Store path to this plugin's directory.
if ( ! defined( 'FEATURED_COMMENTS_PATH' ) ) {
	define( 'FEATURED_COMMENTS_PATH', plugin_dir_path( FEATURED_COMMENTS_FILE ) );
}

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

			// Bootstrap instance.
			self::$instance = new Featured_Comments();
			self::$instance->register_hooks();

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
	 * Register hook callbacks.
	 *
	 * @since 1.0
	 * @since 2.0.1 Renamed.
	 */
	private function register_hooks() {

		// Init localisation.
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'init', [ $this, 'load_translations' ] );

		// Add metabox to "Edit Comment" screen.
		add_action( 'admin_menu', [ $this, 'add_meta_box' ] );
		add_action( 'edit_comment', [ $this, 'save_meta_box_postdata' ] );

		// Add actions to Comment lists.
		add_filter( 'comment_row_actions', [ $this, 'comment_row_actions' ], 10, 2 );

		// Modify Comments.
		add_filter( 'comment_class', [ $this, 'comment_class' ], 10, 4 );
		add_filter( 'comment_text', [ $this, 'comment_text' ], 10, 3 );

		// Scripts and styles.
		add_action( 'wp_print_scripts', [ $this, 'print_scripts' ] );
		add_action( 'admin_print_scripts', [ $this, 'print_scripts' ] );
		add_action( 'wp_print_styles', [ $this, 'print_styles' ] );
		add_action( 'admin_print_styles', [ $this, 'print_styles' ] );

		// AJAX callback.
		add_action( 'wp_ajax_feature_comments', [ $this, 'ajax' ] );

		// Register widgets.
		add_action( 'widgets_init', [ $this, 'register_widgets' ] );

	}

	/**
	 * Loads the plugin text domain.
	 *
	 * @since 1.0
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$lang_dir = dirname( plugin_basename( FEATURED_COMMENTS_FILE ) ) . '/languages/';
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
	 * Loads the translated action array.
	 *
	 * @since 2.0.1
	 */
	public function load_translations() {

		self::$actions = [
			'feature'   => __( 'Feature', 'featured-comments' ),
			'unfeature' => __( 'Unfeature', 'featured-comments' ),
			'bury'      => __( 'Bury', 'featured-comments' ),
			'unbury'    => __( 'Unbury', 'featured-comments' ),
		];

	}

	/**
	 * Registers widgets for this plugin.
	 *
	 * @since 2.0.1
	 */
	public function register_widgets() {

		// Register default widget.
		include_once dirname( FEATURED_COMMENTS_FILE ) . '/widget.php';
		register_widget( 'Featured_Comments_Widget' );

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
			FEATURED_COMMENTS_URL . 'assets/js/feature-comments.js',
			[ 'jquery' ],
			FEATURED_COMMENTS_VERSION,
			true
		);

		$vars = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		];

		wp_localize_script( 'featured_comments', 'featured_comments', $vars );

	}

	/**
	 * Write inline CSS styles.
	 *
	 * @since 1.0
	 */
	public function print_styles() {

		if ( ! current_user_can( 'moderate_comments' ) ) {
			return;
		}

		// Include inline CSS template.
		include FEATURED_COMMENTS_PATH . 'assets/css/feature-comments-inline.php';

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
	 * @param string     $comment_text Text of the comment.
	 * @param WP_Comment $comment      The Comment object.
	 * @param array      $args         An array of arguments.
	 * @return string $comment_text The modified Comment text.
	 */
	public function comment_text( $comment_text, $comment, $args = [] ) {

		// Bail if not front end and User does not have capability.
		if ( is_admin() || ! current_user_can( 'moderate_comments' ) ) {
			return $comment_text;
		}

		// Get the current Comment ID.
		$comment_id = $comment->comment_ID;

		// Build Comment classes.
		$comment_classes = implode( ' ', self::comment_class( [], [], $comment_id, $comment ) );

		// Create a nonce.
		$nonce = esc_attr( wp_create_nonce( 'featured_comments' ) );

		// Build items array.
		$items = [];
		foreach ( self::$actions as $action => $label ) {
			$items[] = sprintf(
				'<a data-do="%s" data-comment-id="%s" data-nonce="%s" class="%s" title="%s">%s</a>',
				$action,
				$comment_id,
				esc_attr( wp_create_nonce( 'featured_comments' ) ),
				esc_attr( 'feature-comments ' . $comment_classes . ' ' . $action ),
				esc_attr( $label ),
				esc_html( $label )
			);
		}

		$comment_text .= sprintf(
			'<div class="feature-bury-comments">%s</div>',
			implode( $items )
		);

		return $comment_text;

	}

	/**
	 * Modify Comment row actions.
	 *
	 * @since 1.0
	 *
	 * @param string[]   $actions An array of Comment actions.
	 * @param WP_Comment $comment The Comment object.
	 * @return string $actions The modified Comment row actions.
	 */
	public function comment_row_actions( $actions, $comment ) {

		// Get the current Comment ID.
		$comment_id = $comment->comment_ID;

		// Build Comment classes.
		$comment_classes = implode( ' ', self::comment_class( [], [], $comment_id, $comment ) );

		// Create a nonce.
		$nonce = esc_attr( wp_create_nonce( 'featured_comments' ) );

		$unfeature = sprintf(
			'<a data-do="unfeature" data-comment-id="%s" data-nonce="%s" class="%s" aria-label="%s" title="%s">%s</a>',
			$comment_id,
			$nonce,
			'feature-comments unfeature vim-u ' . $comment_classes,
			"dim:the-comment-list:comment-{$comment_id}:unfeatured:e7e7d3:e7e7d3:new=unfeatured",
			esc_attr__( 'Unfeature this comment', 'featured-comments' ),
			esc_html__( 'Unfeature', 'featured-comments' )
		);

		$feature = sprintf(
			'<a data-do="feature" data-comment-id="%s" data-nonce="%s" class="%s" aria-label="%s" title="%s">%s</a>',
			$comment_id,
			$nonce,
			'feature-comments feature vim-a ' . $comment_classes,
			"dim:the-comment-list:comment-{$comment_id}:unfeatured:e7e7d3:e7e7d3:new=featured",
			esc_attr__( 'Feature this comment', 'featured-comments' ),
			esc_html__( 'Feature', 'featured-comments' )
		);

		$unbury = sprintf(
			'<a data-do="unbury" data-comment-id="%s" data-nonce="%s" class="%s" aria-label="%s" title="%s">%s</a>',
			$comment_id,
			$nonce,
			'feature-comments unbury vim-u ' . $comment_classes,
			"dim:the-comment-list:comment-{$comment_id}:unburied:e7e7d3:e7e7d3:new=unburied",
			esc_attr__( 'Unbury this comment', 'featured-comments' ),
			esc_html__( 'Unbury', 'featured-comments' )
		);

		$bury = sprintf(
			'<a data-do="bury" data-comment-id="%s" data-nonce="%s" class="%s" aria-label="%s" title="%s">%s</a>',
			$comment_id,
			$nonce,
			'feature-comments bury vim-a ' . $comment_classes,
			"dim:the-comment-list:comment-{$comment_id}:unburied:e7e7d3:e7e7d3:new=buried",
			esc_attr__( 'Bury this comment', 'featured-comments' ),
			esc_html__( 'Bury', 'featured-comments' )
		);

		$wrapper = sprintf(
			'<span class="%s">%s%s | %s%s</span>',
			$comment_classes,
			$unfeature,
			$feature,
			$unbury,
			$bury
		);

		$actions['feature_comments'] = $wrapper;

		return $actions;

	}

	/**
	 * Adds meta box.
	 *
	 * @since 1.0
	 */
	public function add_meta_box() {

		add_meta_box(
			'feature_bury_comment_meta_box',
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
	 *
	 * @param WP_Comment $comment The Comment object.
	 * @param array      $box {
	 *     Comment meta box arguments.
	 *
	 *     @type string   $id       Meta box ID.
	 *     @type string   $title    Meta box title.
	 *     @type callback $callback Meta box display callback.
	 *     @type array    $args {
	 *         Extra meta box arguments.
	 *     }
	 * }
	 */
	public function comment_meta_box( $comment, $box ) {

		// Populate template vars.
		$comment_id = $comment->comment_ID;
		$featured   = self::is_comment_featured( $comment_id );
		$buried     = self::is_comment_buried( $comment_id );

		// Include template.
		include FEATURED_COMMENTS_PATH . 'assets/templates/metabox-comment.php';

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

		if ( ! wp_verify_nonce( $nonce, plugin_basename( FEATURED_COMMENTS_FILE ) ) ) {
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
	 * @param string[]   $classes    An array of comment classes.
	 * @param string[]   $css_class  An array of additional classes added to the list.
	 * @param string     $comment_id The Comment ID as a numeric string.
	 * @param WP_Comment $comment    The Comment object.
	 * @return array $classes The modified array of Comment classes.
	 */
	public function comment_class( $classes = [], $css_class = [], $comment_id = 0, $comment = null ) {

		if ( self::is_comment_featured( $comment_id ) ) {
			$classes[] = 'featured';
		}

		if ( self::is_comment_buried( $comment_id ) ) {
			$classes [] = 'buried';
		}

		return $classes;

	}

	/**
	 * Checks if a Comment is featured.
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
	 * Checks if a Comment is buried.
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
