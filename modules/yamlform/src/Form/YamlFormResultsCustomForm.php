<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormRequestInterface;
use Drupal\yamlform\YamlFormSubmissionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for YAML form results custom(ize) form.
 */
class YamlFormResultsCustomForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_results_custom';
  }

  /**
   * The YAML form entity.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The YAML form source entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $sourceEntity;

  /**
   * The YAML form submission storage.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionStorageInterface
   */
  protected $submissionStorage;

  /**
   * YAML form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $yamlFormRequest;

  /**
   * Constructs a new YamlFormResultsDeleteBaseForm object.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionStorageInterface $yamlform_submission_storage
   *   The YAML form submission storage.
   * @param \Drupal\yamlform\YamlFormRequestInterface $yamlform_request
   *   The YAML form request handler.
   */
  public function __construct(YamlFormSubmissionStorageInterface $yamlform_submission_storage, YamlFormRequestInterface $yamlform_request) {
    $this->submissionStorage = $yamlform_submission_storage;
    $this->yamlFormRequest = $yamlform_request;
    list($this->yamlform, $this->sourceEntity) = $this->yamlFormRequest->getYamlFormEntities();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('yamlform_submission'),
      $container->get('yamlform.request')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $available_columns = $this->submissionStorage->getColumns($this->yamlform, $this->sourceEntity, NULL, TRUE);
    $custom_columns = $this->submissionStorage->getCustomColumns($this->yamlform, $this->sourceEntity, NULL, TRUE);

    // Change sid's # to an actual label.
    $available_columns['sid']['title'] = $this->t('Submission ID');
    if (isset($custom_columns['sid'])) {
      $custom_columns['sid']['title'] = $this->t('Submission ID');
    }

    $weight = 0;
    $delta = count($available_columns);
    $rows = [];

    // Display custom columns first.
    foreach ($custom_columns as $column_name => $column) {
      $rows[$column_name] = $this->buildRow($column_name, $column, TRUE, $weight++, $delta);
    }

    // Display available columns sorted alphabetically.
    // Get available sort options.
    $sort_options = [];
    $sort_columns = $available_columns;
    ksort($sort_columns);
    foreach ($sort_columns as $column_name => $column) {
      if (!isset($custom_columns[$column_name])) {
        $rows[$column_name] = $this->buildRow($column_name, $column, FALSE, $weight++, $delta);
      }

      if (!isset($column['sort']) || $column['sort'] === TRUE) {
        $sort_options[$column_name] = (string) $column['title'];
      };
    }
    asort($sort_options);

    // Please note, 'tableselect' element did not work correctly with
    // drag-n-drop behavior, so for now we are going to use a simple table.
    $form['columns'] = [
      '#type' => 'table',
      '#header' => [
        'name' => [
          'width' => '40px',
        ],
        'title' => $this->t('Title'),
        'weight' => $this->t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
    ] + $rows;

    // Sort and direction.
    $sort = $this->yamlform->getState($this->getStateKey('sort'), 'sid');
    $direction = $this->yamlform->getState($this->getStateKey('direction'), 'desc');
    $form['sort'] = [
      '#prefix' => '<div class="container-inline">',
      '#type' => 'select',
      '#field_prefix' => $this->t('Sort by'),
      '#options' => $sort_options,
      '#default_value' => $sort,
    ];
    $form['direction'] = [
      '#type' => 'select',
      '#field_prefix' => ' ' . $this->t('in') . ' ',
      '#field_suffix' => ' ' . $this->t('order.'),
      '#options' => [
        'asc' => $this->t('Ascending (ASC)'),
        'desc' => $this->t('Descending (DESC)'),
      ],
      '#default_value' => $direction,
      '#suffix' => '</div>',
    ];

    // Limit.
    $limit = $this->yamlform->getState($this->getStateKey('limit'), NULL);
    $form['limit'] = [
      '#type' => 'select',
      '#field_prefix' => $this->t('Show'),
      '#field_suffix' => $this->t('results per page.'),
      '#options' => [
        '20' => '20',
        '50' => '50',
        '100' => '100',
        '200' => '200',
        '500' => '500',
        '1000' => '1000',
        '0' => $this->t('All'),
      ],
      '#default_value' => ($limit != NULL) ? $limit : 50,
    ];

    // Default configuration.
    if (empty($this->sourceEntity)) {
      $form['default'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Use as default configuration'),
        '#description' => $this->t('If checked, the above settings will be used as the default configuration for YAML Form nodes.'),
        '#return_value' => TRUE,
        '#default_value' => $this->yamlform->getState($this->getStateKey('default'), TRUE),
      ];
    }

    // Build actions.
    $form['actions']['#type'] = 'actions';
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset'),
      '#attributes' => [
        'class' => ['button', 'button--danger'],
      ],
      '#access' => $this->yamlform->hasState($this->getStateKey('columns')),
      '#submit' => ['::delete'],
    ];
    return $form;
  }

  /**
   * Build table row for a results columns.
   *
   * @param string $column_name
   *   The column name.
   * @param array $column
   *   The column.
   * @param bool $default_value
   *   Whether the column should be checked.
   * @param int $weight
   *   The columns weights.
   * @param int $delta
   *   The max delta for the weight element.
   *
   * @return array
   *   A renderable containing a table row for a results column.
   */
  protected function buildRow($column_name, array $column, $default_value, $weight, $delta) {
    return [
      '#attributes' => ['class' => ['draggable']],
      'name' => [
        '#type' => 'checkbox',
        '#default_value' => $default_value,
      ],
      'title' => [
        '#markup' => $column['title'],
      ],
      'weight' => [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @label', ['@label' => $column['title']]),
        '#title_display' => 'invisible',
        '#attributes' => [
          'class' => ['table-sort-weight'],
        ],
        '#delta' => $delta,
        '#default_value' => $weight,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $columns = $form_state->getValue('columns');
    foreach ($columns as $column) {
      if ($column['name']) {
        return;
      }
    }
    $form_state->setErrorByName('columns', $this->t('At least once column is required'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Column.
    // Get custom columns are a simple sorted array.
    $columns = $form_state->getValue('columns');
    $custom_columns = [];
    foreach ($columns as $name => $column) {
      if ($column['name'] == 1) {
        $custom_columns[$column['weight']] = $name;
      }
    }
    ksort($custom_columns);
    array_values($custom_columns);
    $this->yamlform->setState($this->getStateKey('columns'), $custom_columns);

    // Set sort, direction, limit.
    $this->yamlform->setState($this->getStateKey('sort'), $form_state->getValue('sort'));
    $this->yamlform->setState($this->getStateKey('direction'), $form_state->getValue('direction'));
    $this->yamlform->setState($this->getStateKey('limit'), (int) $form_state->getValue('limit'));

    // Set default.
    if (empty($this->sourceEntity)) {
      $this->yamlform->setState($this->getStateKey('default'), $form_state->getValue('default'));
    }

    // Display message.
    drupal_set_message($this->t('The customized columns and results per page limit have been saved.'));

    // Set redirect.
    $route_name = $this->yamlFormRequest->getRouteName($this->yamlform, $this->sourceEntity, 'yamlform.results_table');
    $route_parameters = $this->yamlFormRequest->getRouteParameters($this->yamlform, $this->sourceEntity);
    $form_state->setRedirect($route_name, $route_parameters);
  }

  /**
   * Form delete customized columns handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function delete(array &$form, FormStateInterface $form_state) {
    $this->yamlform->deleteState($this->getStateKey('columns'));
    $this->yamlform->deleteState($this->getStateKey('sort'));
    $this->yamlform->deleteState($this->getStateKey('direction'));
    $this->yamlform->deleteState($this->getStateKey('limit'));
    $this->yamlform->deleteState($this->getStateKey('default'));
    drupal_set_message($this->t('The customized columns, sort by, and results per page limit have been reset.'));
  }

  /**
   * Get the state key for the custom data.
   *
   * @return string
   *   The state key for the custom data.
   */
  protected function getStateKey($name) {
    if ($source_entity = $this->sourceEntity) {
      return "results.custom.$name." . $source_entity->getEntityTypeId() . '.' . $source_entity->id();
    }
    else {
      return "results.custom.$name";
    }
  }

}
