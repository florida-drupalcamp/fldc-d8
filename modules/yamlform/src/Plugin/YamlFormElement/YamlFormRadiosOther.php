<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'radios_other' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_radios_other",
 *   label = @Translation("Radios other"),
 *   category = @Translation("Options")
 * )
 */
class YamlFormRadiosOther extends Radios {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'other__title' => '',
      'other__placeholder' => '',
      'other__description' => '',
      'other__size' => '',
      'other__maxlength' => '',
    ];
  }

}
