<?php

/**
 * @file
 * Generate form elements for the Devel settings.
 */

// Development settings
$form['devel'] = array(
  '#type' => 'details',
  '#title' => t('Developer tools'),
  '#group' => 'extension_settings',
  '#description' => t('Tools to aid theme development.'),
);

// Show page suggestions.
$form['devel']['settings_show_page_suggestions'] = array(
  '#type' => 'checkbox',
  '#title' => t('Show Page Suggestions'),
  '#description' => t('Show all template suggestions for the current page. Appears in the messages area.'),
  '#default_value' => theme_get_setting('settings.show_page_suggestions', $theme),
);

// Window size
$form['devel']['settings_show_window_size'] = array(
  '#type' => 'checkbox',
  '#title' => t('Show Window Size'),
  '#description' => t('Shows the window width (in pixels and ems) in the bottom right corner of the screen. Works for any device or browser that supports JavaScript.'),
  '#default_value' => theme_get_setting('settings.show_window_size', $theme),
);

// Show grid
$form['devel']['settings_show_grid'] = array(
  '#type' => 'checkbox',
  '#title' => t('Show the grid'),
  '#default_value' => theme_get_setting('settings.show_grid', $theme),
  '#description' => t('Show the grid. Your theme must include a CSS file named "show-grid.css" in the /css folder which should load the grid CSS or image file. All UIKit based themes have this file and will show a toggle icon in the bottom left corner of the screen - hover the icon to show the grid as an overlay. The CSS grid overlay is an approximation. Browsers have extra trouble with sub-pixel rounding on background images. <b>This is meant for rough debugging, not for pixel-perfect measurements</b>.'),
);

// Debug layout
//$form['devel']['settings_devel_layout'] = array(
//  '#type' => 'checkbox',
//  '#title' => t('Debug Layout <small>(hides all content, colorizes all rows and regions)</small>'),
//  '#default_value' => theme_get_setting('settings.devel_layout', $theme),
//  '#description' => t('An aggressive option that removes all content, hides the toolbar, and colorizes page rows and regions. Works with LiveReload.'),
//);

// Colorize regions.
$form['devel']['settings_devel_color_regions'] = array(
  '#type' => 'checkbox',
  '#title' => t('Colorize regions'),
  '#default_value' => theme_get_setting('settings.devel_color_regions', $theme),
  '#description' => t('Add background color to regions. Also adds a margin-bottom for visual separation.'),
  '#states' => array(
    //'disabled' => array('input[name="settings_devel_layout"]' => array('checked' => TRUE)),
  ),
);

// Neutralize Toolbar.
$form['devel']['settings_nuke_toolbar'] = array(
  '#type' => 'checkbox',
  '#title' => t('Neutralize Toolbar'),
  '#default_value' => theme_get_setting('settings.nuke_toolbar', $theme),
  '#description' => t('Completely removes the toolbar in the front end by hiding it with CSS and overriding all it\'s CSS rules.'),
);
