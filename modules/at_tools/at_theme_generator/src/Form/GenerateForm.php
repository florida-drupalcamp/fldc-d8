<?php

namespace Drupal\at_theme_generator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Uuid;
use Drupal\Component\Utility\Html;
use Drupal\Component\Serialization\Yaml;
use Drupal\at_theme_generator\File\FileOperations;
use Drupal\at_theme_generator\File\DirectoryOperations;
use Drupal\at_theme_generator\Theme\ThemeInfo;

/**
 * @file
 * Generator form.
 */
class GenerateForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'at_generator_form';
  }

  /**
   */
  private $themeInfoData;

  /**
   */
  private $themeSettingsInfo;

  /**
   */
  private $sourceThemeOptions;

  /**
   */
  private $listInfo;

  /**
   */
  public function __construct() {
    $this->themeInfoData = \Drupal::service('theme_handler')->rebuildThemeData();
    $this->listInfo = \Drupal::service('theme_handler')->listInfo();
    $this->themeSettingsInfo = new ThemeInfo('at_core');
    $this->sourceThemeOptions = $this->themeSettingsInfo->baseThemeOptions();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $base_themes = $this->sourceThemeOptions;
    $at_core = array_key_exists('at_core', $this->themeInfoData) ? TRUE : FALSE;

    $form['#attached']['library'][] = 'at_theme_generator/theme_generator';

    $form['generate'] = array(
      '#type' => 'container',
      '#tree' => TRUE,
    );

    $form['generate']['docs'] = array(
      '#type' => 'container',
      '#markup' => t('<a class="at-docs" href="@docs" target="_blank" title="External link: docs.adaptivethemes.com/theme-generator">View generator documentation <svg class="docs-ext-link-icon" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1408 928v320q0 119-84.5 203.5t-203.5 84.5h-832q-119 0-203.5-84.5t-84.5-203.5v-832q0-119 84.5-203.5t203.5-84.5h704q14 0 23 9t9 23v64q0 14-9 23t-23 9h-704q-66 0-113 47t-47 113v832q0 66 47 113t113 47h832q66 0 113-47t47-113v-320q0-14 9-23t23-9h64q14 0 23 9t9 23zm384-864v512q0 26-19 45t-45 19-45-19l-176-176-652 652q-10 10-23 10t-23-10l-114-114q-10-10-10-23t10-23l652-652-176-176q-19-19-19-45t19-45 45-19h512q26 0 45 19t19 45z"/></svg></a>', array('@docs' => '//docs.adaptivethemes.com/theme-generator/')),
      '#weight' => -1000,
    );


    if ($at_core == FALSE) {
      $form['generate']['#disabled'] = TRUE;
      drupal_set_message(t('<a href="@download_href" target="_blank">Adaptivetheme</a> is a required base theme for all generated themes, <a href="@download_href" target="_blank">download the latest version for Drupal 8</a> and place in the themes directory.', array('@download_href' => 'https://www.drupal.org/project/adaptivetheme')), 'error');
    }

    // Friendly name.
    $form['generate']['generate_friendly_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Theme name'),
      '#maxlength' => 50, // the maximum allowable length of a module or theme name.
      '#size' => 30,
      '#required' => TRUE,
      '#default_value' => '',
      '#description' => t('Enter a unique theme name. Letters, spaces and underscores only.'),
    );

    // Machine name.
    $form['generate']['generate_machine_name'] = array(
      '#type' => 'machine_name',
      '#maxlength' => 50,
      '#size' => 30,
      '#title' => t('Machine name'),
      '#required' => TRUE,
      '#field_prefix' => '',
      '#default_value' => '',
      '#machine_name' => array(
        'exists' => array($this->themeSettingsInfo, 'themeNameExists'), // class method for call_user_func()
        'source' => array('generate', 'generate_friendly_name'),
        'label' => t('Machine name'),
        'replace_pattern' => '[^a-z_]+',
        'replace' => '_',
      ),
    );

    $generate_type_options = array(
      'standard' => t('Standard kit'),
    );

    if (!empty($base_themes)) {
      $generate_type_options = array(
        'standard' => t('Standard kit'),
        'clone' => t('Clone'),
      );
    }

    $form['generate']['generate_type'] = array(
      '#type' => 'select',
      '#title' => t('Type'),
      '#required' => TRUE,
      '#options' => $generate_type_options,
    );

    $form['generate']['generate_type_description_standard_kit'] = array(
      '#type' => 'container',
      '#markup' => t('Standard kit includes an advanced layout and is designed to fully support the UIKit and Color module (both optional).'),
      '#attributes' => array('class' => array('generate-type__description')),
      '#states' => array(
        'visible' => array('select[name="generate[generate_type]"]' => array('value' => 'standard')),
      ),
    );

    $form['generate']['generate_clone_source'] = array(
      '#type' => 'select',
      '#title' => t('Clone source'),
      '#options' => $base_themes,
      '#default_value' => '',
      '#description' => t('Clones are direct copies of existing sub-themes. Use a unique name.'),
      '#states' => array(
        'visible' => array('select[name="generate[generate_type]"]' => array('value' => 'clone')),
      ),
    );

    // Options
    $form['generate']['options'] = array(
      '#type' => 'fieldset',
      '#title' => t('Options'),
      '#states' => array(
        'visible' => array(
          'select[name="generate[generate_type]"]' => array(
            array('value' => 'standard'),
            array('value' => 'clone'),
          ),
        ),
      ),
    );

    // UI Kit
    $form['generate']['options']['generate_uikit'] = array(
      '#type' => 'checkbox',
      '#title' => t('UI Kit'),
      '#default_value' => 0,
      '#description' => t('Include the User Interfact Kit - a SASS/Compass UI Kit for Adativetheme and Drupal.'),
      '#states' => array(
        'visible' => array(
          'select[name="generate[generate_type]"]' => array(
            array('value' => 'standard'),
          ),
        ),
      ),
    );

    // Color module
    $form['generate']['options']['generate_color'] = array(
      '#type' => 'checkbox',
      '#title' => t('Color Module'),
      '#default_value' => 0,
      '#description' => t('Provides Color module support - includes a starter color.inc file. Requires UI Kit.'),
      '#states' => array(
        'disabled' => array(
          'input[name="generate[options][generate_uikit]"]' => array('checked' => FALSE),
        ),
        'visible' => array(
          'select[name="generate[generate_type]"]' => array(
            array('value' => 'standard'),
          ),
        ),
      ),
    );

    // Templates
    $form['generate']['options']['generate_templates'] = array(
      '#type' => 'checkbox',
      '#title' => t('Templates'),
      '#default_value' => 0,
      '#description' => t('Include copies of Drupals front end twig templates (page.html.twig is always included regardless of this setting).'),
      '#states' => array(
        'visible' => array(
          'select[name="generate[generate_type]"]' => array(
            array('value' => 'standard'),
          ),
        ),
      ),
    );

    // theme-settings.php file
    $form['generate']['options']['generate_themesettingsfile'] = array(
      '#type' => 'checkbox',
      '#title' => t('theme-settings.php'),
      '#default_value' => 0,
      '#description' => t('Include a theme-settings.php file. Includes skeleton code for the form alter, custom validation and submit functions.'),
      '#states' => array(
        'visible' => array(
          'select[name="generate[generate_type]"]' => array(
            array('value' => 'standard'),
          ),
        ),
      ),
    );

    // Description.
    $form['generate']['options']['generate_description'] = array(
      '#type' => 'textfield',
      '#title' => t('Description'),
      '#default_value' => '',
      '#description' => t('Descriptions are used on the Appearance list page.'),
    );

    // Version.
    $form['generate']['options']['generate_version'] = array(
      '#type' => 'textfield',
      '#title' => t('Version string'),
      '#default_value' => '',
      '#description' => t('Numbers, hyphens and periods only. E.g. 8.x-1.0'),
    );

    $form['generate']['actions']['#type'] = 'actions';
    $form['generate']['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Validate Theme Generator.
    if (!empty($values['generate']['generate_machine_name'])) {
      $machine_name = $values['generate']['generate_machine_name'];
      $theme_data = $this->themeInfoData;

      if (array_key_exists($machine_name, $theme_data) == FALSE) {
        $target = drupal_get_path('theme', 'at_core') . '/../../' . $machine_name;
        $subtheme_type = $values['generate']['generate_type'];
        $source = '';
        $source_error = '';

        if ($subtheme_type == 'standard') {
          $source = drupal_get_path('module', 'at_theme_generator') . '/starterkits/starterkit';
        }
        else if ($subtheme_type == 'clone') {
          $source = drupal_get_path('theme', $values['generate']['generate_clone_source']);
          $source_error = 'generate][generate_clone_source';
        }

        // Check if directories and files exist and are readable/writable etc.
        if (!file_exists($source) && !is_readable($source)) {
          $form_state->setErrorByName($source_error, t('The source theme (starter kit or clone source) can not be found or is not readable:<br /><code>@source</code>', array('@source' => $source)));
        }
        if (!is_writable(dirname($target))) {
          $form_state->setErrorByName('', t('The target directory is not writable, please check permissions on the <code>@target</code> directory.', array('@target' => $target)));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Generate a new theme.
    if (!empty($values['generate']['generate_machine_name'])) {

      $fileOperations      = new FileOperations();
      $directoryOperations = new DirectoryOperations();

      // Prepare form values and set them into variables.
      $machine_name        = $values['generate']['generate_machine_name'];
      $friendly_name       = Html::escape($values['generate']['generate_friendly_name']);
      $subtheme_type       = $values['generate']['generate_type'];
      $clone_source        = $values['generate']['generate_clone_source'] ?: '';
      $templates           = $values['generate']['options']['generate_templates'];
      $uikit               = $values['generate']['options']['generate_uikit'];
      $color               = $values['generate']['options']['generate_color'];
      $theme_settings_file = $values['generate']['options']['generate_themesettingsfile'];
      $description         = preg_replace('/[^A-Za-z0-9. ]/', '', Html::escape($values['generate']['options']['generate_description']));
      $version             = Html::escape($values['generate']['options']['generate_version']);

      // Initialize variables.
      $source        = '';
      $source_theme  = '';

      // Generated date time for descriptions.
      $datetime = \Drupal::service('date.formatter')->format(REQUEST_TIME, 'custom', 'D jS, M, Y - G:i');

      // Generic description if the desc field is not set.
      $generic_description = 'Sub theme of AT Core';

      // Path to themes
      $path = drupal_get_path('theme', 'at_core');
      $at_generator_path = drupal_get_path('module', 'at_theme_generator');

      // Path to where we will save the cloned theme.
      // This could be configurable?
      $at_core_path_parts = explode("/", $path);
      if (in_array('contrib', $at_core_path_parts)) {
        $target_path = array('themes', 'custom');
      }
      else {
        $target_path = array('themes');
      }

      $target_dir = $directoryOperations->directoryPrepare($target_path);
      $target = "$target_dir/$machine_name";

      if (!is_writable('themes')) {
        drupal_set_message(t('The "themes" directory is not writable. To generate a new theme reset permissions for the "@themespath" directory so it is writable, e.g. chmod themes 0755.', array('@themespath' => base_path() .  'themes')), 'error');
        return;
      }

      // Array of UIKit tools.
      $uikit_tools = array(
        'package.json',
        '.csslintrc',
        'Gruntfile.js',
        'Gemfile',
        'Gemfile.lock',
      );

      // Standard type variables.
      if ($subtheme_type === 'standard') {
        $source_theme = 'THEMENAME';
        $source = $at_generator_path . '/starterkits/starterkit';
      }

      // Clone variables.
      if ($subtheme_type === 'clone') {
        $source_theme = $clone_source;
        $source = drupal_get_path('theme', $source_theme);
      }

      // Recursively scan the config directory for config files.
      $configuration = $directoryOperations->directoryScanRecursive("$source/config");

      // Files to strip replace strings
      $info_file = "$target/$machine_name.info.yml";
      $library_file = "$target/$machine_name.libraries.yml";
      $shortcodes_file = "$target/$machine_name.shortcodes.yml";

      // Begin generation.
      //------------------------------------------------------------------------------------------------

      // Recursively copy the source theme.
      if (is_dir($source)) {
        $directoryOperations->directoryRecursiveCopy($source, $target);
      }

      // Generated CSS files.
      $generated_css = $directoryOperations->directoryScan("$source/styles/css/generated");
      foreach ($generated_css as $old_css_file) {
        $new_css_file = str_replace($source_theme, $machine_name, $old_css_file);
        $fileOperations->fileRename("$target/styles/css/generated/$old_css_file", "$target/styles/css/generated/$new_css_file");
      }

      // UIKit and Color
      if ($subtheme_type === 'standard') {

        // UIKit
        if ($uikit === 0) {
          $directoryOperations->directoryRemove("$target/styles/uikit");

          // remove files like GEM, Gruntfile.js etc
          foreach ($uikit_tools as $tool) {
            unlink("$target/$tool");
          }

          // Delete the maps directory and all map files.
          $directoryOperations->directoryRemove("$target/styles/css/components/maps");
          $component_css_files = $directoryOperations->directoryScan("$target/styles/css/components");

          foreach ($component_css_files as $component_file_key => $component_file) {
            $map_string = '/*# sourceMappingURL=maps/' . str_replace('.css', '.css.map', $component_file) . ' */';

            if (file_exists("$target/styles/css/components/$component_file")) {
              $fileOperations->fileStrReplace("$target/styles/css/components/$component_file", $map_string, '');
            }
          }
        }

        // Color.
        if ($color === 0) {
          $directoryOperations->directoryRemove("$target/color");
        }
      }

      // Templates.
      if ($subtheme_type === 'standard') {
        $fileOperations->fileStrReplace("$target/templates/generated/page.html.twig", 'THEMENAME', $machine_name);
        if ($templates === 1) {
          $directoryOperations->directoryRecursiveCopy("$path/templates", "$target/templates");
        }
      }
      if ($subtheme_type === 'clone') {
        $cloned_templates = $directoryOperations->directoryScan("$target/templates/generated");
        foreach ($cloned_templates as $cloned_template) {
          $fileOperations->fileStrReplace("$target/templates/generated/$cloned_template", $source_theme, $machine_name);
        }
      }

      // .theme
      if ($subtheme_type === 'standard') {
        $fileOperations->fileRename("$target/$source_theme.theme", "$target/$machine_name.theme");
        $fileOperations->fileStrReplace("$target/$machine_name.theme", 'HOOK', $machine_name);
      }
      if ($subtheme_type === 'clone') {
        $fileOperations->fileRename("$target/$source_theme.theme", "$target/$machine_name.theme");
        $fileOperations->fileStrReplace("$target/$machine_name.theme", $source_theme, $machine_name);
      }

      // libraries
      $fileOperations->fileRename("$target/$source_theme.libraries.yml", $library_file);

      // theme-settings.php
      if ($subtheme_type === 'standard') {
        if ($theme_settings_file === 1) {
          $fileOperations->fileStrReplace("$target/theme-settings.php", 'HOOK', $machine_name);
        }
        else {
          $directoryOperations->directoryRemove("$target/theme-settings.php");
        }
      }
      if ($subtheme_type === 'clone') {
        $fileOperations->fileStrReplace("$target/theme-settings.php", $source_theme, $machine_name);
      }

      // Config.
      $new_config_file = '';
      foreach ($configuration as $config_path => $config_files) {
        if (is_dir("$target/config/$config_path")) {
          foreach ($config_files as $config_file) {
            $new_config_file = str_replace($source_theme, $machine_name, $config_file) ?: '';
            $fileOperations->fileRename("$target/config/$config_path/$config_file", "$target/config/$config_path/$new_config_file");
            $fileOperations->fileStrReplace("$target/config/$config_path/$new_config_file", 'TARGET', $target);
            $fileOperations->fileStrReplace("$target/config/$config_path/$new_config_file", $source_theme, $machine_name);
          }
        }
      }
      if ($subtheme_type === 'clone') {
        $source_config = \Drupal::config($source_theme . '.settings')->get();

        // Empty if the source theme has never been installed, in which case it
        // should be safe to assume there is no new configuration worth saving.
        if (!empty($source_config)) {

          // Remove the default config hash.
          if (array_key_exists('_core', $source_config)) {
            unset($source_config['_core']);
          }

          $old_config = "$target/config/install/$machine_name.settings.yml";
          $new_config = Yaml::encode($source_config);

          $find_generated_files = "themes/$source_theme/styles/css/generated";
          $replace_generated_files = "themes/$machine_name/styles/css/generated";
          $new_config = str_replace($find_generated_files, $replace_generated_files, $new_config);

          $fileOperations->fileReplace($new_config, $old_config);
          $fileOperations->fileStrReplace($old_config, $source_theme, $machine_name);
        }
      }

      // Info.
      $fileOperations->fileRename("$target/$source_theme.info.yml", $info_file);

      // Shortcodes.
      $fileOperations->fileRename("$target/$source_theme.shortcodes.yml", $shortcodes_file);

      // Parse, rebuild and save the themes info.yml file.
      $theme_info_data = \Drupal::service('info_parser')->parse($info_file);

      // Name and theme type.
      $theme_info_data['name'] = "$friendly_name";
      $theme_info_data['type'] = "theme";

      $clone_quotes = array(
        'The shroud of the Dark Side has fallen. Begun, this clone war has.',
        'Blind we are, if creation of this clone army we could not see.',
        'The first step to correcting a mistake is patience.',
        'A single chance is a galaxy of hope.',
        'A very wise jedi once said nothing happens by accident.',
        'Smaller in number we are but larger in mind.',
      );
      $cq = array_rand($clone_quotes);
      $clone_quote = $clone_quotes[$cq];

      // Description.
      $base_theme = $theme_info_data['base theme'];
      if ($subtheme_type === 'clone') {
        $description = $description ? $description . '<br />Clone of: ' . $source_theme : '<i>' . $clone_quote . '</i>' . '<br />Clone of: ' . $source_theme;
      }

      $description = $description ?: $generic_description;
      $theme_info_data['description'] = "$description.<br />Base theme: $base_theme <br />Machine-name: $machine_name <br />Generated: $datetime";

      // alt text.
      $theme_info_data['alt text'] = "Screenshot for $friendly_name";

      // Version.
      $theme_info_data['version'] = $version ? str_replace(' ', '-', trim($version)) : '8.x-1.0';

      // Regions.
      foreach($theme_info_data['regions'] as $region_key => $region_name) {
        $theme_info_data['regions'][$region_key] = "$region_name";
      }

      // Unset stuff we don't want or need.
      unset($theme_info_data['hidden']);
      unset($theme_info_data['project']);
      unset($theme_info_data['datestamp']);

      // Libraries.
      if (isset($theme_info_data['libraries-extend']['quickedit/quickedit'])) {
        $theme_info_data['libraries-extend']['quickedit/quickedit'] = array($machine_name . '/quickedit');
      }

      // Save the info file.
      $rebuilt_info = $fileOperations->fileBuildInfoYml($theme_info_data);
      $fileOperations->fileReplace($rebuilt_info, $info_file);

      // Set messages, however we may need more validation?
      //----------------------------------------------------------------------
      // system message for Reports.
      $logger_message = t('A new theme <b>@theme_name</b>, with then machine name: <code><b>@machine_name</b></code>, has been generated.', array(
        '@theme_name'   => $friendly_name,
        '@machine_name' => $machine_name));
      \Drupal::logger('at_generator')->notice($logger_message);

      // Message for the user.
      drupal_set_message(
        t("<p>A new theme <b>@theme_name</b>, with then machine name: <code><b>@machine_name</b></code>, has been generated.</p><p>You can find your theme here: <code><b>@theme_path</b></code> </p><p>Click the List tab to view the themes list and enable your new theme.</p>", array(
          '@theme_name'   => $friendly_name,
          '@machine_name' => $machine_name,
          '@theme_path'   => $target,
          '@performance_settings' => base_path() . 'admin/config/development/performance')),
          'status'
        );

      // Refresh data.
      system_list_reset();
      \Drupal::service('theme_handler')->rebuildThemeData();
    }
    else {
      drupal_set_message(t('Bummer, something went wrong with the machine name, please try again or contact support.'));
    }
  }
}
