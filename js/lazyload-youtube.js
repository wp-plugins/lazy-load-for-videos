/*
 * Lazy Load Youtube
 * by Kevin Weber (kevinw.de)
 */

var $lly = jQuery.noConflict();

// Classes
var classBranding = 'lazyload-info-icon';
var classBrandingDot = '.' + classBranding;


var $lly_o;
var setOptionsYoutube = function(options) {
  $lly_o = $lly.extend({
      theme: 'dark', // possible: dark, light
      colour: 'red', // possible: red, white
      controls: true,
      relations: true,
      playlist: '',
    },
    options);
};

$lly(document).ready(function() {

  var doload_lly = function() {

    $lly("a.lazy-load-youtube").each(function(index) {
      var that = this;

      var embedparms = $lly(this).attr("href").split("/embed/")[1];
      if (!embedparms) {
        embedparms = $lly(this).attr("href").split("://youtu.be/")[1];
      }
      if (!embedparms) {
        embedparms = $lly(this).attr("href").split("v=")[1].replace(/\&/, '?');
      }
      var youid = embedparms.split("?")[0].split("#")[0];
      var start = embedparms.match(/[#&]t=(\d+)s/);
      if (start) {
        start = start[1];
      } else {
        start = embedparms.match(/[#&]t=(\d+)m(\d+)s/);
        if (start) {
          start = parseInt(start[1]) * 60 + parseInt(start[2]);
        } else {
          start = embedparms.match(/[?&]start=(\d+)/);
          if (start) {
            start = start[1];
          }
        }
      }

      /*
       * Load plugin info
       */
      var loadPluginInfo = function() {
        return '<a class="' + classBranding + '" href="http://kevinw.de/lazyloadvideos" title="Lazy Load for Videos by Kevin Weber" target="_blank">i</a>';
      };

      /*
       * Create info element
       */
      var createPluginInfo = function() {
        // source = Video
        var source = $lly(that);
        // element = Plugin info element
        var element = $lly( loadPluginInfo() );
        // Prepend element to source
        source.before( element );
      };

      createPluginInfo();

      var videoTitle = function() {
        if ( $lly(that).attr('video-title') !== undefined ) {
          return $lly(that).attr("video-title");
        }
        else if ( $lly(this).html() !== '' && $lly(this).html() !== undefined ) {
          return $lly(this).html();
        }
        else {
          return "";
        }
      };

      embedparms = embedparms.split("#")[0];
      if (start && embedparms.indexOf("start=") === -1) {
        embedparms += ((embedparms.indexOf("?") === -1) ? "?" : "&") + "start=" + start;
      }
      if (embedparms.indexOf("showinfo=0") !== -1) {
        $lly(this).html('');
      } else {
        $lly(this).html('<div class="lazy-load-youtube-info"><span class="titletext youtube">' + videoTitle() + '</span></div>');
      }

      $lly(this).prepend('<div style="height:' + (parseInt($lly(this).css("height")) - 4) + 'px;width:' + (parseInt($lly(this).css("width")) - 4) + 'px;" class="lazy-load-youtube-div"></div>');
      $lly(this).css("background", "#000 url(//i2.ytimg.com/vi/" + youid + "/0.jpg) center center no-repeat");
      $lly(this).attr("id", youid + index);
      $lly(this).attr("href", "//www.youtube.com/watch?v=" + youid + (start ? "#t=" + start + "s" : ""));
      var emu = '//www.youtube.com/embed/' + embedparms;

      /*
       * Configure URL parameters
       */
      var theme = '';
      if ($lly_o.theme !== theme && $lly_o.theme !== undefined && $lly_o.theme !== 'dark') {
        theme = '&theme=' + $lly_o.theme;
      }
      var colour = '';
      if ($lly_o.colour !== colour && $lly_o.colour !== undefined && $lly_o.colour !== 'red') {
        colour = '&color=' + $lly_o.colour;
      }
      var relations = '';
      if (!$lly_o.relations) {
        relations = '&rel=0';
      }
      var controls = '';
      if (!$lly_o.controls) {
        controls = '&controls=0';
      }
      var playlist = '';
      if ($lly_o.playlist !== playlist && $lly_o.playlist !== undefined) {
        playlist = '&playlist=' + $lly_o.playlist;
      }

      /*
       * Generate URL
       */
      emu += ((emu.indexOf("?") === -1) ? "?" : "&") + "autoplay=1" + theme + colour + controls + relations + playlist;

      /*
       * Generate iFrame
       */
      var videoFrame = '<iframe width="' + parseInt($lly(this).css("width")) + '" height="' + parseInt($lly(this).css("height")) + '" style="vertical-align:top;" src="' + emu + '" frameborder="0" allowfullscreen></iframe>';

      /*
       * Register "onclick" event handler
       */
      $lly( this ).on( "click", function() {
        $lly( this ).prev( classBrandingDot ).remove();
        $lly('#' + youid + index).replaceWith( videoFrame );
        return false;
      });
    });

  };

  /*
   * Use ajaxStop function to prevent plugin from breaking when another plugin uses Ajax
   */
  $lly(document).ready(doload_lly()).ajaxStop(function() {
    doload_lly();
  });

  /*
   * Prevent users from removing branding // YOU'RE NOT ALLOWED TO EDIT THE FOLLOWING LINES OF CODE
   */
  var displayBranding = function() {
    if ($lly_o.displayBranding !== false) {
      $lly(classBrandingDot).css({
        'display': 'block',
        'visibility': 'visible',
      });
    }
  };
  displayBranding();

});