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
      'required_error' => '',
      'default_value' => '',

      'title_display' => '',
      'description_display' => '',
      'field_prefix' => '',
      'field_suffix' => '',
      'placeholder' => '',

      'unique' => FALSE,

      'admin_title' => '',
      'private' => FALSE,

      'format' => $this->getDefaultFormat(),

      'counter_type' => '',
      'counter_maximum' => '',
      'counter_message' => '',
      'rows' => '',

      'wrapper_attributes__class' => '',
      'wrapper_attributes__style' => '',
      'attributes__class' => '',
      'attributes__style' => '',

      'flex' => 1,
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
