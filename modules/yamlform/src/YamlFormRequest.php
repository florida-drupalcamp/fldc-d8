<?php

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Handles YAML form requests.
 */
class YamlFormRequest implements YamlFormRequestInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   */
  protected $entityManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a YamlFormSubmissionExporter object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(EntityManagerInterface $entity_manager, RouteMatchInterface $route_match) {
    $this->entityManager = $entity_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentSourceEntity($ignored_types = NULL) {
    $entity_types = $this->entityManager->getEntityTypeLabels();
    if ($ignored_types) {
      if (is_array($ignored_types)) {
        $entity_types = array_diff_key($entity_types, array_flip($ignored_types));
      }
      else {
        unset($entity_types[$ignored_types]);
      }
    }
    foreach ($entity_types as $entity_type => $entity_label) {
      $entity = $this->routeMatch->getParameter($entity_type);
      if ($entity instanceof EntityInterface) {
        return $entity;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentYamlForm() {
    $source_entity = self::getCurrentSourceEntity('yamlform');
    if ($source_entity && method_exists($source_entity, 'hasField') && $source_entity->hasField('yamlform')) {
      return $source_entity->yamlform->entity;
    }
    else {
      return $this->routeMatch->getParameter('yamlform');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlFormEntities() {
    $yamlform = $this->getCurrentYamlForm();
    $source_entity = $this->getCurrentSourceEntity('yamlform');
    return [$yamlform, $source_entity];
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlFormSubmissionEntities() {
    $yamlform_submission = $this->routeMatch->getParameter('yamlform_submission');
    $source_entity = $this->getCurrentSourceEntity('yamlform_submission');
    return [$yamlform_submission, $source_entity];
  }

  /****************************************************************************/
  // Routing helpers
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getRouteName(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL, $route_name) {
    return $this->getBaseRouteName($yamlform_entity, $source_entity) . '.' . $route_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL) {
    // Get source entity from the YAML form submission.
    if (!$source_entity && $yamlform_entity instanceof YamlFormSubmissionInterface) {
      $source_entity = $yamlform_entity->getSourceEntity();
    }

    if (self::isValidSourceEntity($yamlform_entity, $source_entity)) {
      if ($yamlform_entity instanceof YamlFormSubmissionInterface) {
        return [
          'yamlform_submission' => $yamlform_entity->id(),
          $source_entity->getEntityTypeId() => $source_entity->id(),
        ];
      }
      else {
        return [$source_entity->getEntityTypeId() => $source_entity->id()];
      }
    }
    elseif ($yamlform_entity instanceof YamlFormSubmissionInterface) {
      return [
        'yamlform_submission' => $yamlform_entity->id(),
        'yamlform' => $yamlform_entity->getYamlForm()->id(),
      ];
    }
    else {
      return [$yamlform_entity->getEntityTypeId() => $yamlform_entity->id()];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseRouteName(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL) {
    if ($yamlform_entity instanceof YamlFormSubmissionInterface) {
      $yamlform = $yamlform_entity->getYamlForm();
      $source_entity = $yamlform_entity->getSourceEntity();
    }
    elseif ($yamlform_entity instanceof YamlFormInterface) {
      $yamlform = $yamlform_entity;
    }
    else {
      throw new \InvalidArgumentException('YAML form entity');
    }

    if (self::isValidSourceEntity($yamlform, $source_entity)) {
      return 'entity.' . $source_entity->getEntityTypeId();
    }
    else {
      return 'entity';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isValidSourceEntity(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL) {
    if ($yamlform_entity instanceof YamlFormSubmissionInterface) {
      $yamlform = $yamlform_entity->getYamlForm();
      $source_entity = $yamlform_entity->getSourceEntity();
    }
    elseif ($yamlform_entity instanceof YamlFormInterface) {
      $yamlform = $yamlform_entity;
    }
    else {
      throw new \InvalidArgumentException('YAML form entity');
    }

    if ($source_entity
      && method_exists($source_entity, 'hasField')
      && $source_entity->hasField('yamlform')
      && $source_entity->yamlform->target_id == $yamlform->id()
    ) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
