<?php

/**
 * @file
 * Generate form elements for the Responsive Menu settings.
 */

$responsive_menu_breakpoint_group = theme_get_setting('settings.responsive_menu_breakpoint_group', $theme);
$responsive_menu_breakpoints = $breakpoints[$responsive_menu_breakpoint_group];

// Breakpoint options
$rmb_group_options = array();
foreach ($responsive_menu_breakpoints as $rmb_key => $rmb_value) {
  $rmb_group_options[$rmb_value->getMediaQuery()] = $rmb_value->getLabel() . ': ' . $rmb_value->getMediaQuery();
}

// Menu blocks
if (!empty($theme_blocks)) {
  foreach ($theme_blocks as $block_key => $block_values) {
    $block_plugin = $block_values->get('plugin');
    $block_settings = $block_values->get('settings');
    if (in_array('system_menu_block', explode(':', $block_plugin))) {
      $menu_blocks[$block_values->id()] = $block_settings['label'];
    }
  }
}
else {
  $menu_blocks['bummer'] = '-- no menu blocks available --';
}

// menu style options
$responsive_menu_options = array(
  'none' => t('-- none --'),
  'horizontal' => t('Horizontal'),
  'vertical'   => t('Vertical'),
  'dropmenu'   => t('Drop menu'),
  'slidedown'  => t('Slide down'),
  'meganav'    => t('Mega nav'),
  'offcanvas'  => t('Off canvas'),
  'overlay'    => t('Overlay'),
  'tiles'      => t('Tiles'),
);

$tiles_count = array(
  'two' => 2,
  'three' => 3,
  'four' => 4,
);

$form['responsive_menus'] = array(
  '#type' => 'details',
  '#title' => t('Responsive Menus'),
  '#group' => 'extension_settings',
  '#description' => t('<h3>Responsive Menus</h3><p>Select a menu and breakpoint group, then a specific breakpoint for the responsive style. You can configure one default style and optionally a responsive style.</p><p>It is recommended to follow a mobile first approach where the <i>responsive style</i> is the one you typically associate with <i>desktop view</i>, and the <i>default style</i> is for small screens such as mobile touch devices.</p>'),
);

$form['responsive_menus']['default_settings'] = array(
  '#type' => 'fieldset',
  '#attributes' => array('class' => array('clearfix')),
);

// Menu
$form['responsive_menus']['default_settings']['settings_responsive_menu_block'] = array(
  '#type' => 'select',
  '#title' => t('Menu'),
  '#options' => $menu_blocks,
  '#default_value' => theme_get_setting('settings.responsive_menu_block', $theme),
);

// Breakpoints group
$form['responsive_menus']['default_settings']['settings_responsive_menu_breakpoint_group'] = array(
  '#type' => 'select',
  '#title' => t('Breakpoint group'),
  '#options' => $breakpoint_options,
  '#default_value' => $responsive_menu_breakpoint_group,
);

// Breakpoint
$form['responsive_menus']['default_settings']['settings_responsive_menu_breakpoint'] = array(
  '#type' => 'select',
  '#title' => t('Breakpoint'),
  '#options' => $rmb_group_options,
  '#default_value' => theme_get_setting('settings.responsive_menu_breakpoint', $theme),
  '#states' => array(
    'enabled' => array('select[name="settings_responsive_menu_breakpoint_group"]' => array('value' => $responsive_menu_breakpoint_group)),
  ),
);

// Change message
$form['responsive_menus']['default_settings']['responsive_menu_breakpoint_group_haschanged'] = array(
  '#type' => 'container',
  '#markup' => t('<em>Save the extension settings to change the breakpoint group and update breakpoint options.</em>'),
  '#attributes' => array('class' => array('warning', 'messages', 'messages--warning')),
  '#states' => array(
    'invisible' => array('select[name="settings_responsive_menu_breakpoint_group"]' => array('value' => $responsive_menu_breakpoint_group)),
  ),
);

// Menu styles
$form['responsive_menus']['styles'] = array(
  '#type' => 'fieldset',
  '#attributes' => array('class' => array('responsive-menu-styles')),
  '#states' => array(
    'enabled' => array('select[name="settings_responsive_menu_breakpoint_group"]' => array('value' => $responsive_menu_breakpoint_group)),
  ),
);

// Default
$form['responsive_menus']['styles']['settings_responsive_menu_default_style'] = array(
  '#type' => 'select',
  '#title' => t('Default style'),
  '#options' => $responsive_menu_options,
  '#default_value' => theme_get_setting('settings.responsive_menu_default_style', $theme),
);

// Responsive
$form['responsive_menus']['styles']['settings_responsive_menu_responsive_style'] = array(
  '#type' => 'select',
  '#title' => t('Responsive style'),
  '#options' => $responsive_menu_options,
  '#default_value' => theme_get_setting('settings.responsive_menu_responsive_style', $theme),
);

// Click menus
$form['responsive_menus']['click_menus'] = array(
  '#type' => 'details',
  '#title' => t('Click Menus'),
  //'#description' => t('These settings only apply to the responsive style (i.e. the "desktop style").'),
);

$form['responsive_menus']['click_menus']['settings_click_menus_enabled'] = array(
  '#type' => 'checkbox',
  '#title' => t('Use folding click menus for Off Canvas, Slide Down, Overlay and Vertical menu items.'),
  '#default_value' => theme_get_setting('settings.click_menus_enabled', $theme),
);

if (!isset($getThemeInfo['base theme original'])) {
  $form['responsive_menus']['click_menus']['upgrade_message'] = array(
    '#type' => 'container',
    '#markup' => t('Themes generated with AT RC1 and prior are not compatible with click menus, <a href="@upgrade_message" target="_blank"><b>click here for upgrade instructions</b></a>.', array('@upgrade_message' => 'https://www.drupal.org/node/2753187')),
  );
  $form['responsive_menus']['click_menus']['settings_click_menus_enabled']['#disabled'] = TRUE;
}

// Position
$form['responsive_menus']['position'] = array(
  '#type' => 'details',
  '#title' => t('Responsive Style Position'),
  '#description' => t('These settings only apply to the responsive style (i.e. the "desktop style").'),
);

$form['responsive_menus']['position']['settings_responsive_menu_vertical_position'] = array(
  '#type' => 'radios',
  '#title' => t('Vertical position'),
  '#options' => array(
    'top' => t('Top'),
    'center' => t('Center'),
    'bottom' => t('Bottom'),
    'none' => t('None'),
  ),
  '#default_value' => theme_get_setting('settings.responsive_menu_vertical_position', $theme),
  '#attributes' => array('class' => array('visually-hidden-off', 'radios-inline')),
);

$form['responsive_menus']['position']['settings_responsive_menu_horizontal_position'] = array(
  '#type' => 'radios',
  '#title' => t('Horizontal position'),
  '#options' => array(
    'left' => t('Left'),
    'center' => t('Center'),
    'right' => t('Right'),
    'none' => t('None'),
  ),
  '#default_value' => theme_get_setting('settings.responsive_menu_horizontal_position', $theme),
  '#attributes' => array('class' => array('visually-hidden-off', 'radios-inline')),
);
