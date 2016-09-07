<?php

/**
 * @file
 * Generate form elements for the Layout settings.
 */

use Drupal\at_core\Layout\LayoutCompatible;
use Drupal\Component\Utility\Html;

$layout_data = new LayoutCompatible($theme);
$layout_compatible_data = $layout_data->getCompatibleLayout();

$layout_config = $layout_compatible_data['layout_config'];
$css_config = $layout_compatible_data['css_config'];


// Prepare variables for getting the visual layout thingee CSS file.
$provider_path = drupal_get_path('theme', $css_config['layout_provider']) . '/layout/' . $css_config['layout'];

// Breakpoints
$breakpoints_group_layout = theme_get_setting('settings.breakpoint_group_layout', $theme);
$layout_breakpoints = $breakpoints[$breakpoints_group_layout];

// Template suggestions
$template_suggestions = array();
$template_suggestions['page'] = 'page';

// Get the suggestions from config.
// Each time a new suggestion is created we will save it to config settings during submit.
foreach ($config as $config_key => $config_value) {
  if (substr($config_key, 0, 16) == 'suggestion_page_') {
    if (!empty($config_value) && $config_value !== 'page') {
      $clean_config_value = Html::escape($config_value);
      $template_suggestions['page__' . $clean_config_value] = 'page__' . $clean_config_value;
    }
  }
}

// Checkbox setting that keeps the layouts details form open.
$layouts_form_open = theme_get_setting('settings.layouts_form_open', $theme);

$form['layouts'] = array(
  '#type' => 'details',
  '#title' => t('Layouts'),
  '#open'=> $layouts_form_open,
  '#attributes' => array('class' => array('clearfix')),
  '#weight' => -200,
);

// Attached required CSS and JS libraries and files.
$form['layouts']['#attached']['library'][] = "$theme/layout_settings";

// Enable layouts, this is a master setting that totally disables the page layout system.
$form['layouts']['layouts-enable-container'] = array(
  '#type' => 'container',
  '#attributes' => array('class' => array('subsystem-enabled-container', 'layouts-column-onequarter'))
);

$form['layouts']['layouts-enable-container']['settings_layouts_form_open'] = array(
  '#type' => 'checkbox',
  '#title' => t('Keep open'),
  '#default_value' => $layouts_form_open,
  '#states' => array(
    'disabled' => array('input[name="settings_layouts_enable"]' => array('checked' => FALSE)),
  ),
);

$form['layouts']['layouts-enable-container']['settings_layouts_enable'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable'),
  '#default_value' => theme_get_setting('settings.layouts_enable', $theme),
);

//
// Layout SELECT
// ---------------------------------------------------------------------------------

$form['layouts']['layout_select'] = array(
  '#type' => 'fieldset',
  '#title' => t('Select Layouts'),
  '#attributes' => array('class' => array('layouts-column', 'layouts-column-threequarters', 'column-select-layouts')),
  '#states' => array(
    'visible' => array('input[name="settings_layouts_enable"]' => array('checked' => TRUE)),
  ),
);

// Push hidden settings into the form so they can be used during submit, to build the css output, saves us
// having to get this data again during submit.
$form['layouts']['layout_select']['settings_suggestions'] = array(
  '#type' => 'hidden',
  '#value' => $template_suggestions,
);

