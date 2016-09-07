(function ($) {

  "use strict";

  /**
   * Renders a widget for displaying the current width of the browser.
   */
  Drupal.behaviors.atWS = {
    attach: function (context, settings) {

      var indicator = $('<div class="window-size-indicator wsi" />').appendTo(document.body),
          indicator_px = $('<div class="wsi__px" />').appendTo(indicator),
          indicator_em = $('<div class="wsi__em" />').appendTo(indicator),
          indicator_bp = $('<div class="wsi__bp" />').appendTo(indicator);

      // Bind to the window.resize event to continuously update the width.
      $(window).bind('resize.window-size-indicator', function () {
        indicator_px.html($(this).width() + 'px');
        indicator_em.html($(this).width() /16 + 'em');
      }).trigger('resize.window-size-indicator');

      // Buggy...
      /*
      function registerEnquire(breakpoint_label, breakpoint_query) {
        enquire.register(breakpoint_query, {
          match: function() {
            indicator_bp.html(breakpoint_label);
          },
          unmatch: function() {
            indicator_bp.html('');
          },
        });
      }

      var activeTheme = settings['ajaxPageState']['theme'];
      var bp = settings[activeTheme]['at_breakpoints'];

      for (var item in bp) {
        if (bp.hasOwnProperty(item)) {
          registerEnquire(item, bp[item]['mediaquery']);
        }
      }
      */
    }
  };
})(jQuery);
