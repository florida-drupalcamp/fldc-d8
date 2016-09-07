/**
 * @file
 * Initialize fastclick.
 */
(function ($, document) {
  Drupal.behaviors.ATfastclickInitialize = {
    attach: function (context) {
      FastClick.attach(document.body);
    }
  };
}(jQuery, document));
