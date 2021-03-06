<?php

namespace Drupal\yamlform\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;
use Drupal\yamlform\YamlFormRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for YAML form submissions.
 */
class YamlFormSubmissionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * YAML form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $yamlFormRequest;

  /**
   * Constructs a new YamlFormSubmissionController object.
   *
   * @param \Drupal\yamlform\YamlFormRequestInterface $yamlform_request
   *   The YAML form request handler.
   */
  public function __construct(YamlFormRequestInterface $yamlform_request) {
    $this->yamlFormRequest = $yamlform_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('yamlform.request')
    );
  }

  /**
   * Returns a YAML form submission in a specified format type.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param string $type
   *   The format type.
   *
   * @return array
   *   A render array representing a YAML form submission in a specified format
   *   type.
   */
  public function index(YamlFormSubmissionInterface $yamlform_submission, $type) {
    if ($type == 'default') {
      $type = 'html';
    }

    $build = [];
    $source_entity = $this->yamlFormRequest->getCurrentSourceEntity('yamlform_submission');
    // Navigation.
    $build['navigation'] = [
      '#theme' => 'yamlform_submission_navigation',
      '#yamlform_submission' => $yamlform_submission,
      '#source_entity' => $source_entity,
      '#rel' => $type,
    ];

    // Information.
    $build['information'] = [
      '#theme' => 'yamlform_submission_information',
      '#yamlform_submission' => $yamlform_submission,
      '#source_entity' => $source_entity,
      '#open' => FALSE,
    ];

    // Submission.
    $build['submission'] = [
      '#theme' => 'yamlform_submission_' . $type,
      '#yamlform_submission' => $yamlform_submission,
      '#source_entity' => $source_entity,
    ];

    // Wrap plain text and YAML in CodeMirror view widget.
    if (in_array($type, ['text', 'yaml'])) {
      $build['submission'] = [
        '#theme' => 'yamlform_codemirror',
        '#code' => $build['submission'],
        '#type' => $type,
      ];
    }

    $build['#attached']['library'][] = 'yamlform/yamlform.admin';

    return $build;
  }

  /**
   * Toggle YAML form submission sticky.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that toggle the sticky icon.
   */
  public function sticky(YamlFormSubmissionInterface $yamlform_submission) {
    // Toggle sticky.
    $yamlform_submission->setSticky(!$yamlform_submission->isSticky())->save();

    // Get state.
    $state = $yamlform_submission->isSticky() ? 'on' : 'off';

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand(
      '#yamlform-submission-' . $yamlform_submission->id() . '-sticky',
      new FormattableMarkup('<span class="yamlform-icon yamlform-icon-sticky yamlform-icon-sticky--@state"></span>', ['@state' => $state])
    ));
    return $response;

  }

  /**
   * Route title callback.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   The YAML form submission.
   *
   * @return array
   *   The YAML form submission as a render array.
   */
  public function title(YamlFormSubmissionInterface $yamlform_submission) {
    $source_entity = $this->yamlFormRequest->getCurrentSourceEntity('yamlform_submission');
    $t_args = [
      '@form' => ($source_entity) ? $source_entity->label() : $yamlform_submission->getYamlForm()->label(),
      '@id' => $yamlform_submission->id(),
    ];
    return $this->t('@form: Submission #@id', $t_args);
  }

}
