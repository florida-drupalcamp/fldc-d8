<?php

/**
 * @file
 * Logs a notice of the custom color settings.
 *
 * If a custom color scheme has been created in the UI it is injected into the
 * schemes array and saved. You must rename the Custom scheme and give it a
 * unique array key before using the generated file in your theme.
 *
 * Note that color module validates the input of the color form and this is not
 * run if there is a problem, e.g. the user inputting non hexadecimal CSS color
 * strings, which color module validates to avoid XSS.
 */
function at_core_log_color_scheme(&$form, &$form_state) {
  $build_info = $form_state->getBuildInfo();
  $values     = $form_state->getValues();
  $theme      = $build_info['args'][0];
  $palette    = $values['palette'];
  $indent     = str_pad(' ', 6);
  $lines      = explode("\n", var_export($palette, TRUE));

  $message  = "    'PaletteName' => array(\n";
  $message .= $indent . "'title' => t('PaletteName'),\n";
  $message .= $indent . "'colors' => array(\n";
  $last_line = $indent . array_pop($lines) . ',';
  $message_scss = '';

  array_shift($lines);
  foreach ($lines as $line) {
    if (strpos($line, ' => ') !== FALSE) {
      $parts = explode(' => ', $line);
      $message .= $indent . $parts[0] . str_pad(' ', (52 - strlen($line))) . '=> ' . $parts[1];
    } else {
      $message .=  "$indent  $line";
    }
    $message .=  "\n";
  }

  foreach ($lines as $line) {
    if (strpos($line, ' => ') !== FALSE) {
      $parts = explode(' => ', $line);
      $part_0 = trim(str_replace("'", "", $parts[0]));
      $part_1 = trim(str_replace(",", ";", $parts[1]));
      $message_scss .= "$" . $part_0 . str_pad(' ', (52 - strlen($line))) . " : " . str_replace("'", "", $part_1) . "\n";
    }
  }

  $message .= "$last_line\n";
  $message .= "    ),\n";
  $message = '<pre>' . $message . "\n\n" .  $message_scss . '</pre>';

  \Drupal::logger($theme)->notice($message);

  // Hopefully this goes away if this ever lands, https://www.drupal.org/node/2415663
  // I'm not holding my breath.
  drupal_flush_all_caches();

  $obi_wan_quotes = array(
    "You'll find that many of the truths we cling to depend greatly on our own point of view.",
    "Only imperial storm troopers are so precise.",
    "You will never find a more wretched hive of scum and villainy.",
    "These aren't the droids you're looking for.",
    "I felt a great disturbance in the Force.",
    "Only a Sith Lord deals in absolutes.",
    "It's over, Anakin. I have the high ground.",
    "He will learn patience.",
    "Aren't you a little short for a Storm Trooper?",
    "You can't win, Darth.",
    "Use the Force, Luke.",
    "May the force be with you.",
  );
  $obi_k = array_rand($obi_wan_quotes);
  $obi_wan = $obi_wan_quotes[$obi_k];

  drupal_set_message(t('Color scheme logged. Cache cleared. <p>Obi Wan says... <em>"@obiwan"</em></p>', array('@obiwan' => $obi_wan)), 'status');
}
