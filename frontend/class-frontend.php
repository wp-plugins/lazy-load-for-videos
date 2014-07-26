<?php
/**
 * @package Frontend
 */
class LAZYLOAD_Frontend {

	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_lazyload_style') );
		add_action( 'wp_head', array( $this, 'load_lazyload_css') );
		require_once( LL_PATH . 'frontend/class-youtube.php' );
		require_once( LL_PATH . 'frontend/class-vimeo.php' );
		$this->remove_branding();
	}

	/**
	 * Add stylesheet
	 */
	function load_lazyload_style() {
		if ( $this->test_if_scripts_should_be_loaded() ) {
			wp_register_style( 'lazyload-style', plugins_url('css/min/style-lazyload.css', plugin_dir_path( __FILE__ )) );
			wp_enqueue_style( 'lazyload-style' );
			wp_enqueue_script( 'jquery' ); // Enable jQuery (comes with WordPress)
		}
	}

	/**
	 * Add CSS
	 */
	function load_lazyload_css() {
		echo '<style type="text/css">';

		$this->load_lazyload_css_thumbnail_size();
		$this->load_lazyload_css_video_titles();
		$this->load_lazyload_css_button_style();
		$this->load_lazyload_css_custom();

		echo '</style>';
	}

	/**
	 * Add Custom CSS
	 */
	function load_lazyload_css_custom() {
		if (stripslashes(get_option('ll_opt_customcss')) != '') {
			echo stripslashes(get_option('ll_opt_customcss'));
		}
	}

	/**
	 * Add CSS for thumbnails
	 */
	function load_lazyload_css_thumbnail_size() {
    	if ( (get_option('ll_opt_thumbnail_size') == 'cover') ) {
    		echo '.entry-content a.lazy-load-youtube, a.lazy-load-youtube, .lazy-load-vimeo { background-size: cover !important; }';
    	}
	}

	/**
	 * Add CSS to hide Video titles
	 */
	function load_lazyload_css_video_titles() {
		// Hide Youtube titles with CSS
    	if ( get_option('lly_opt_title') == false ) {
    		echo '.titletext.youtube { display: none; }';
    	}
	}

	/**
	 * Change play button style
	 */
	function load_lazyload_css_button_style() {
    	if ( get_option('ll_opt_button_style') == 'youtube_button_image' ) {
    		// Display youtube button image
    		echo '.lazy-load-youtube-div, .lazy-load-vimeo-div { background: url('.plugin_dir_url( __FILE__ ).'../images/play-youtube.png) center center no-repeat; }';	
    		// ... and remove CSS-only content
    		echo $this->load_css_button_selectors() . ' { content: ""; }';
    	}
    	else if ( get_option('ll_opt_button_style') == 'css_black' ) {
    		echo $this->load_css_button_selectors() . ' { color: #000; text-shadow: none; }';
    		echo $this->load_css_button_selectors(':hover') . ' { text-shadow: none; }';
    	}
	}

	/**
	 * Little helper funtion to return the needed selectors for the play buttons
	 */
	private function load_css_button_selectors( $add = '' ) {
		return '
			.lazy-load-youtube-div'.$add.':before,
			.lazy-load-youtube-div'.$add.'::before,
			.lazy-load-vimeo'.$add.':after,
			.lazy-load-vimeo'.$add.'::after
			';
	}

	/**
	 * Don't load scripts on specific circumstances
	 */
	function test_if_scripts_should_be_loaded() {
		$lazyload_general = new LAZYLOAD_General();

		return
			( get_option('ll_opt_load_scripts') != true ) ||	// Option "Support for Widgets (Youtube only)" is checked
			( get_option('lly_opt_support_for_widgets') == true ) ||	// Option "Support for Widgets (Youtube only)" is checked
			( is_singular() && ($lazyload_general->test_if_post_or_page_has_embed()) ) ||	// Pages/posts with oembedded media
			( !is_singular() )	// Everything else (except for pages/posts without oembedded media)
		? true : false;
	}

	/**
	 * Remove branding
	 */
	function remove_branding() {
		if ( get_option('ll_remove_branding') == true ) {
			require_once( LL_PATH . 'frontend/inc/remove_branding.php');
		}
	}

}

$lazyload_frontend = new LAZYLOAD_Frontend();