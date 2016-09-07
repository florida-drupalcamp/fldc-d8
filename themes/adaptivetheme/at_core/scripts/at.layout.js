/**
 * @file
 * Load layout.
 */
(function ($, window) {

  "use strict";

  Drupal.behaviors.atLayoutLoad = {
    attach: function () {

      // Verify that the user agent understands media queries.
      if (!window.matchMedia('only screen').matches) {
        return;
      }

      // Never run this on really small devices.
      var notSmartPhone = window.matchMedia('(min-width: 320px)');

      if (notSmartPhone.matches) {
        $('.regions').each(function() {
          // Remove empty regions first, otherwise classes will be wrong.
          $(this).children().filter(function() {
            return !($.trim($(this).text()).length);
          }).remove();

          // Add classes.
          var active_regions = $(this).children().map(function() {
            return $(this).attr('data-at-region');
          }).get().join('-');
          if (active_regions) {
            var hr = 'hr--' + active_regions;
            var arc = 'arc--' + this.children.length;
            $(this).addClass(hr).addClass(arc).attr('data-at-regions', 'has-regions');
          }

          // Clean up empty parents.
          $(this).filter(function() {
            return !($.trim($(this).text()).length);
          }).parent().remove();
        });
      }
    }
  };
}(jQuery, window));
