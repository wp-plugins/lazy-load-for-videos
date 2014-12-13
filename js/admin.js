(function( incom, $, undefined ) {

  $(document).ready(function() {
  	init();
  });

  var init = function() {
    handleTabs();
    addColourPicker();
    toggle();
    thumbnailQuality.init();
  };

  var thumbnailQuality = {
    init : function() {
      thumbnailQuality.changeVisibility();
      $("[name=lly_opt_thumbnail_quality]").change(function() {
        thumbnailQuality.changeVisibility();
      });
    },

    changeVisibility : function() {
      var $obj = $(".lly_opt_thumbnail_quality_max_force_wrapper");
      if( $('[name=lly_opt_thumbnail_quality]').find("option:selected").val() === "max") {
         $obj.show('fast');
      } else {
        $obj.hide();
      }
    }
  };

  /*
   * Handle jQuery tabs
   */
  var handleTabs = function() {
    $( "#tabs" ).tabs();

    handleTabs_URL();
    handleTabs_URL_scrollTop();
  };

  /*
   * Change URL when tab is clicked
   */
  var handleTabs_URL = function() {
    $( "#tabs" ).on( "tabsactivate", function( event, ui ) {
      var href = ui.newTab.children('li a').first().attr("href");
      history.pushState(null, null, href);
      if(history.pushState) {
          history.pushState(null, null, href);
      }
      else {
          location.hash = href;
      }
    });
  };

  /*
   * When user calls a URL that contains a hash, scroll to top
   */
  var handleTabs_URL_scrollTop = function() {
    setTimeout(function() {
      if (location.hash) {
        $( "html, body" ).animate({ scrollTop: 0 }, 1000);
      }
    }, 1);
  };


  var toggle = function() {
    $( '.toggle' ).on( 'click', function(e) {
      $( e.target ).siblings( '.toggle-me' ).toggle();
    });
  };

  var addColourPicker = function() {
    $('.ll_picker_player_colour').wpColorPicker();
  };

}( window.incom = window.incom || {}, jQuery ));
