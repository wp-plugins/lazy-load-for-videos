<?php
/**
 * @package Lazyload Youtube
 */
class LAZYLOAD_youtube extends LAZYLOAD_Frontend {

	function __construct() {
		parent::__construct();
	}

	/**
	 * Lazy Load Youtube Videos (Load youtube script and video after clicking on the preview image)
	 * Thanks to »Lazy loading of youtube videos by MS-potilas 2012« (see http://yabtb.blogspot.com/2012/02/youtube-videos-lazy-load-improved-style.html)
	 */
	function enable_lazyload_js() {
		wp_enqueue_script( 'lazyload_youtube_js', plugins_url( '../js/min/lazyload-youtube-ck.js' , __FILE__ ) );
	}

}

function initialize_lazyload_youtube() {
	$lazyload_youtube = new LAZYLOAD_youtube();
}
add_action( 'init', 'initialize_lazyload_youtube' );