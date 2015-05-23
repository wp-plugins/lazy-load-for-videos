<?php
/**
 * Create options panel (http://codex.wordpress.org/Creating_Options_Pages)
 * @package Admin
 */
class Lazyload_Videos_Admin {

	private $schema_prop_video = '';

	function __construct() {
		$this->set_schema_prop_video();
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		// The 'oembed_dataparse' filter should be called on backend AND on frontend, not only on backend [is_admin()]. Otherwise, on some websites occur errors.
		add_filter( 'oembed_dataparse', array( $this, 'lazyload_replace_video' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'lazyload_create_menu' ) );
		$this->lazyloadvideos_update_posts_with_embed();
	}

	function admin_init() {
		if ( isset( $_GET['page'] ) && ( $_GET['page'] == LL_ADMIN_URL ) ) {
			$this->lazyload_admin_css();
			$this->lazyload_admin_js();
		}
		$plugin = plugin_basename( LL_FILE ); 
		add_filter("plugin_action_links_$plugin", array( $this, 'lazyload_settings_link' ) );
		$this->register_lazyload_settings();
	}

	/*
	 * Update posts with embed when user has clicked "Update Posts"
	 * @info: lazyloadvideos_update_posts_with_embed() is loaded by class-register.php
	 */
	function lazyloadvideos_update_posts_with_embed() {
		if ( isset( $_POST['update_posts'] ) && $_POST['update_posts'] == 'with_oembed' ) {
			lazyloadvideos_update_posts_with_embed();
		}
	}

	/**
	 * Add settings link on plugin page
	 */
	function lazyload_settings_link($links) { 
	  $settings_link = '<a href="options-general.php?page='. LL_ADMIN_URL .'">Settings</a>'; 
	  array_unshift($links, $settings_link); 
	  return $links; 
	}

	/**
	 * Handle schema for Video SEO
	 */
	function set_schema_prop_video() {
		if ( get_option('ll_video_seo') == true ) {
			$this->schema_prop_video = ' itemprop="video" itemscope itemtype="http://schema.org/VideoObject"';
		}
	}
	function get_schema_prop_video() {
		return $this->schema_prop_video;
	}

	/**
	 * Replace embedded Youtube and Vimeo videos with a special piece of code.
	 * Thanks to Otto's comment on StackExchange (See http://wordpress.stackexchange.com/a/19533)
	 */
	function lazyload_replace_video($return, $data, $url) {
		global $lazyload_videos_general;

		// Youtube support
	    if ( (! is_feed()) && ($data->provider_name == 'YouTube') 
				&& (get_option('lly_opt') == false) // test if Lazy Load for Youtube is deactivated
	    	) {

	    	$a_class = 'lazy-load-youtube preview-lazyload preview-youtube';
	    	$a_class = apply_filters( 'lazyload_preview_url_a_class_youtube', $a_class );

       		$preview_url = '<a class="' . $a_class . '" href="' . $url . '" video-title="' . $data->title . '" title="Play Video &quot;' . $data->title . '&quot;" style="text-decoration:none;color:#000">' . $url . '</a>';
 			
 			// Wrap container around $preview_url
       		$preview_url = '<div class="container-lazyload preview-lazyload container-youtube"'. $this->get_schema_prop_video() .'>' . $preview_url . '</div>';
       		return apply_filters( 'lazyload_replace_video_preview_url_youtube', $preview_url );
	    }

	    // Vimeo support
	    elseif ( (! is_feed()) && ($data->provider_name == 'Vimeo') 
				&& (get_option('llv_opt') == false) // test if Lazy Load for Vimeo is deactivated
	    	) {

			$spliturl = explode("/", $url);
			foreach($spliturl as $key=>$value)
			{
			    if ( empty( $value ) )
			        unset($spliturl[$key]);
			};
			$vimeoid = end($spliturl);

	    	$a_class = 'lazy-load-vimeo preview-lazyload preview-vimeo';
	    	$a_class = apply_filters( 'lazyload_preview_url_a_class_youtube', $a_class );

			$preview_url = '<div id="' . $vimeoid . '" class="' . $a_class . '" title="Play Video &quot;' . $data->title . '&quot;">' . $url . '</div>';
			// Wrap container around $preview_url
			$preview_url = '<div class="container-lazyload container-vimeo"'. $this->get_schema_prop_video() .'>' . $preview_url . '</div>';
			return apply_filters( 'lazyload_replace_video_preview_url_vimeo', $preview_url );
	    }

	    else return $return;
	}

