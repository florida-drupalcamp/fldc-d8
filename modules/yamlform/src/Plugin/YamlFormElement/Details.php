<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'details' element.
 *
 * @YamlFormElement(
 *   id = "details",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Details.php/class/Details",
 *   label = @Translation("Details"),
 *   category = @Translation("Container")
 * )
 */
class Details extends ContainerBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'open' => FALSE,
    ];
  }

}