foreach ($template_suggestions as $suggestion_key => $suggestions_name) {
  if ($suggestions_name == 'page') {
    $suggestions_name = 'page (default)';
  }
  else {
    $suggestions_name = str_replace('__', ' ', $suggestions_name);
  }

  $form['layouts']['layout_select'][$suggestion_key] = array(
    '#type' => 'details',
    '#title' => t($suggestions_name),
    '#attributes' => array('class' => array('clearfix')),
    '#states' => array(
      'enabled' => array('select[name="breakpoint_group_layout"]' => array('value' => $breakpoints_group_layout)),
    ),
  );

  if ($suggestion_key !== 'page') {
    $form['layouts']['layout_select'][$suggestion_key]['delete_suggestion_' . $suggestion_key] = array(
      '#type' => 'checkbox',
      '#title' => t('Delete this suggestion.'),
      '#default_value' => FALSE,
    );
  }

  if (!empty($layout_breakpoints)) {
    foreach ($layout_breakpoints as $layout_breakpoint_id => $layout_breakpoint_value) {

      $breakpoint_layout_label = $layout_breakpoint_value->getLabel();
      $breakpoint_layout_mediaquery = $layout_breakpoint_value->getMediaQuery();

      // There is probably a way to get the bp machine name but I could not find a method.
      $breakpoint_layout_key = strtolower(preg_replace("/\W|_/", "", $breakpoint_layout_label));

      $form['layouts']['layout_select'][$suggestion_key][$breakpoint_layout_key] = array(
        '#type' => 'details',
        '#title' => t($breakpoint_layout_label . ' <small>' . $breakpoint_layout_mediaquery . '</small>'),
        '#attributes' => array('class' => array('clearfix')),
      );

      if (!empty($layout_config['rows'])) {
        foreach ($layout_config['rows'] as $row_key => $row_values) {

          // CSS files
          $reg_count[$row_key] = count($row_values['regions']);

          foreach ($css_config['css'] as $css_key => $css_values) {
            if ($css_values['regions'] == $reg_count[$row_key]) {
              foreach ($css_values['files'] as $css_file) {
                $css_options[$row_key][$css_file] =  str_replace('-', ' ', $css_file); // convert to associative array, we need the key
              }
            }
          }

          // Only print rows that have regions, maybe they don't...
          if ($reg_count[$row_key]) {

            // Build markup for the visual display thingee.
            $regions_markup = array();
            $markup[$row_key] = '';
            $reg_num = 1;

            if ($reg_count[$row_key] > 1) {
              for ($i=0; $i<$reg_count[$row_key]; $i++) {
                $regions_markup[$row_key][] = '<div class="l-r region"><span>R' . $reg_num++ . '</span></div>';
              }
              $markup[$row_key] = implode('', $regions_markup[$row_key]);
            }
            else {
              $markup[$row_key] = '<div class="l-r region"><span>R1</span></div>';
            }

            // Try to inherit the default page layout, by default.
            if (NULL !== theme_get_setting('settings.' . $suggestion_key . '_' . $breakpoint_layout_key . '_' . $row_key)) {
              $row_default_value = theme_get_setting('settings.' . $suggestion_key . '_' . $breakpoint_layout_key . '_' . $row_key);
            }
            else {
              $row_default_value = theme_get_setting('settings.page_' . $breakpoint_layout_key . '_' . $row_key);
            }

            $form['layouts']['layout_select'][$suggestion_key][$breakpoint_layout_key][$row_key] = array(
              '#type' => 'fieldset',
              '#title' => t($row_key),
            );

            $form['layouts']['layout_select'][$suggestion_key][$breakpoint_layout_key][$row_key]['settings_' . $suggestion_key . '_' . $breakpoint_layout_key . '_' . $row_key] = array(
              '#type' => 'select',
              '#empty_option' => '--none--',
              '#title' => t(ucfirst(str_replace('_', ' ', $row_key))),
              '#options' => $css_options[$row_key],
              '#default_value' => $row_default_value,
            );

            $form['layouts']['layout_select'][$suggestion_key][$breakpoint_layout_key][$row_key]['css-options-visuals'] = array(
              '#type' => 'container',
              '#attributes' => array('class' => array('css-layout-options', 'layouts-column-onequarter', 'pull-right')),
            );

            $form['layouts']['layout_select'][$suggestion_key][$breakpoint_layout_key][$row_key]['css-options-visuals'][$suggestion_key . '-' . $breakpoint_layout_key . '-' . $row_key . '-row_region_markup'] = array(
              '#type' => 'container',
              '#markup' => '<div class="l-rw regions"><div class="arc--' . $reg_count[$row_key] . '">' . $markup[$row_key] . '</div></div>',
              '#attributes' => array('class' => array('css-layout-option-not-set', $row_default_value)),
            );
          }
        }
      }
    }
  }
}

// Suggestions container.
$form['layouts']['layout_select']['suggestions'] = array(
  '#type' => 'details',
  '#title' => t('Add new suggestion'),
);

// Suggestions input and help.
//$suggestion_plugin_message = isset($default_plugin) ? $default_plugin : '-- not set --';
$form['layouts']['layout_select']['suggestions']['ts_name'] = array(
  '#type' => 'textfield',
  '#size' => 20,
  '#field_prefix' => 'page--',
  '#field_suffix' => '.html.twig',
  '#description' => array(
    '#theme' => 'item_list',
    '#list_type' => 'ol',
    '#attributes' => array('class' => array('suggestions-ts-name-desc')),
    '#items' => array(
      t('Enter the template suggestion. Only enter the modifier, e.g. for "page--front" enter "front" (without quotes).'),
      t('Save the layout settings.'),
      t('After saving the suggestion configure a layout for it. If no layout is set it will use the default layout.'),
    ),
    '#suffix' => t('<p>Find page suggestions by turning on the Devel extension in Advanced settings and enable the option: <em>Show Page Suggestions</em>. Reload a page in the site and the suggestions will be shown in the messages area.</p>'),
  ),
);

