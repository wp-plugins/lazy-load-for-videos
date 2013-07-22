<?php
/*
 * Plugin Name: Lazy Load for Videos
 * Plugin URI: http://kevinw.de/lazyloadvideos.php
 * Description: The Lazy Load Videos plugin speeds up your site by replacing embedded Youtube videos with a clickable preview image. Visitors simply click on the image to play the video.
 * Author: Kevin Weber
 * Version: 1.2.2
 * Author URI: http://kevinw.de/
 * License: GPL2+
 * Text Domain: lazy-load-videos
*/

/***** Part 1: Replace embedded Youtube videos with a special piece of code (required for Part 2) */
/* Thanks to Otto's comment on StackExchange (See http://wordpress.stackexchange.com/a/19533) */
	function lazyload_replace_youtube($return, $data, $url) {
		    if ( (! is_feed()) && ($data->provider_name == 'YouTube') ) {
	       		$preview_url = '<a class="lazy-load-youtube preview-youtube" href="' . $url . '" title="Play Video">&ensp;</a>';
	       		return $preview_url;
		    }
		    else return $return;
	}
	add_filter('oembed_dataparse','lazyload_replace_youtube', 10, 3);


/***** Part 2: Enable jQuery (comes with WordPress) */
	function lazyload_enqueue_jquery() {
    	wp_enqueue_script('jquery');
	}
	add_action( 'wp_enqueue_scripts', 'lazyload_enqueue_jquery' );


/***** Part 3: Lazy Load Youtube Videos (Load youtube script and video after clicking on the preview image) */
/* Thanks to »Lazy loading of youtube videos by MS-potilas 2012« (See http://yabtb.blogspot.com/2012/02/youtube-videos-lazy-load-improved-style.html) */
	function enable_lazyload_youtube() { ?>
	    <script type='text/javascript'>
	    var $llv = jQuery.noConflict();
	    $llv(document).ready(function() {

			function doload() {

		      $llv("a.lazy-load-youtube").each(function(index) {
		        var embedparms = $llv(this).attr("href").split("/embed/")[1];
		        if(!embedparms) embedparms = $llv(this).attr("href").split("://youtu.be/")[1];
		        if(!embedparms) embedparms = $llv(this).attr("href").split("v=")[1].replace(/\&/,'?');
		        var youid = embedparms.split("?")[0].split("#")[0];
		        var start = embedparms.match(/[#&]t=(\d+)s/);
		        if(start) start = start[1];
		        else {
		          start = embedparms.match(/[#&]t=(\d+)m(\d+)s/);
		          if(start) start = parseInt(start[1])*60+parseInt(start[2]);
		          else {
		            start = embedparms.match(/[?&]start=(\d+)/);
		            if(start) start = start[1];
		          }
		        }
		        embedparms = embedparms.split("#")[0];
		        if(start && embedparms.indexOf("start=") == -1)
		          embedparms += ((embedparms.indexOf("?")==-1) ? "?" : "&") + "start="+start;
		        if(embedparms.indexOf("showinfo=0") != -1)
		          $llv(this).html('');
		        else
		          $llv(this).html('<div class="lazy-load-youtube-info">' + $llv(this).html() + '</div>');
		        $llv(this).prepend('<div style="height:'+(parseInt($llv(this).css("height"))-4)+'px;width:'+(parseInt($llv(this).css("width"))-4)+'px;" class="lazy-load-youtube-div"></div>');
		        $llv(this).css("background", "#000 url(http://i2.ytimg.com/vi/"+youid+"/0.jpg) center center no-repeat");
		        $llv(this).attr("id", youid+index);
		        $llv(this).attr("href", "http://www.youtube.com/watch?v="+youid+(start ? "#t="+start+"s" : ""));
		        var emu = 'http://www.youtube.com/embed/'+embedparms;
		        emu += ((emu.indexOf("?")==-1) ? "?" : "&") + "autoplay=1";
		        var videoFrame = '<iframe width="'+parseInt($llv(this).css("width"))+'" height="'+parseInt($llv(this).css("height"))+'" style="vertical-align:top;" src="'+emu+'" frameborder="0" allowfullscreen></iframe>';
		        $llv(this).attr("onclick", "$llv('#"+youid+index+"').replaceWith('"+videoFrame+"');return false;");
		      });

			}

			$llv(document).ready(doload());

			$llv(document).ajaxStop(function(){
				doload();
			});

	    })
	    </script>
	<?php }    
	add_action('wp_head', 'enable_lazyload_youtube');


/***** Part 4: Add stylesheet */
	function lazyload_youtube_style() {
		wp_register_style( 'lazy-load-style', plugins_url('style.css', __FILE__) );
		wp_enqueue_style( 'lazy-load-style' );
	}
	add_action( 'wp_enqueue_scripts', 'lazyload_youtube_style' );

/***** Plugin by Kevin Weber || kevinw.de *****/
?>