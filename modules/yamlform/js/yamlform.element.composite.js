/**
 * @file
 * YAML form composite element handler.
 */

(function ($, Drupal) {

  "use strict";

  /**
   * Attach handlers to composite element required.
   */
  Drupal.behaviors.yamlFormCompositeRequired = {
    attach: function (context) {
      $(context).find('#edit-properties-required').once().click(function () {
        // If the main required properties is checked off, check required for
        // all composite elements.
        var $input = $('input[name$="__required]"]');
        if (this.checked) {
          $input.attr('checked', 'checked').attr('readonly', 'readonly');
        }
        else {
          $input.removeAttr('readonly');
        }
      });
    }
  };

})(jQuery, Drupal);