// Layout OPTIONS
// ---------------------------------------------------------------------------------
$form['layouts']['adv_options'] = array(
  '#type' => 'fieldset',
  '#title' => t('Options'),
  '#attributes' => array('class' => array('layouts-column', 'layouts-column-onequarter')),
  '#states' => array(
    'visible' => array('input[name="settings_layouts_enable"]' => array('checked' => TRUE)),
  ),
);

$form['layouts']['adv_options']['description'] = array(
  '#markup' => t('<h3>Options</h3>'),
);

// Layout method.
$form['layouts']['adv_options']['method'] = array(
  '#type' => 'details',
  '#title' => t('Layout method'),
  '#description' => t('By default layout classes are printed in twig templates. Alternatively you can use JavaScript. This will work-around the issue where <a href="@bugincore" target="_blank">Drupal may print empty regions</a> and break the layout. JS layout takes time to load and may feel slower overall.', array('@bugincore' => 'https://www.drupal.org/node/953034')),
  '#collapsed' => TRUE,
  '#collapsible' => TRUE,
);

$form['layouts']['adv_options']['method']['settings_layout_method'] = array(
  '#type' => 'checkbox',
  '#title' => t('Use JavaScript to set the layout'),
  '#default_value' => theme_get_setting("settings.layout_method", $theme),
);

if (!isset($layout_config['js_layout']) || $layout_config['js_layout'] == FALSE) {
  $form['layouts']['adv_options']['method']['settings_layout_method']['#default_value'] = 0;
  $form['layouts']['adv_options']['method']['settings_layout_method']['#disabled'] = TRUE;
  $form['layouts']['adv_options']['method']['settings_layout_method']['#description'] = t('This layout is not compatible with the JS layout method.');
}

// Breakpoint group.
$form['layouts']['adv_options']['breakpoint_group'] = array(
  '#type' => 'details',
  '#title' => t('Breakpoints'),
  '#description' => t('Select the breakpoint group. You must save the layout settings for it to take effect, then reconfigure your layouts.'),
);

$form['layouts']['adv_options']['breakpoint_group']['settings_breakpoint_group_layout'] = array(
  '#type' => 'select',
  '#options' => $breakpoint_options,
  '#title' => t('Breakpoint group'),
  '#default_value' => $breakpoints_group_layout,
);

foreach($breakpoints as $group_message_key => $group_message_values)  {
  if ($group_message_values !== array()) {
    foreach ($group_message_values as $breakpoint_message_key => $breakpoint_message_values) {
      $breakpoint_message[$group_message_key][] = '<dt>' . $breakpoint_message_values->getLabel() . ':</dt><dd>' . $breakpoint_message_values->getMediaQuery() . '</dd>';
    }
    $form['layouts']['adv_options']['breakpoint_group'][$group_message_key]['bygroup_breakpoints'] = array(
      '#type' => 'container',
      '#markup' => '<dl class="breakpoint-group-values">' . implode("\n", $breakpoint_message[$group_message_key]) . '</dl>',
      '#states' => array(
        'visible' => array('select[name="settings_breakpoint_group_layout"]' => array('value' => $group_message_key)),
      ),
    );
  }
}

// Change message
$form['layouts']['adv_options']['breakpoint_group']['layouts_breakpoint_group_haschanged'] = array(
  '#type' => 'container',
  '#markup' => t('<em>Save the layout settings to change the breakpoint group and update the layout breakpoints.</em>'),
  '#attributes' => array('class' => array('warning', 'messages', 'messages--warning')),
  '#states' => array(
    'invisible' => array('select[name="settings_breakpoint_group_layout"]' => array('value' => $breakpoints_group_layout)),
  ),
);

// Max width.
$max_width_units = array(
  'em'  => 'em',
  'rem' => 'rem',
  '%'   => '%',
  'vw'  => 'vw',
  'px'  => 'px',
);

