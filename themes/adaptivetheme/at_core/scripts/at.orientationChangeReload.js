/**
 * @file
 * Force reloads on orientation change.
 */
(function () {
  Drupal.behaviors.atOrientationChangeReload = {
    attach: function () {
      window.addEventListener('orientationchange', function () {
        document.body.style.display = 'none';
        window.location.reload();
      });
    }
  };
}());
