<?php

/**
 * @file
 * Generate form elements for the font settings.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;

// Elements to apply fonts to.
$font_elements = font_elements();
$websafe_options = array();

// Websafe stacks and select options.
if ($settings_font_websafe = theme_get_setting('settings.font_websafe')) {
  $websafe_fonts = $settings_font_websafe;
}
else {
  $websafe_fonts = implode("\n", websafe_fonts());
}
if (!empty($websafe_fonts)) {
  $websafe_options = explode(PHP_EOL, $websafe_fonts);
}

// Font Options - here we must test if there are values set for each font type
// and populate the options list.
$font_options = array(
  'none' => t('-- none --'),
);

if (!empty($websafe_fonts)) {
  $font_options['websafe'] = t('Websafe fonts');
}
if (theme_get_setting('settings.font_google')) {
  $font_options['google'] = t('Google font');
}
if (theme_get_setting('settings.font_typekit')) {
  $font_options['typekit'] = t('Typekit');
}

$form['fonts'] = array(
  '#type' => 'details',
  '#title' => t('Fonts'),
  '#group' => 'extension_settings',
);

// Font Setup
$form['fonts']['setup'] = array(
  '#type' => 'details',
  '#title' => t('Fonts Setup'),
);

// Help
$form['fonts']['setup']['help'] = array(
  '#type' => 'container',
  '#markup' => t('First set the fonts you want to use in your site and save the Extension settings. Then apply fonts to specific elements.'),
);

// Websafe stacks
$form['fonts']['setup']['settings_font_websafe'] = array(
  '#type' => 'textarea',
  '#title' => t('Websafe font stacks'),
  '#rows' => 10,
  '#default_value' => $websafe_fonts,
  '#description' => t('Enter one font stack per line. Separate fonts with a comma, quote font names with spaces, e.g <code>"Times New Roman", Times, serif;</code>.'),
);

// Font Setup: Google font
$form['fonts']['setup']['settings_font_google'] = array(
  '#type' => 'textarea',
  '#rows' => 3,
  //'#maxlength' => 1024,
  '#title' => t('Google fonts'),
  '#default_value' => Xss::filter(theme_get_setting('settings.font_google')),
  '#description' => array(
    '#theme' => 'item_list',
    '#list_type' => 'ol',
    '#attributes' => array('class' => array('google-font-wizard-desc', 'web-font-desc')),
    '#items' => array(
      t('Use the <a href="@google_font_wizard" target="_blank">Google font wizard</a> to select your fonts.', array('@google_font_wizard' => 'https://fonts.google.com/')),
      t('Click the "Use" button, then copy/paste the URL from the <em>Standard</em> method, e.g. <code>https://fonts.googleapis.com/css?family=Open+Sans</code>'),
      t('Note: always use <code>https</code>, even with <code>http</code> and mixed content websites.'),
    ),
  ),
);

// Font Setup: Typekit
$form['fonts']['setup']['settings_font_typekit'] = array(
  '#type' => 'textfield',
  '#title' => t('Typekit ID'),
  '#default_value' => Html::escape(theme_get_setting('settings.font_typekit')),
  '#description' => array(
    '#theme' => 'item_list',
    '#list_type' => 'ol',
    '#attributes' => array('class' => array('typekit-font-desc', 'web-font-desc')),
    '#items' => array(
      t('Locate the <em>Embed Code</em> details for your kit.'),
      t('Copy/paste the ID, e.g. <code>okb4kwr</code>.'),
    ),
  ),
);

// Fallback
$form['fonts']['setup']['settings_font_fallback'] = array(
  '#type' => 'radios',
  '#title' => t('Fallback font family'),
  '#options' => array(
    'sans_serif' => t('sans-serif'),
    'serif' => t('serif'),
  ),
  '#default_value' => theme_get_setting('settings.font_fallback') ? theme_get_setting('settings.font_fallback') : 'sans_serif',
  '#description' => t('In the event a web-font does not load, set a fallback font family.'),
);

$form['fonts']['setup']['line_height'] = array(
  '#type' => 'details',
  '#title' => t('Line height multipliers'),
  '#description' => t('Multipliers are used to calculate the line-height for each font size. Normally this value will be between 1.0 and 3.0.'),
);

$form['fonts']['setup']['line_height']['settings_font_line_height_multiplier_default'] = array(
  '#type' => 'number',
  '#title' => t('Default'),
  '#max-length' => 3,
  '#step' => 0.1,
  '#default_value' => Html::escape(theme_get_setting('settings.font_line_height_multiplier_default')),
  '#attributes' => array(
    'min' => 1,
    'max' => 10,
    'step' => 0.1,
    'class' => array('font-option')
  ),
);

$form['fonts']['setup']['line_height']['settings_font_line_height_multiplier_large'] = array(
  '#type' => 'number',
  '#title' => t('Large font multiplier'),
  '#max-length' => 3,
  '#step' => 0.1,
  '#description' => t('Large fonts usually require a smaller multiplier.'),
  '#default_value' => Html::escape(theme_get_setting('settings.font_line_height_multiplier_large')),
  '#attributes' => array(
    'min' => 1,
    'max' => 10,
    'step' => 0.1,
    'class' => array('font-option')
  ),
);

$form['fonts']['setup']['line_height']['settings_font_line_height_multiplier_large_size'] = array(
  '#type' => 'number',
  '#title' => t('Large font size'),
  '#field_suffix' => 'px',
  '#max-length' => 2,
  '#description' => t('What is considered a large font?'),
  '#default_value' => Html::escape(theme_get_setting('settings.font_line_height_multiplier_large_size')),
  '#attributes' => array(
    'min' => 1,
    'max' => 99,
    'step' => 1,
    'class' => array('font-option')
  ),
);

// APPLY Fonts
$form['fonts']['apply'] = array(
  '#type' => 'details',
  '#title' => t('Apply Fonts'),
);

// Build form
foreach ($font_elements as $font_element_key => $font_element_values) {

  $form['fonts']['apply'][$font_element_key] = array(
    '#type' => 'details',
    '#title' => t($font_element_values['label']),
  );

  $form['fonts']['apply'][$font_element_key]['settings_font_' . $font_element_key] = array(
    '#type' => 'select',
    '#title' => t('Font type'),
    '#options' => $font_options,
    '#default_value' => theme_get_setting('settings.font_' . $font_element_key),
  );

  // Websafe fonts.
  if (isset($font_options['websafe'])) {
    $websafe_font_element_key_setting = theme_get_setting('settings.font_websafe_' . $font_element_key);
    $form['fonts']['apply'][$font_element_key]['settings_font_websafe_' . $font_element_key] = array(
      '#type' => 'select',
      '#title' => t('Select a font stack to apply to this element.'),
      '#options' => $websafe_options,
      '#default_value' => isset($websafe_font_element_key_setting) ? $websafe_font_element_key_setting : 0,
      '#states' => array(
        'visible' => array(
          'select[name="settings_font_' . $font_element_key . '"]' => array(
            'value' => 'websafe',
          ),
        ),
      ),
    );
  }

  // Google font.
  if (isset($font_options['google'])) {
    $form['fonts']['apply'][$font_element_key]['settings_font_google_' . $font_element_key] = array(
      '#type' => 'textfield',
      '#title' => t('Google font name'),
      '#description' => t('Enter the name of <b>one</b> Google font you set in <em>Fonts</em>. You can find this in step 4 of the Google font wizard. Quote names with a space e.g. <code>"Open Sans"</code>'),
      '#default_value' => Xss::filter(theme_get_setting('settings.font_google_' . $font_element_key)),
      '#states' => array(
        'visible' => array(
          'select[name="settings_font_' . $font_element_key . '"]' => array(
            'value' => 'google',
          ),
        ),
      ),
    );
  }

  // Typekit font.
  if (isset($font_options['typekit'])) {
    $form['fonts']['apply'][$font_element_key]['settings_font_typekit_' . $font_element_key] = array(
      '#type' => 'textfield',
      '#title' => t('Typekit font name'),
      '#description' => t('Enter the name of <b>one</b> Typekit font you set in <em>Fonts</em>. You can find the correct name to use by checking the kits selectors "Using fonts in CSS". Quote names with a space, e.g. <code>"Proxima nova"</code>'),
      '#default_value' => Xss::filter(theme_get_setting('settings.font_typekit_' . $font_element_key)),
      '#states' => array(
        'visible' => array(
          'select[name="settings_font_' . $font_element_key . '"]' => array(
            'value' => 'typekit',
          ),
        ),
      ),
    );
  }

  // Font size
  if ($font_element_key !== 'h1h4' && $font_element_key !== 'h5h6') {
    $form['fonts']['apply'][$font_element_key]['settings_font_size_' . $font_element_key] = array(
      '#type' => 'number',
      '#title' => t('Size'),
      '#field_suffix' => 'px <small>(coverts to rem)</small>',
      '#default_value' => Html::escape(theme_get_setting('settings.font_size_' . $font_element_key)),
      '#attributes' => array(
        'min' => 0,
        'max' => 999,
        'step' => 1,
        'class' => array('font-option')
      ),
    );
  }
  
  // Font smoothing
  $form['fonts']['apply'][$font_element_key]['settings_font_smoothing_' . $font_element_key] = array(
    '#type' => 'checkbox',
    '#title' => t('Apply font smoothing'),
    '#description' => t('Font smoothing only works in Mac OS X and may improve legibility, in particular for Google and Typekit fonts.'),
    '#default_value' => theme_get_setting('settings.font_smoothing_' . $font_element_key),
  );

  // Custom selectors has a textarea.
  if ($font_element_key === 'custom_selectors') {
    $form['fonts']['apply']['custom_selectors']['settings_custom_selectors'] = array(
      '#type' => 'textarea',
      '#title' => t('Custom Selectors'),
      '#rows' => 3,
      '#default_value' => Xss::filter(theme_get_setting('settings.custom_selectors')),
      '#description' => t("Enter a comma separated list of valid CSS selectors, with no trailing comma, such as <code>.node__content, .block__content </code>. Note that due to security reason you cannot use the greater than symbol (>) as a child combinator selector."),
      '#states' => array(
        'disabled' => array('select[name="settings_selectors_font_type"]' => array('value' => '<none>')),
      ),
    );
  }

  // Show the selectors this applies to.
  $form['fonts']['apply'][$font_element_key]['selector'] = array(
    '#type' => 'container',
    '#markup' => t('Applies to: @selectors' , array('@selectors' => $font_element_values['selector'])),
    '#attributes' => array('class' => array('font-selector-list')),
  );
}
