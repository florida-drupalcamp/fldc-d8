<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'flexbox' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_flexbox",
 *   label = @Translation("Flexbox layout (Experimental)"),
 *   category = @Translation("Container")
 *
 * )
 */
class YamlFormFlexbox extends Container {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'align_items' => 'flex-start',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\yamlform_ui\Form\YamlFormUiElementFormInterface $form_object */
    $form_object = $form_state->getFormObject();

    if ($form_object->isNew()) {
      $form['messages'] = [
        '#markup' => $this->t('Flexbox layouts are experimental and provided for testing purposes only. Use at your own risk.'),
        '#prefix' => '<div class="messages messages--warning">',
        '#suffix' => '</div>',
        '#access' => TRUE,
      ];
    }

    $form['flexbox'] = [
      '#type' => 'details',
      '#title' => $this->t('Flexbox settings'),
      '#open' => FALSE,
    ];
    $form['flexbox']['align_items'] = [
      '#type' => 'select',
      '#title' => $this->t('Align items'),
      '#options' => [
        'flex-start' => $this->t('Top (flex-start)'),
        'flex-end' => $this->t('Bottom (flex-end)'),
        'center' => $this->t('Center (center)'),
      ],
      '#required' => TRUE,
    ];
    return $form;
  }

}
