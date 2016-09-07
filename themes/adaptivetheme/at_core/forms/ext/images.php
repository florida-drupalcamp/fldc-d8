<?php

/**
 * @file
 * Generate form elements for the Image styles settings.
 */

$image_alignment_options = array(
  'none'   => t('None'),
  'left'   => t('Left'),
  'center' => t('Center'),
  'right'  => t('Right'),
);

$form['images'] = array(
  '#type' => 'details',
  '#title' => t('Image Settings'),
  '#group' => 'extension_settings',
);

$form['images']['image-settings'] = array(
  '#type' => 'fieldset',
  '#title' => t('Image Settings'),
  '#weight' => 0,
);

$form['images']['image-settings']['description'] = array(
  '#markup' => '<h3>Image Settings</h3><p>Set alignment, caption display and image count per content type and display modes.</p>',
);

// Loop node types and set form elements for each type.
foreach ($node_types as $nt) {
  // Node type variables.
  $node_type = $nt->get('type');
  $node_type_name = $nt->get('name');

  $form['images']['image-settings']['node_type_' . $node_type] = array(
    '#type' => 'details',
    '#title' => t($node_type_name),
    '#collapsed'=> TRUE,
  );

  // Alignment settings.
  $form['images']['image-settings']['node_type_' . $node_type]['alignment'] = array(
    '#type' => 'details',
    '#title' => t('Alignment'),
    '#collapsed'=> TRUE,
  );

  // View modes
  foreach ($node_view_modes as $display_mode) {

    // View mode labels.
    $display_mode_label = t('Display mode: ') . $display_mode['label'];
    $display_mode_id = str_replace('.', '_', $display_mode['id']);

    // Alignment setting.
    $form['images']['image-settings']['node_type_' . $node_type]['alignment'][$display_mode_id]['settings_image_alignment_' . $node_type . '_' .  $display_mode_id] = array(
      '#type' => 'radios',
      '#title' => $display_mode_label,
      '#default_value' => theme_get_setting('settings.image_alignment_' . $node_type . '_' .  $display_mode_id),
      '#options' => $image_alignment_options,
      '#attributes' => array('class' => array('clearfix')),
    );
  }

  // Caption setting.
  $form['images']['image-settings']['node_type_' . $node_type]['captions'] = array(
    '#type' => 'details',
    '#title' => t('Captions'),
    '#collapsed'=> TRUE,
    '#description' => t('Show captions per display mode. Captions use the "Title" option and must be enabled in the image field settings.'),
  );

  // View modes
  foreach ($node_view_modes as $display_mode) {

    // View mode labels.
    $display_mode_label = $display_mode['label'];
    $display_mode_id = str_replace('.', '_', $display_mode['id']);

    $form['images']['image-settings']['node_type_' . $node_type]['captions'][$display_mode_id]['settings_image_captions_' . $node_type . '_' .  $display_mode_id] = array(
      '#type' => 'checkbox',
      '#title' => $display_mode_label,
      '#default_value' => theme_get_setting('settings.image_captions_' . $node_type . '_' .  $display_mode_id),
    );
  }

  // Image count settings.
  $form['images']['image-settings']['node_type_' . $node_type]['count'] = array(
    '#type' => 'details',
    '#title' => t('Image Count'),
    '#collapsed'=> TRUE,
    '#description' => t('Restrict to <b>one image</b> only in certain display modes - useful for teaser mode when you have multi-value or unlimited images.'),
  );

  // View modes
  foreach ($node_view_modes as $display_mode) {

    // View mode labels.
    $display_mode_label = $display_mode['label'];
    $display_mode_id = str_replace('.', '_', $display_mode['id']);

    $form['images']['image-settings']['node_type_' . $node_type]['count'][$display_mode_id]['settings_image_count_' . $node_type . '_' .  $display_mode_id] = array(
      '#type' => 'checkbox',
      '#title' => $display_mode_label,
      '#default_value' => theme_get_setting('settings.image_count_' . $node_type . '_' .  $display_mode_id),
    );
  }
}
