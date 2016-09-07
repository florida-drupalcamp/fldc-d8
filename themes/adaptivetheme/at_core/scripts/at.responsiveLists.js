/**
 * @file
 * Responsive Lists
 * This is based on the the Seven themes responsive tabs JS, except I have
 * generalized it so we can use it for any list. Also we use outerWidth and
 * calculate the total width of all list items.
 *
 * In AT we use this for tabs (local tasks), pagers and breadcrumbs.
 * See the breadcrumbs template and SCSS partial to see how this works.
 */
(function ($, Drupal, window) {

  'use strict';

  function init(i, list_item) {

    var list_item = $(list_item);

    function handleResize(e) {
      list_item.addClass('is-horizontal');

      var lists = list_item.find('.is-responsive__list');
      var list_items_width = 0;

      lists.find('.is-responsive__item').each(function() {
        list_items_width += $(this).outerWidth(true);
      });

      var isHorizontal = lists.outerWidth(true) <= list_items_width;

      if (isHorizontal ==  true) {
        list_item.removeClass('is-horizontal').addClass('is-vertical');
      } else {
        list_item.removeClass('is-vertical').addClass('is-horizontal');
      }
    }

    $(window).on('resize.lists', Drupal.debounce(handleResize, 150)).trigger('resize.lists');
  }

  // Initialize the Responsive lists JS.
  Drupal.behaviors.atRL = {
    attach: function (context) {
      var list_items = $(context).find('[data-at-responsive-list]');
      if (list_items.length) {
        list_items.once().each(init);
      }
    }
  };
})(jQuery, Drupal, window);
