<?php

namespace Drupal\yamlform\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\Core\Url;
use Drupal\yamlform\Utility\YamlFormElementHelper;
use Drupal\yamlform\Utility\YamlFormReflectionHelper;
use Drupal\yamlform\YamlFormElementManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for all YAML form elements.
 */
class YamlFormPluginElementController extends ControllerBase {

  /**
   * A element manager.
   *
   * @var \Drupal\Core\Render\ElementInfoManagerInterface
   */
  protected $elementManager;

  /**
   * A YAML form element plugin manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $yamlFormElementManager;

  /**
   * Constructs a YamlFormPluginBaseController object.
   *
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $element_manager
   *   A element plugin manager.
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $yamlform_element_manager
   *   A YAML form element plugin manager.
   */
  public function __construct(ElementInfoManagerInterface $element_manager, YamlFormElementManagerInterface $yamlform_element_manager) {
    $this->elementManager = $element_manager;
    $this->yamlFormElementManager = $yamlform_element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.element_info'),
      $container->get('plugin.manager.yamlform.element')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function index() {
    $yamlform_form_element_rows = [];
    $element_rows = [];

    $default_properties = [
      '#title',
      '#description',
      '#required',
      '#default_value',
      '#title_display',
      '#description_display',
      '#prefix',
      '#suffix',
      '#field_prefix',
      '#field_suffix',
      '#private',
      '#unique',
      '#format',
    ];
    $default_properties = array_combine($default_properties, $default_properties);

    // Test element is only enabled if the YAML Form Devel and UI module are
    // enabled.
    $test_element_enabled = (\Drupal::moduleHandler()->moduleExists('yamlform_devel') && \Drupal::moduleHandler()->moduleExists('yamlform_ui')) ? TRUE : FALSE;

    // Define a default element used to get default properties.
    $element = ['#type' => 'element'];

    $element_plugin_definitions = $this->elementManager->getDefinitions();
    foreach ($element_plugin_definitions as $element_plugin_id => $element_plugin_definition) {
      if ($this->yamlFormElementManager->hasDefinition($element_plugin_id)) {

        /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
        $yamlform_element = $this->yamlFormElementManager->createInstance($element_plugin_id);
        $yamlform_element_plugin_definition = $this->yamlFormElementManager->getDefinition($element_plugin_id);

        $parent_classes = YamlFormReflectionHelper::getParentClasses($yamlform_element, 'YamlFormElementBase');

        $default_format = $yamlform_element->getDefaultFormat();
        $format_names = array_keys($yamlform_element->getFormats());
        $formats = array_combine($format_names, $format_names);
        if (isset($formats[$default_format])) {
          $formats[$default_format] = '<b>' . $formats[$default_format] . '</b>';
        }

        $related_types = $yamlform_element->getRelatedTypes($element);

        $definitions = [
          'value' => $yamlform_element->hasValue($element),
          'container' => $yamlform_element->isContainer($element),
          'root' => $yamlform_element->isRoot($element),
          'hidden' => $yamlform_element->isHidden($element),
          'multiline' => $yamlform_element->isMultiline($element),
          'multiple' => $yamlform_element->hasMultipleValues($element),
        ];
        $settings = [];
        foreach ($definitions as $key => $value) {
          $settings[] = '<b>' . $key . '</b>: ' . ($value ? $this->t('Yes') : $this->t('No'));
        }

        $properties = array_keys(YamlFormElementHelper::addPrefix($yamlform_element->getDefaultProperties()));
        foreach ($properties as &$property) {
          if (!isset($default_properties[$property])) {
            $property = '<b>' . $property . '</b>';
          }
        }
        if (count($properties) >= 20) {
          $properties = array_slice($properties, 0, 20) + ['...' => '...'];
        }
        $operations = [];
        if ($test_element_enabled) {
          $operations['test'] = [
            'title' => $this->t('Test'),
            'url' => new Url('yamlform.element_plugins.test', ['type' => $element_plugin_id]),
          ];
        }
        if ($api_url = $yamlform_element->getPluginApiUrl()) {
          $operations['documentation'] = [
            'title' => $this->t('API Docs'),
            'url' => $api_url,
          ];
        }
        $yamlform_form_element_rows[$element_plugin_id] = [
          'data' => [
            new FormattableMarkup('<div class="yamlform-form-filter-text-source">@id</div>', ['@id' => $element_plugin_id]),
            $yamlform_element->getPluginLabel(),
            ['data' => ['#markup' => implode('<br/> → ', $parent_classes)], 'nowrap' => 'nowrap'],
            ['data' => ['#markup' => implode('<br/>', $settings)], 'nowrap' => 'nowrap'],
            ['data' => ['#markup' => implode('<br/>', $properties)]],
            $formats ? ['data' => ['#markup' => '• ' . implode('<br/>• ', $formats)], 'nowrap' => 'nowrap'] : '',
            $related_types ? ['data' => ['#markup' => '• ' . implode('<br/>• ', $related_types)], 'nowrap' => 'nowrap'] : '<' . $this->t('none') . '>',
            $element_plugin_definition['provider'],
            $yamlform_element_plugin_definition['provider'],
            $operations ? ['data' => ['#type' => 'operations', '#links' => $operations]] : '',
          ],
        ];
      }
      else {
        $element_rows[$element_plugin_id] = [
          $element_plugin_id,
          $element_plugin_definition['provider'],
        ];
      }
    }

    $build = [];

    $build['filter'] = [
      '#type' => 'search',
      '#title' => $this->t('Filter'),
      '#title_display' => 'invisible',
      '#size' => 30,
      '#placeholder' => $this->t('Filter by element name'),
      '#attributes' => [
        'class' => ['yamlform-form-filter-text'],
        'data-element' => '.yamlform-element-plugin',
        'title' => $this->t('Enter a part of the handler name to filter by.'),
        'autofocus' => 'autofocus',
      ],
    ];

    ksort($yamlform_form_element_rows);
    $build['yamlform_elements'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Label'),
        $this->t('Class hierarchy'),
        $this->t('Definition'),
        $this->t('Properties'),
        $this->t('Formats'),
        $this->t('Related'),
        $this->t('Provided by'),
        $this->t('Integrated by'),
        $this->t('Operations'),
      ],
      '#rows' => $yamlform_form_element_rows,
      '#attributes' => [
        'class' => ['yamlform-element-plugin'],
      ],
    ];

    ksort($element_rows);
    $build['elements'] = [
      '#type' => 'details',
      '#title' => $this->t('Additional elements'),
      '#description' => $this->t('Below are element that are available to a YAML form but do not have YAML form element plugin and/or require any additional integration'),
      'table' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Provided by'),
        ],
        '#rows' => $element_rows,
        '#sticky' => TRUE,
      ],
    ];

    $build['#attached']['library'][] = 'yamlform/yamlform.admin';
    $build['#attached']['library'][] = 'yamlform/yamlform.form';

    return $build;
  }

  /**
   * Get a class's name without its namespace.
   *
   * @param string $class
   *   A class.
   *
   * @return string
   *   The class's name without its namespace.
   */
  protected function getClassName($class) {
    $parts = preg_split('#\\\\#', $class);
    return end($parts);
  }

}
