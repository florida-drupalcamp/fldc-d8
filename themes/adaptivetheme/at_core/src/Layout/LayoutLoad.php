<?php

/**
 * @file
 * Contains \Drupal\at_core\Layout\LayoutLoad
 */

namespace Drupal\at_core\Layout;

use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Html;

class LayoutLoad extends Layout {

  // The active theme name.
  protected $theme_name;

  // The active regions on page load.
  protected $active_regions;

  /**
   * LayoutInterface constructor.
   *
   * @param $theme_name
   * @param $active_regions
   */
  public function __construct($theme_name, $active_regions) {
    $this->theme_name = $theme_name;
    $this->active_regions = $active_regions;
    $layout_data = new LayoutCompatible($this->theme_name);
    $layout_compatible_data = $layout_data->getCompatibleLayout();
    $this->layout_config = $layout_compatible_data['layout_config'];
  }

  /**
   * Returns the active regions.
   *
   * @return mixed
   */
  public function activeRegions() {
    return $this->active_regions;
  }

  /**
   * Returns the source order attribute.
   *
   * @param $region
   * @return mixed
   */
  public function regionSourceOrder($region) {
    $region_source_order = array();
    foreach ($this->layout_config['rows'] as $row_name => $row_data) {
      foreach ($row_data['regions'] as $region_key => $region_values) {
        if ($region == $region_key) {
          $region_source_order[$region] = $region_values['order'];
        }
      }
    }

    return $region_source_order;
  }

  /**
   * Returns the row name for the region.
   *
   * @param $region
   * @return mixed
   */
  public function regionAttributes($region) {
    $region_row = '';

    // If rows are empty return early.
    if (empty($this->layout_config['rows'])) {
      return NULL;
    }

    foreach ($this->layout_config['rows'] as $row_name => $row_data) {
      foreach ($row_data['regions'] as $region_key => $region_name) {
        if ($region_key == $region) {
          $region_row = $row_name;
          break;
        }
      }
    }

    return $region_row;
  }

  /**
   * Builds and returns layout attributes.
   *
   * @return int|string|void
   */
  public function rowAttributes() {
    $variables = array();
    $active_row_regions = array();
    $config_settings = \Drupal::config($this->theme_name . '.settings')->get('settings');

    // If rows are empty return early.
    if (empty($this->layout_config['rows'])) {
      return null;
    }

    // Build array of rows with region values.
    foreach ($this->layout_config['rows'] as $row_name => $row_data) {

      // Set a bool for active regions, assume false.
      $variables[$row_name]['has_regions'] = FALSE;

      $i = 1;
      foreach ($row_data['regions'] as $region_key => $region_name) {
        $region_source_order[$row_name][$region_key] = $i++; // Set an increment value for each region for the .hr class (has-regions)
        $row_regions[$row_name][] = $region_key; // Build array to intersect and use for the .arc class (active region count).
      }

      // Pass on row wrapper attributes only for rows with active regions
      $active_row_regions[$row_name]['attributes'] = $row_data['attributes'];

      // Remove inactive regions. array_intersect_key()
      $active_row_regions[$row_name]['regions'] = array_intersect($row_regions[$row_name], $this->active_regions);

      // Unset inactive rows.
      if (empty($active_row_regions[$row_name]['regions'])) {
        unset($active_row_regions[$row_name]);
      }
    }

    // Set additional attributes for rows.
    foreach ($active_row_regions as $row_key => $row_values) {

      // If active regions set to true, print the row.
      $variables[$row_key]['has_regions'] = TRUE;

      // Wrapper attributes.
      $variables[$row_key]['wrapper_attributes'] = new Attribute;
      $variables[$row_key]['wrapper_attributes']['class'] = array('l-pr', 'page__row', 'pr-' . str_replace('_', '-', $row_key));

      // Wrapper attributes set in the layout yml file.
      foreach ($row_values['attributes'] as $attribute_type => $attribute_values) {
        if (is_array($attribute_values)) {
          $variables[$row_key]['wrapper_attributes'][$attribute_type] = array(implode(' ', $attribute_values));
        }
        else {
          $variables[$row_key]['wrapper_attributes'][$attribute_type] = array($attribute_values);
        }
      }

      // Set class multiple
      if (count($row_values['regions']) > 1) {
        $variables[$row_key]['wrapper_attributes']['class'][] = 'regions-multiple';
      }

      // Container attributes.
      $variables[$row_key]['container_attributes'] = new Attribute;
      $variables[$row_key]['container_attributes']['class'] = array('l-rw', 'regions', 'container', 'pr-'. str_replace('_', '-', $row_key) . '__rw');

      // Active Regions: "arc" is "active region count", this is number of
      // active regions in this row on this page.
      $variables[$row_key]['container_attributes']['class'][] = 'arc--'. count($row_values['regions']);

      // data attribute
      $variables[$row_key]['container_attributes']['data-at-regions'] = '';

      // Match each active region with its'corrosponding source order increment.
      foreach ($row_values['regions'] as $region) {
        if (isset($region_source_order[$row_key][$region])) {
          $row_has_regions[$row_key][] = $region_source_order[$row_key][$region];
        }
      }

      // Has Regions: the "hr" class tells us which regions are active by source
      // order (as per the layout markup yml), this allows us to set layout
      // depending on which regions are active.
      if (isset($row_has_regions[$row_key])) {
        $variables[$row_key]['container_attributes']['class'][] =  'hr--' . implode('-', $row_has_regions[$row_key]);
      }

      // Shortcode classes.
      if (isset($config_settings['enable_extensions']) && $config_settings['enable_extensions'] === 1) {
        if (isset($config_settings['enable_shortcodes']) && $config_settings['enable_shortcodes'] === 1) {

          // Wrapper codes
          if (!empty($config_settings['page_classes_row_wrapper_' . $row_key])) {
            $wrappercodes = Tags::explode($config_settings['page_classes_row_wrapper_' . $row_key]);
            foreach ($wrappercodes as $wrapperclass) {
              $variables[$row_key]['wrapper_attributes']['class'][] = Html::cleanCssIdentifier($wrapperclass);
            }
          }

          // Container codes
          if (!empty($config_settings['page_classes_row_container_' . $row_key])) {
            $containercodes = Tags::explode($config_settings['page_classes_row_container_' . $row_key]);
            foreach ($containercodes as $containerclass) {
              $variables[$row_key]['container_attributes']['class'][] = Html::cleanCssIdentifier($containerclass);
            }
          }
        }
      }
    }

    return $variables;
  }

