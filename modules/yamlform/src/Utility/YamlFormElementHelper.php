<?php

namespace Drupal\yamlform\Utility;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Template\Attribute;

/**
 * Helper class YAML form element methods.
 */
class YamlFormElementHelper {

  /**
   * Determine if a form element's title is displayed.
   *
   * @param array $element
   *   A form element.
   *
   * @return bool
   *   TRUE if a form element's title is displayed.
   */
  public static function isTitleDisplayed(array $element) {
    return (!empty($element['#title']) && (empty($element['#title_display']) || !in_array($element['#title_display'], ['invisible', ['attribute']]))) ? TRUE : FALSE;
  }

  /**
   * Replaces all tokens in a given render element with appropriate values.
   *
   * @param array $element
   *   A render element.
   * @param array $data
   *   (optional) An array of keyed objects.
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the token
   *   replacement process.
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) An object to which static::generate() and the hooks and
   *   functions that it invokes will add their required bubbleable metadata.
   *
   * @see \Drupal\Core\Utility\Token::replace()
   */
  public static function replaceTokens(array &$element, array $data = [], array $options = [], BubbleableMetadata $bubbleable_metadata = NULL) {
    foreach ($element as $element_property => &$element_value) {
      // Most strings won't contain tokens so lets check and return ASAP.
      if (is_string($element_value) && strpos($element_value, '[') !== FALSE) {
        $element[$element_property] = \Drupal::token()->replace($element_value, $data, $options);
      }
      elseif (is_array($element_value)) {
        self::replaceTokens($element_value, $data, $options, $bubbleable_metadata);
      }
    }
  }

  /**
   * Get an associative array containing a render element's properties.
   *
   * @param array $element
   *   A render element.
   *
   * @return array
   *   An associative array containing a render element's properties.
   */
  public static function getProperties(array $element) {
    $properties = [];
    foreach ($element as $key => $value) {
      if (Element::property($key)) {
        $properties[$key] = $value;
      }
    }
    return $properties;
  }

  /**
   * Remove all properties from a render element.
   *
   * @param array $element
   *   A render element.
   *
   * @return array
   *   A render element with no properties.
   */
  public static function removeProperties(array $element) {
    foreach ($element as $key => $value) {
      if (Element::property($key)) {
        unset($element[$key]);
      }
    }
    return $element;
  }

  /**
   * Add prefix to all top level keys in an associative array.
   *
   * @param array $array
   *   An associative array.
   * @param string $prefix
   *   Prefix to be prepended to all keys.
   *
   * @return array
   *   An associative array with all top level keys prefixed.
   */
  public static function addPrefix(array $array, $prefix = '#') {
    $prefixed_array = [];
    foreach ($array as $key => $value) {
      if ($key[0] != $prefix) {
        $key = $prefix . $key;
      }
      $prefixed_array[$key] = $value;
    }
    return $prefixed_array;
  }

  /**
   * Remove prefix from all top level keys in an associative array.
   *
   * @param array $array
   *   An associative array.
   * @param string $prefix
   *   Prefix to be remove from to all keys.
   *
   * @return array
   *   An associative array with prefix removed from all top level keys.
   */
  public static function removePrefix(array $array, $prefix = '#') {
    $unprefixed_array = [];
    foreach ($array as $key => $value) {
      if ($key[0] == $prefix) {
        $key = preg_replace('/^' . $prefix . '/', '', $key);
      }
      $unprefixed_array[$key] = $value;
    }
    return $unprefixed_array;
  }

  /**
   * Fix form element #states attribute handling.
   *
   * @param array $element
   *   A form element that is missing the 'data-drupal-states' attribute.
   */
  public static function fixStates(array &$element) {
    if (isset($element['#states'])) {
      $attributes = ['class' => ['js-form-wrapper'], 'data-drupal-states' => Json::encode($element['#states'])];
      $element += ['#prefix' => '', '#suffix' => ''];
      $element['#prefix'] = '<div ' . new Attribute($attributes) . '>' . $element['#prefix'];
      $element['#suffix'] = $element['#suffix'] . '</div>';
    }
  }

}
