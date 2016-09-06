/**
 * @file
 * Javascript behaviors for YAML form custom #states.
 */

(function ($, Drupal) {

  'use strict';

  var $document = $(document);
  $document.on('state:visible', function (e) {
    if (e.trigger && !e.value) {
      // @see https://www.sitepoint.com/jquery-function-clear-form-data/
      $(':input', e.target).andSelf().each(function() {
        var type = this.type;
        var tag = this.tagName.toLowerCase(); // normalize case
        if (type == 'text' || type == 'password' || tag == 'textarea') {
          this.value = '';
        }
        else if (type == 'checkbox' || type == 'radio') {
          this.checked = false;
        }
        else if (tag == 'select') {
          this.selectedIndex = -1;
        }
      });
    }
  });

})(jQuery, Drupal);
