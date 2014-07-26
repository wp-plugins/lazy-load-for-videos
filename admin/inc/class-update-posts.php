<?php
/**
 * @package Admin
 */
class lazyload_Update_Posts {

	/**
	 * Update all posts that have an oembedded medium
	 */
	function lazyload_update_posts_with_oembed(){
			$lazyload_general = new LAZYLOAD_General();

		    $arr_posts = get_posts( array( 'post_type' => 'post', 'posts_per_page' => -1 ) );	// -1 == no limit

		    foreach ( $arr_posts as $post ):
		    	if ( $lazyload_general->test_if_post_or_page_has_embed( $post->ID ) ) {
		    		wp_update_post( $post );
		    	}
		    endforeach;
	}

}