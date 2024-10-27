/**
 * Adds a click handler to "Edit Comment" links.
 *
 * @since 1.0
 */
function featured_comments_click() {

	// Unbind first.
	jQuery('.feature-comments').unbind('click');

	// Re-bind.
	jQuery('body').on( 'click', '.feature-comments', function() {
		$this = jQuery(this);
		jQuery.post(
			featured_comments.ajax_url,
			{
				'action': 'feature_comments',
				'do': $this.data('do'),
				'comment_id': $this.data('comment_id'),
				'_ajax_nonce': $this.data('nonce')
			},
			function( response ) {
				var action = $this.attr('data-do'),
					comment_id = $this.attr('data-comment_id'),
					$comment = jQuery("#comment-" + comment_id + ", #li-comment-" + comment_id),
					$this_and_comment = $this.siblings('.feature-comments').add($comment).add($this);
				if ( action == 'feature' ) {
					$this_and_comment.addClass('featured');
				}
				if ( action == 'unfeature' ) {
					$this_and_comment.removeClass('featured');
				}
				if ( action == 'bury' ) {
					$this_and_comment.addClass('buried');
				}
				if ( action == 'unbury' ) {
					$this_and_comment.removeClass('buried');
				}
				$this.data( 'nonce', response );
			}
		);
		return false;
	});

}

/**
 * Act when document is ready.
 *
 * @since 1.0
 */
jQuery(document).ready( function($) {

	// Init click handler.
	featured_comments_click();

	/**
	 * Set classes on Edit Comments.
	 *
	 * @since 1.0
	 */
	$('.feature-comments.feature').each( function() {
		$this = $(this);
		$tr = $(this).parents('tr');
		if ( $this.hasClass('featured') ) {
			$tr.addClass('featured');
		}
		if ( $this.hasClass('buried') ) {
			$tr.addClass('buried');
		}
	});

});
