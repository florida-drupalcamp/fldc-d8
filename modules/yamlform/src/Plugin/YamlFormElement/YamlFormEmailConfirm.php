<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'email_confirm' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_email_confirm",
 *   label = @Translation("Email confirm"),
 *   category = @Translation("Advanced")
 * )
 */
class YamlFormEmailConfirm extends Email {

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
      'private' => FALSE,
      'unique' => FALSE,

      'format' => $this->getDefaultFormat(),

      'size' => '',
      'maxlength' => '',
      'placeholder' => '',
      'pattern' => '',

      'confirm__title' => '',
      'confirm__description' => '',
      'confirm__placeholder' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['email_confirm'] = [
      '#type' => 'details',
      '#title' => $this->t('Email confirm settings'),
      '#open' => TRUE,
    ];
    $form['email_confirm']['confirm__title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email confirm title'),
    ];
    $form['email_confirm']['confirm__description'] = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Email confirm description'),
    ];
    $form['email_confirm']['confirm__placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email confirm placeholder'),
    ];
    return $form;
  }

}