  /**
   * Builds and returns layout attributes for the JS layout method.
   *
   * @return int|string|void
   */
  public function rowAttributesJS() {
    $variables = array();
    $config_settings = \Drupal::config($this->theme_name . '.settings')->get('settings');

    // If rows are empty return early.
    if (empty($this->layout_config['rows'])) {
      return NULL;
    }

    // Set additional attributes for rows.
    foreach ($this->layout_config['rows'] as $row_key => $row_values) {

      $row_key_class = str_replace('_', '-', $row_key);

      // Wrapper attributes.
      $variables[$row_key]['wrapper_attributes'] = new Attribute;
      $variables[$row_key]['wrapper_attributes']['class'] = array('l-pr', 'page__row', 'pr-' . $row_key_class);

      // Wrapper attributes set in the layout yml file.
      foreach ($row_values['attributes'] as $attribute_type => $attribute_values) {
        if (is_array($attribute_values)) {
          $variables[$row_key]['wrapper_attributes'][$attribute_type] = array(implode(' ', $attribute_values));
        }
        else {
          $variables[$row_key]['wrapper_attributes'][$attribute_type] = array($attribute_values);
        }
      }

      // Set class multiple
      if (count($row_values['regions']) > 1) {
        $variables[$row_key]['wrapper_attributes']['class'][] = 'regions-multiple';
      }

      // Container attributes.
      $variables[$row_key]['container_attributes'] = new Attribute;
      $variables[$row_key]['container_attributes']['class'] = array('l-rw', 'regions', 'container', 'pr-'. $row_key_class . '__rw');
      $variables[$row_key]['container_attributes']['data-at-regions'] = '';

      // Shortcode classes.
      if (isset($config_settings['enable_extensions']) && $config_settings['enable_extensions'] === 1) {
        if (isset($config_settings['enable_shortcodes']) && $config_settings['enable_shortcodes'] === 1) {

          // Wrapper codes
          if (!empty($config_settings['page_classes_row_wrapper_' . $row_key])) {
            $wrapper_codes = Tags::explode($config_settings['page_classes_row_wrapper_' . $row_key]);
            foreach ($wrapper_codes as $wrapper_class) {
              $variables[$row_key]['wrapper_attributes']['class'][] = Html::cleanCssIdentifier($wrapper_class);
            }
          }

          // Container codes
          if (!empty($config_settings['page_classes_row_container_' . $row_key])) {
            $container_codes = Tags::explode($config_settings['page_classes_row_container_' . $row_key]);
            foreach ($container_codes as $container_class) {
              $variables[$row_key]['container_attributes']['class'][] = Html::cleanCssIdentifier($container_class);
            }
          }
        }
      }
    }

    return $variables;
  }

}