$form['layouts']['adv_options']['select']['max_width'] = array(
  '#type' => 'details',
  '#title' => t('Max width'),
  '#collapsed' => TRUE,
  '#collapsible' => TRUE,
  '#description' => t('Override the global max-width and per row.'),
);

$form['layouts']['adv_options']['select']['max_width']['settings_max_width_enable'] = array(
  '#type' => 'checkbox',
  '#title' => t('Override max-widths'),
  '#default_value' => theme_get_setting('settings.max_width_enable'),
);

$form['layouts']['adv_options']['select']['max_width']['global'] = array(
  '#type' => 'details',
  '#title' => t('Global max width'),
  '#collapsed' => FALSE,
  '#collapsible' => TRUE,
  '#states' => array(
    'invisible' => array('input[name="settings_max_width_enable"]' => array('checked' => FALSE)),
  ),
);

$form['layouts']['adv_options']['select']['max_width']['global']['settings_max_width_value'] = array(
  '#type' => 'number',
  '#title' => t('Value'),
  '#default_value' => Html::escape(theme_get_setting('settings.max_width_value')),
  '#attributes' => array(
    'min' => 0,
    'max' => 9999,
    'step' => 1,
  ),
);

$form['layouts']['adv_options']['select']['max_width']['global']['settings_max_width_unit'] = array(
  '#type' => 'select',
  '#title' => t('Unit'),
  '#options' => $max_width_units,
  '#default_value' => theme_get_setting('settings.max_width_unit'),
);

$form['layouts']['adv_options']['select']['max_width']['settings_max_width_enable_rows'] = array(
  '#type' => 'checkbox',
  '#title' => t('Override max-width per row'),
  '#default_value' => theme_get_setting('settings.max_width_enable_rows'),
  '#states' => array(
    'invisible' => array('input[name="settings_max_width_enable"]' => array('checked' => FALSE)),
  ),
);

if (!empty($layout_config['rows'])) {
  foreach ($layout_config['rows'] as $row_key => $row_values) {

    $form['layouts']['adv_options']['select']['max_width'][$row_key] = array(
      '#type' => 'details',
      '#title' => t(str_replace('_', ' ', $row_key)),
      '#collapsed' => TRUE,
      '#collapsible' => TRUE,
      '#states' => array(
        'invisible' => array('input[name="settings_max_width_enable_rows"]' => array('checked' => FALSE)),
      ),
    );

    $form['layouts']['adv_options']['select']['max_width'][$row_key]['settings_max_width_value_' . $row_key] = array(
      '#type' => 'number',
      '#title' => t('Value'),
      '#default_value' => Html::escape(theme_get_setting('settings.max_width_value_' . $row_key)),
      '#attributes' => array(
        'min' => 0,
        'max' => 9999,
        'step' => 1,
      ),
    );

    $form['layouts']['adv_options']['select']['max_width'][$row_key]['settings_max_width_unit_' . $row_key] = array(
      '#type' => 'select',
      '#title' => t('Unit'),
      '#options' => $max_width_units,
      '#default_value' => theme_get_setting('settings.max_width_unit_' . $row_key),
    );
  }
}

// Backups.
$form['layouts']['adv_options']['backups'] = array(
  '#type' => 'details',
  '#title' => t('Backups'),
  '#description' => t('Adaptivetheme can automatically save backups for page templates and your themes info.yml file, since both of these can change when you save a layout. Backups are saved to your themes "backup" folder.'),
  '#collapsed' => TRUE,
  '#collapsible' => TRUE,
);

// Disable backups.
$form['layouts']['adv_options']['backups']['settings_enable_backups'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable backups'),
  '#default_value' => theme_get_setting("settings.enable_backups", $theme),
  //'#description' => t('Warning: un-checking this option will disable backups.'),
);

// Submit button for layouts.
$form['layouts']['actions'] = array(
  '#type' => 'actions',
  '#attributes' => array('class' => array('submit--layout')),
);

$form['layouts']['actions']['submit'] = array(
  '#type' => 'submit',
  '#value' => t('Save layout settings'),
  '#validate'=> array('at_core_validate_layouts'),
  '#submit'=> array('at_core_submit_layouts'),
  '#button_type' => 'primary',
);

// Layout submit handlers.
include_once(drupal_get_path('theme', 'at_core') . '/forms/layout/layouts_validate.php');
include_once(drupal_get_path('theme', 'at_core') . '/forms/layout/layouts_submit.php');
