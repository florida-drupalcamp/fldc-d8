<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'date' element.
 *
 * @YamlFormElement(
 *   id = "date",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Date.php/class/Date",
 *   label = @Translation("Date"),
 *   category = @Translation("Date/time elements")
 * )
 */
class Date extends DateBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    $date_format = '';
    // Date formats cannot be loaded during install or update.
    if (!defined('MAINTENANCE_MODE')) {
      /** @var $date_format_entity \Drupal\Core\Datetime\DateFormatInterface */
      if ($date_format_entity = DateFormat::load('html_date')) {
        $date_format = $date_format_entity->getPattern();
      }
    }

    return parent::getDefaultProperties() + [
      'date_date_format' => $date_format,
      'min' => '',
      'max' => '',
      'step' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['date'] = [
      '#type' => 'details',
      '#title' => $this->t('Date settings'),
      '#open' => FALSE,
    ];
    $form['date']['date_date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format'),
      '#description' => $this->t('The date format used in PHP formats.'),
    ];
    $form['date']['min'] = [
      '#type' => 'date',
      '#title' => $this->t('Min'),
      '#description' => $this->t('Specifies the minimum date.'),
      '#size' => 4,
    ];
    $form['date']['max'] = [
      '#type' => 'date',
      '#title' => $this->t('Max'),
      '#description' => $this->t('Specifies the maximum date.'),
      '#size' => 4,
    ];
    $form['date']['step'] = [
      '#type' => 'number',
      '#title' => $this->t('Steps'),
      '#description' => $this->t('Specifies the legal number intervals.'),
      '#size' => 4,
    ];
    return $form;
  }

}
