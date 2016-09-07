/**
 * @file
 * Appearance settings.
 */
(function ($, Drupal) {
  Drupal.behaviors.atCoreLayoutVisualization = {
    attach: function () {
      $('#edit-layout-select select[id*="edit-settings-page-"]').change(function(){
        $('#' + $(this).attr('id')).parent().next().children().removeClass().addClass(this.value);
      });
    }
  };
}(jQuery, Drupal));
