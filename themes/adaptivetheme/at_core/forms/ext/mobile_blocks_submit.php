<?php

/**
 * @file
 * Save Mobile Blocks CSS to file.
 */

use Drupal\Component\Utility\Html;

/**
 * Submit Mobile Blocks settings.
 * @param $values
 * @param $theme
 * @param $generated_files_path
 */
function at_core_submit_mobile_blocks($values, $theme, $generated_files_path) {
  // Breakpoints.
  $breakpoints_module = \Drupal::moduleHandler()->moduleExists('breakpoint');
  if ($breakpoints_module == TRUE) {
    $breakpoint_groups = \Drupal::service('breakpoint.manager')->getGroups();
    $breakpoints = array();
    foreach ($breakpoint_groups as $group_key => $group_values) {
      $breakpoints[$group_key] = \Drupal::service('breakpoint.manager')->getBreakpointsByGroup($group_key);
    }
  }
  $mobile_blocks_breakpoint_group = theme_get_setting('settings.mobile_blocks_breakpoint_group', $theme) ?: 'at_core.simple';
  $mobile_blocks_breakpoints = $breakpoints[$mobile_blocks_breakpoint_group];

  // Active themes active blocks
  $block_module = \Drupal::moduleHandler()->moduleExists('breakpoint');
  if ($block_module == TRUE) {
    $theme_blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties(['theme' => $theme]);
  }
  else {
    $theme_blocks = NULL;
  }

  if (!empty($theme_blocks) && !empty($mobile_blocks_breakpoints)) {
    foreach (array_reverse($mobile_blocks_breakpoints) as $mbs_key => $mbs_value) {
      $mbs_query = $mbs_value->getMediaQuery();
      $mbs_breakpoints_all[$mbs_query] = $mbs_query;
      $mbs_label = strtolower($mbs_value->getLabel());

      $output[$mbs_label][] = '@media ' . $mbs_query . ' {' . "\n";

      foreach ($theme_blocks as $block_key => $block_values) {
        $block_id = $block_values->id();
        $block_selector[$mbs_label] = '#block-' . Html::cleanCssIdentifier($block_id);

        if (isset($values['settings_mobile_block_hide_' . 'bp' . $mbs_label . '_' . $block_id]) && $values['settings_mobile_block_hide_' . 'bp' . $mbs_label . '_' . $block_id] == 1) {
          $output[$mbs_label][] = '  ' . $block_selector[$mbs_label] . ' {display:none}' . "\n";
        }
        elseif (isset($values['settings_mobile_block_show_' . 'bp' . $mbs_label . '_' . $block_id]) && $values['settings_mobile_block_show_' . 'bp' . $mbs_label . '_' . $block_id] == 1) {
          $output[$mbs_label][] = '  ' . $block_selector[$mbs_label] . ' {display:block}' . "\n";
        }
      }

      $output[$mbs_label][] = '}';
      $mobile_blocks_css[] = implode($output[$mbs_label]);
    }

    $mobile_blocks_css = isset($mobile_blocks_css) ? implode("\n", $mobile_blocks_css) : '';
    if (!empty($mobile_blocks_css)) {
      $file_name = 'mobile-blocks.css';
      $filepath = $generated_files_path . '/' . $file_name;
      file_unmanaged_save_data($mobile_blocks_css, $filepath, FILE_EXISTS_REPLACE);
    }
  }
}