	function lazyload_create_menu() {
		add_options_page('Lazy Load for Videos', 'Lazy Load for Videos', 'manage_options', LL_ADMIN_URL, array( $this, 'lazyload_settings_page' ));
	}

	function register_lazyload_settings() {
		$arr = array(
			//General/Styling
			'll_opt_load_scripts',
			'll_opt_load_responsive',
			'll_opt_button_style',
			'll_opt_thumbnail_size',
			'll_opt_customcss',
			'll_video_seo', // Google: "Make sure that your video and schema.org markup are visible without executing any JavaScript or Flash." --> Video is not working with Lazy Load
			'll_opt_support_for_tablepress',

			// Youtube
			'lly_opt',
			'lly_opt_title',
			'lly_opt_player_preroll',
			'lly_opt_player_postroll',
			'lly_opt_support_for_widgets',
			'lly_opt_thumbnail_quality',
			'lly_opt_thumbnail_quality_max_force',
			'lly_opt_player_colour',
			'lly_opt_player_colour_progress',
			'lly_opt_player_showinfo',
			'lly_opt_player_relations',
			'lly_opt_player_controls',
			'lly_opt_player_loadpolicy',

			// Vimeo
			'llv_opt',
			'llv_opt_title',
			'llv_opt_player_colour',
		);

		foreach ( $arr as $i ) {
			register_setting( 'll-settings-group', $i );
		}
		do_action( 'lazyload_register_settings_after' );
	}

