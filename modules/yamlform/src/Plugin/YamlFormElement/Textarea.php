<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Component\Render\HtmlEscapedText;

/**
 * Provides a 'textarea' element.
 *
 * @YamlFormElement(
 *   id = "textarea",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Textarea.php/class/Textarea",
 *   label = @Translation("Textarea"),
 *   category = @Translation("Basic"),
 *   multiline = TRUE
 * )
 */
class Textarea extends TextBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'title' => '',
      'description' => '',

      'required' => FALSE,
      'default_value' => '',

      'title_display' => '',
      'description_display' => '',
      'prefix' => '',
      'suffix' => '',
      'field_prefix' => '',
      'field_suffix' => '',

      'private' => FALSE,
      'unique' => FALSE,

      'format' => $this->getDefaultFormat(),

      'counter_type' => '',
      'counter_maximum' => '',
      'counter_message' => '',
      'rows' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    $build = [
      '#markup' => nl2br(new HtmlEscapedText($value)),
    ];
    return \Drupal::service('renderer')->renderPlain($build);
  }

}
