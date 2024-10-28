<?php
/**
 * Featured Comments inline CSS.
 *
 * @since 1.0
 *
 * @package Featured_Comments
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<style>
	.feature-comments.unfeature, .feature-comments.unbury {
		display: none;
	}

	.feature-comments {
		cursor: pointer;
	}

	.featured.feature-comments.feature {
		display: none;
	}

	.featured.feature-comments.unfeature {
		display: inline;
	}

	.buried.feature-comments.bury {
		display: none;
	}

	.buried.feature-comments.unbury {
		display: inline;
	}

	#the-comment-list tr.featured {
		background-color: #dfd;
	}

	#the-comment-list tr.buried {
		opacity: 0.5;
	}
</style>