	function lazyload_settings_page()	{ ?>

		<?php if ( isset( $_POST['update_posts'] ) && $_POST['update_posts'] == 'with_oembed' ) { ?>
			<div class="update-posts updated"><p>Your posts have been updated successfully.</p></div>
		<?php } ?>

		<div id="tabs" class="ui-tabs">
			<h2>Lazy Load for Videos <span class="subtitle">by <a href="http://kevinw.de/ll" target="_blank" title="Website by Kevin Weber">Kevin Weber</a> (Version <?php echo LL_VERSION; ?>)</span>
				<br><span class="claim" style="font-size:15px;font-style:italic;position:relative;top:-7px;"><?php esc_html_e( 'Speed up your site and customise your video player!', LL_TD ); ?></span>
			</h2>
	
			<ul class="ui-tabs-nav">
		        <li><a href="#general">General/Styling</a></li>
		        <li><a href="#youtube">Youtube <span class="newred_dot">&bull;</span></a></li>
		    	<li><a href="#vimeo">Vimeo</a></li>
		        <?php do_action( 'lazyload_settings_page_tabs_link_after' ); ?>
		    </ul>

			<form method="post" action="options.php">
			<?php
			    settings_fields( 'll-settings-group' );
		   		do_settings_sections( 'll-settings-group' );
		   	?>


				<div id="general">

					<h3>General/Styling</h3>

					<table class="form-table">
						<tbody>
					        <tr valign="top">
						        <th scope="row"><label>Only load CSS/JS when needed<br><span class="description thin">to improve performance</span></label></th>
						        <td>
									<input name="ll_opt_load_scripts" type="checkbox" value="1" <?php checked( '1', get_option( 'll_opt_load_scripts' ) ); ?> /> <label>It can happen that &ndash; when this option is checked &ndash; videos on pages do not lazy load although they should. It works on most sites. Simply test it.</label>
						        </td>
					        </tr>
				        	<tr valign="top">
						        <th scope="row"><label>Responsive Mode <span class="newred grey">Tip</span></label></th>
						        <td>
									<input name="ll_opt_load_responsive" type="checkbox" value="1" <?php checked( '1', get_option( 'll_opt_load_responsive' ) ); ?> /> <label>Check this to improve responsiveness. Video aspect ratio will be 16:9.</label>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Play Button</label></th>
						        <td>
									<select class="select" typle="select" name="ll_opt_button_style">
										<option value="default"<?php if (get_option('ll_opt_button_style') === 'default') { echo ' selected="selected"'; } ?>>White (CSS-only)</option>
										<option value="css_white_pulse"<?php if (get_option('ll_opt_button_style') === 'css_white_pulse') { echo ' selected="selected"'; } ?>>White Pulse (CSS-only)</option>
										<option value="css_black"<?php if (get_option('ll_opt_button_style') === 'css_black') { echo ' selected="selected"'; } ?>>Black (CSS-only)</option>
										<option value="css_black_pulse"<?php if (get_option('ll_opt_button_style') === 'css_black_pulse') { echo ' selected="selected"'; } ?>>Black Pulse (CSS-only)</option>
										<option value="youtube_button_image"<?php if (get_option('ll_opt_button_style') === 'youtube_button_image') { echo ' selected="selected"'; } ?>>Youtube button image</option>
										<option value="youtube_button_image_red"<?php if (get_option('ll_opt_button_style') === 'youtube_button_image_red') { echo ' selected="selected"'; } ?>>Red Youtube button image</option>
									</select>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Thumbnail Size</label></th>
						        <td>
									<select class="select" typle="select" name="ll_opt_thumbnail_size">
										<option value="cover"<?php if (get_option('ll_opt_thumbnail_size') === 'cover') { echo ' selected="selected"'; } ?>>Cover</option>
										<option value="standard"<?php if (get_option('ll_opt_thumbnail_size') === 'standard') { echo ' selected="selected"'; } ?>>Contain</option>
									</select>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Custom CSS</label></th>
					        	<td>
					        		<textarea rows="14" cols="70" type="text" name="ll_opt_customcss"><?php echo get_option('ll_opt_customcss'); ?></textarea>
					        	</td>
					        </tr>
					        <tr valign="top">
						        <th scope="row"><label>Schema.org Markup <span class="newred">Beta</span></label></th>
						        <td>
									<input name="ll_video_seo" type="checkbox" value="1" <?php checked( '1', get_option( 'll_video_seo' ) ); ?> /> <label>Add schema.org markup to your Youtube and Vimeo videos. Those changes don't seem to affect your search ranking because videos and schema.org markup <a href="https://developers.google.com/webmasters/videosearch/schema" target="_blank">should be visible</a> without JavaScript (but that cannot be the case when videos are lazy loaded).</label> <label><span style="color:#f60;">Important:</span> Updates on this option will only affect new posts and posts you update afterwards with the "Update Posts" button at the bottom of this form.</label>
						        </td>
					        </tr>
					        <tr valign="top">
						        <th scope="row"><label>Support for TablePress</label></th>
						        <td>
									<input name="ll_opt_support_for_tablepress" type="checkbox" value="1" <?php checked( '1', get_option( 'll_opt_support_for_tablepress' ) ); ?> /> <label>Only check this box if you actually use this feature (for reason of performance). If checked, you can paste a Youtube or Vimeo URL into tables that are created with TablePress and it will be lazy loaded.</label>
						        </td>
					        </tr>
					    </tbody>
				    </table>

				</div>

				<div id="youtube">

					<h3>Lazy Load for Youtube</h3>

					<table class="form-table">
						<tbody>
					        <tr valign="top">
						        <th scope="row"><label>Disable Lazy Load for Youtube</label></th>
						        <td>
									<input name="lly_opt" type="checkbox" value="1" <?php checked( '1', get_option( 'lly_opt' ) ); ?> /> <label>If checked, Lazy Load will not be used for <b>Youtube</b> videos.</label> <label><span style="color:#f60;">Important:</span> Updates on this option will only affect new posts and posts you update afterwards with the "Update Posts" button at the bottom of this form.</label>
						        </td>
					        </tr>
					        <tr valign="top">
						        <th scope="row"><label>Display Youtube title</label></th>
						        <td>
									<input name="lly_opt_title" type="checkbox" value="1" <?php checked( '1', get_option( 'lly_opt_title' ) ); ?> /> <label>If checked, the Youtube video title will be displayed on preview image.</label>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Pre-roll/post-roll ads <span class="newred">New!</span><span class="description thin"><br>Sell advertising space!</span></label></th>
					        	<td>
					        		<strong style="width:80px;display:inline-block">Pre-roll</strong> <input type="text" name="lly_opt_player_preroll" placeholder="" value="<?php echo get_option('lly_opt_player_preroll'); ?>" /><br>
					        		<strong style="width:80px;display:inline-block">Post-roll</strong> <input type="text" name="lly_opt_player_postroll" placeholder="" value="<?php echo get_option('lly_opt_player_postroll'); ?>" /> (multiple IDs allowed)<br>
					        		<br>
					        		<label>Convert all Youtube videos into a playlist and automatically add your corporate video, product teaser or another video advertisement. You have to insert the plain Youtube <b>video ID</b>, like <b>Dp2mI9AgiGs</b> or a comma-separated list of video IDs (<i>Dp2mI9AgiGs,IJNR2EpS0jw</i>).</label><br><br><label>&raquo;I'm very proud of this feature because it gives you a new space to promote your brand or sell advertisements! An advertiser might pay to play his video before your actual video starts to play. Isn't this an amazing opportunity?&laquo;<br>&ndash; <a href="http://kevinw.de/ll" target="_blank">Kevin Weber</a>, marketing technologist and developer of this plugin</label><br><br><label>Play around with this plugin, and then consider to get premium or say "Thank you" with an appropriate <a href="http://kevinw.de/donate/LazyLoadVideos/" title="Pay Kevin Weber something to eat" target="_blank">donation</a>.</label>
					        	</td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Default thumbnail quality</label></th>
						        <td>
									<select class="select" typle="select" name="lly_opt_thumbnail_quality">
										<option value="0"<?php if (get_option('lly_opt_thumbnail_quality') === '0') { echo ' selected="selected"'; } ?>>Standard quality</option>
										<option value="max"<?php if (get_option('lly_opt_thumbnail_quality') === 'max') { echo ' selected="selected"'; } ?>>Max resolution</option>
									</select>
									<span style="display:none;" class="lly_opt_thumbnail_quality_max_force_wrapper">
										<input style="margin-left:3px;" name="lly_opt_thumbnail_quality_max_force" type="checkbox" value="1" <?php checked( '1', get_option( 'lly_opt_thumbnail_quality_max_force' ) ); ?> />
										<label>Force max resolution everywhere<div style="display:inline;float:none;" class="help"><span class="tooltip-right info-icon" data-tooltip="By default, max resolution is only used when a singular post/page is displayed.">?</span></div></label>
									</span>
									<p>
										Define which thumbnail quality should be used by default.<br>
										<span style="color:#f90;">Important:</span> Some videos don't have a thumbnail with maximum resolution. In this case, a pixelated placeholder image will be displayed and error messages might appear in your browser's log. You can override the default setting on every post and page individually.
									</p>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Player colour</label></th>
						        <td>
									<select class="select" typle="select" name="lly_opt_player_colour">
										<option value="dark"<?php if (get_option('lly_opt_player_colour') === 'dark') { echo ' selected="selected"'; } ?>>Dark (default)</option>
										<option value="light"<?php if (get_option('lly_opt_player_colour') === 'light') { echo ' selected="selected"'; } ?>>Light</option>
									</select>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Colour of progress bar</label></th>
						        <td>
									<select class="select" typle="select" name="lly_opt_player_colour_progress">
										<option value="red"<?php if (get_option('lly_opt_player_colour_progress') === 'red') { echo ' selected="selected"'; } ?>>Red (default)</option>
										<option value="white"<?php if (get_option('lly_opt_player_colour_progress') === 'white') { echo ' selected="selected"'; } ?>>White</option>
									</select>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Hide annotations <span class="newred grey">Tip</span></label></th>
						        <td>
									<input name="lly_opt_player_loadpolicy" type="checkbox" value="1" <?php checked( '1', get_option( 'lly_opt_player_loadpolicy' ) ); ?> /> <label>If checked, video annotations (like "subscribe to channel") will not be shown.</label>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Hide title/uploader</label></th>
						        <td>
									<input name="lly_opt_player_showinfo" type="checkbox" value="1" <?php checked( '1', get_option( 'lly_opt_player_showinfo' ) ); ?> /> <label>If checked, information like the video title and uploader will not be displayed when the video starts playing. This option only affects the playing video, not the video thumbnail.</label>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Hide related videos</label></th>
						        <td>
									<input name="lly_opt_player_relations" type="checkbox" value="1" <?php checked( '1', get_option( 'lly_opt_player_relations' ) ); ?> /> <label>If checked, related videos at the end of your videos will not be displayed.</label>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Hide player controls</label></th>
						        <td>
									<input name="lly_opt_player_controls" type="checkbox" value="1" <?php checked( '1', get_option( 'lly_opt_player_controls' ) ); ?> /> <label>If checked, Youtube player controls will not be displayed.</label>
						        </td>
					        </tr>
					        <tr valign="top">
						        <th scope="row"><label>Support for widgets</label></th>
						        <td>
									<input name="lly_opt_support_for_widgets" type="checkbox" value="1" <?php checked( '1', get_option( 'lly_opt_support_for_widgets' ) ); ?> /> <label>Only check this box if you actually use this feature (for reason of performance)! If checked, you can paste a Youtube URL into a text widget and it will be lazy loaded.</label>
						        </td>
					        </tr>
			        	</tbody>
		        	</table>
		        </div>

				<div id="vimeo">

					<h3>Lazy Load for Vimeo</h3>

					<table class="form-table">
						<tbody>
					        <tr valign="top">
						        <th scope="row"><label>Disable Lazy Load for Vimeo</label></th>
						        <td>
									<input name="llv_opt" type="checkbox" value="1" <?php checked( '1', get_option( 'llv_opt' ) ); ?> /> <label>If checked, Lazy Load will not be used for <b>Vimeo</b> videos.</label> <label><span style="color:#f60;">Important:</span> Updates on this option will only affect new posts and posts you update afterwards with the "Update Posts" button at the bottom of this form.</label>
						        </td>
					        </tr>
					        <tr valign="top">
						        <th scope="row"><label>Display Vimeo title</label></th>
						        <td>
									<input name="llv_opt_title" type="checkbox" value="1" <?php checked( '1', get_option( 'llv_opt_title' ) ); ?> /> <label>If checked, the Vimeo video title will be displayed on preview image.</label>
						        </td>
					        </tr>
					        <tr valign="top">
					        	<th scope="row"><label>Colour of the vimeo controls</label></th>
					        	<td>
					        		<input id="llv_picker_input_player_colour" class="ll_picker_player_colour picker-input" type="text" name="llv_opt_player_colour" data-default-color="#00adef" value="<?php if (get_option("llv_opt_player_colour") == "") { echo "#00adef"; } else { echo get_option("llv_opt_player_colour"); } ?>" />
					        	</td>
					        </tr>
			        	</tbody>
		        	</table>
		        </div>

				<?php do_action( 'lazyload_settings_page_tabs_after' ); ?>

			    <?php submit_button(); ?>
			</form>

	 		<div class="update-posts notice">
				<form action="options-general.php?page=<?php echo LL_ADMIN_URL; ?>" method="post">
				   <input type="hidden" name="update_posts" value="with_oembed" />
				   <input class="button update-posts" type="submit" value="Update Posts" />
				</form>
				<div class="help">
					<span class="tooltip-right info-icon" data-tooltip="Save changes first.">?</span> <span>Update posts to setup your plugin for the first time or when recommended somewhere.
				</div>
			</div>

			<?php require_once( 'inc/signup.php' ); ?>

		    <table class="form-table">
		        <tr valign="top">
			        <th scope="row" style="width:100px;"><a href="http://kevinw.de/ll" target="_blank"><img src="https://www.gravatar.com/avatar/9d876cfd1fed468f71c84d26ca0e9e33?d=http%3A%2F%2F1.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536&s=100" style="-webkit-border-radius:50%;-moz-border-radius:50%;border-radius:50%;"></a></th>
			        <td style="width:200px;">
			        	<p><a href="http://kevinw.de/ll" target="_blank">Kevin Weber</a> &ndash; that's me.<br>
			        	I'm the developer of this plugin. I hope you enjoy it!</p>
			        </td>
			        <td>
						<p>Another great plugin: <a href="http://kevinw.de/ll-ind" title="Inline Comments" target="_blank">Inline Comments</a>.</p>
			        </td>
			        <td>
						<p><b>It's free!</b> Support me with <a href="http://kevinw.de/donate/LazyLoadVideos/" title="Pay him something to eat" target="_blank">a delicious lunch</a> and give this plugin a 5 star rating <a href="http://wordpress.org/support/view/plugin-reviews/lazy-load-for-videos?filter=5" title="Vote for Lazy Load for Videos" target="_blank">on WordPress.org</a>.</p>
			        </td>
		        </tr>
		    </table>
		</div>
	<?php
	}

	function lazyload_admin_js() {
		if ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) {
			wp_enqueue_script( 'lazyload_admin_js', LL_URL . 'js/admin.js', array('jquery', 'jquery-ui-tabs', 'wp-color-picker' ), LL_VERSION );
		} else {
			wp_enqueue_script( 'lazyload_admin_js', LL_URL . 'js/min/admin-ck.js', array('jquery', 'jquery-ui-tabs', 'wp-color-picker' ), LL_VERSION );
		}
	}

	function lazyload_admin_css() {
		wp_enqueue_style( 'lazyload-admin-css', LL_URL . 'css/min/admin.min.css' );
		wp_enqueue_style( 'lazyload-admin-css-tooltips', LL_URL . 'css/min/admin-tooltips.min.css' );
		wp_enqueue_style( 'wp-color-picker' );	// Required for colour picker

		if ( is_rtl() ) {
			wp_enqueue_style( 'lazyload-admin-rtl', LL_URL . 'css/min/admin-rtl.min.css' );
		}
	}

}

function initialize_lazyloadvideos_admin() {
	new Lazyload_Videos_Admin();
}
add_action( 'init', 'initialize_lazyloadvideos_admin' );
