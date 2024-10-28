<?php
/**
 * Widget Class.
 *
 * Handles Featured Comments widget.
 *
 * @package Featured_Comments
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Featured Comments widget class.
 *
 * @see WP_Widget
 *
 * @since 1.0
 */
class Featured_Comments_Widget extends WP_Widget {

	/**
	 * Sets up a new Featured Comments widget instance.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$widget_ops = [
			'classname'   => 'featured-comments-widget',
			'description' => __( 'Display comments marked as "featured".', 'featured-comments' ),
		];

		parent::__construct(
			'widget-name-id',
			__( 'Featured Comments', 'featured-comments' ),
			$widget_ops
		);

	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @since 1.0
	 *
	 * @param array $args The array of form elements.
	 * @param array $instance The current instance of the widget.
	 */
	public function widget( $args, $instance ) {

		$cache = wp_cache_get( 'featured_comments_widget', 'widget' );

		if ( ! is_array( $cache ) ) {
			$cache = [];
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		// Print cached markup if it exists.
		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $cache[ $args['widget_id'] ];
			return;
		}

		/* This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( empty( $instance['number'] ) || $instance['number'] < 1 ) {
			$intance['number'] = 5;
		}

		// Build default args.
		$args = [
			'number'      => $instance['number'],
			'status'      => 'approve',
			'post_status' => 'publish',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'  => [
				[
					'key'   => 'featured',
					'value' => '1',
				],
			],
		];

		/**
		 * Filter the default Comment query args.
		 *
		 * @since 1.0
		 *
		 * @param array $args The default Comment query args.
		 */
		$args = apply_filters( 'featured_comments_query', $args );

		$comments_query = new WP_Comment_Query( $args );
		$comments       = $comments_query->comments;

		$output = '';
		if ( is_array( $comments ) && ! empty( $comments ) ) :
			$output = $args['before_widget'];

			if ( $title ) {
				$output .= $args['before_title'] . $title . $args['after_title'];
			}

			$output .= '<ul id="featured-comments">';
			foreach ( $comments as $comment ) {
				$output .= '<li class="featured-comments">';
				$output .= sprintf(
					/* translators: 1: The link to the Author, 2: The link to the Comment. */
					_x( '%1$s on %2$s', 'widgets', 'featured-comments' ),
					get_comment_author_link( $comment->comment_ID ),
					'<a href="' . esc_url( get_comment_link( $comment->comment_ID ) ) . '">' . esc_html( get_the_title( $comment->comment_post_ID ) ) . '</a>'
				);
				$output .= '</li>';
			}
			$output .= '</ul>';

			$output .= $args['after_widget'];
		endif;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;

		$cache[ $args['widget_id'] ] = $output;
		wp_cache_set( 'featured_comments_widget', $cache, 'widget' );

	}

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @since 1.0
	 *
	 * @param array $new_instance The previous instance of values before the update.
	 * @param array $old_instance The new instance of values to be generated via the update.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']  = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );

		wp_cache_delete( 'featured_comments_widget', 'widget' );

		return $instance;

	}

	/**
	 * Flushes the widget's cache.
	 *
	 * @see https://github.com/jjeaton/Featured-Comments/commit/0e86b9084d189f121c3191f5be358770118aac22
	 *
	 * @since 2.0.0
	 */
	public function flush_widget_cache() {
		wp_cache_delete( 'featured_comments_widget', 'widget' );
	}

	/**
	 * Generates the administration form for the widget.
	 *
	 * @since 1.0
	 *
	 * @param array $instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		$defaults = [
			'title'  => __( 'Featured Comments', 'featured-comments' ),
			'number' => 5,
		];

		$args = wp_parse_args( $instance, $defaults );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget Title:', 'featured-comments' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $args['title'] ); ?>" />
		</p>
		<p>
			<input class="widefat small-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" value="<?php echo esc_attr( $args['number'] ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number to show', 'featured-comments' ); ?></label>
		</p>

		<?php

	}

}
