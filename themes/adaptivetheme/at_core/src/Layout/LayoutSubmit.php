<?php

/**
 * @file
 * Contains \Drupal\at_core\Layout\LayoutSubmit
 */

namespace Drupal\at_core\Layout;

use Drupal\at_core\File\FileOperations;
use Drupal\at_core\File\DirectoryOperations;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\Yaml\Parser;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache;

class LayoutSubmit {

  // The active theme name.
  protected $theme_name;

  // Form state values.
  protected $values;

  // Constructor
  public function __construct($theme_name, $values) {
    $this->theme_name = $theme_name;

    $layout_data = new LayoutCompatible($this->theme_name);
    $layout_compatible_data = $layout_data->getCompatibleLayout();

    $this->layout_config = $layout_compatible_data['layout_config'];
    $this->css_config = $layout_compatible_data['css_config'];

    $this->layout_name = $layout_compatible_data['layout_name'];
    $this->layout_path = drupal_get_path('theme', $this->layout_config['layout_provider']) . '/layout/' . $this->layout_name;

    $this->form_values = $values;
  }

  /**
   * Build and save the suggestions layout css files.
   */
  public function saveLayoutSuggestionsCSS() {
    $breakpoints_group = \Drupal::service('breakpoint.manager')->getBreakpointsByGroup($this->form_values['settings_breakpoint_group_layout']);
    $generated_files_path = $this->form_values['settings_generated_files_path'];
    $css_data = array();

    foreach ($this->form_values['settings_suggestions'] as $suggestion_key => $suggestions_name) {
      foreach ($breakpoints_group as $breakpoint_id => $breakpoint_value) {
        foreach ($this->layout_config['rows'] as $row_key => $row_values) {
          // match the key set in the form, hacking on get label
          $breakpoint_layout_key = strtolower(preg_replace("/\W|_/", "", $breakpoint_value->getLabel()));
          $css_data[$suggestion_key][$breakpoint_layout_key]['query'] = $breakpoint_value->getMediaQuery();
          if (!empty($this->form_values['settings_'. $suggestion_key .'_'. $breakpoint_layout_key .'_'. $row_key])) {
            $css_data[$suggestion_key][$breakpoint_layout_key]['rows'][$row_key] = $this->form_values['settings_'. $suggestion_key .'_'. $breakpoint_layout_key .'_'. $row_key];
          }
          else {
            $css_data[$suggestion_key][$breakpoint_layout_key]['rows'][$row_key] = 'not_set';
          }
        }
      }
    }

    // Initialize or set vars.
    $output = array();
    $css_rows = array();
    $css_file = array();
    $path_to_css_files = $this->layout_path . '/' . $this->css_config['css_files_path'];

    foreach ($css_data as $suggestion => $breakpoints) {
      foreach ($breakpoints as $breakpoint_keys => $breakpoint_values) {
        foreach ($breakpoint_values['rows'] as $row_keys => $row_values) {
          if ($row_values == 'not_set') {
            continue;
          }
          foreach ($this->css_config['css'] as $css_key => $css_values) {
            if (file_exists($path_to_css_files . '/' . $css_key . '/' . $row_values . '.css')) {
              $css_file[$suggestion][$breakpoint_keys][$row_keys] = file_get_contents($path_to_css_files . '/' . $css_key . '/' . $row_values . '.css');
              $replace_class = 'pr-' . $row_keys;
              if (!empty($css_file[$suggestion][$breakpoint_keys][$row_keys])) {
                $file = str_replace($row_values, $replace_class, $css_file[$suggestion][$breakpoint_keys][$row_keys]);
                $css_rows[$suggestion][$breakpoint_keys][$breakpoint_keys . '_' . $row_keys] = $file;
              }
            }
          }
        }

        if (!empty($css_rows[$suggestion][$breakpoint_keys])) {
          $output[$suggestion][] = '@media ' . $breakpoint_values['query'] . " {\n";
          $output[$suggestion][] =  implode($css_rows[$suggestion][$breakpoint_keys]);
          $output[$suggestion][] = "}\n";
        }
      }
    }

    // Get the layouts global CSS if any.
    $global_css = '';
    if ($this->css_config['css_global_layout']) {
      $global_css = file_get_contents($path_to_css_files . '/' . $this->css_config['css_global_layout']);
    }

    // Max widths.
    $max_width = array();
    if (isset($this->form_values['settings_max_width_enable']) && $this->form_values['settings_max_width_enable'] === 1) {
      $max_width_value = Html::escape($this->form_values['settings_max_width_value']);
      $max_width['global'] = '.l-rw { max-width: ' . trim($max_width_value) . $this->form_values['settings_max_width_unit'] . '; }';

      // Per row.
      if (isset($this->form_values['settings_max_width_enable_rows']) && $this->form_values['settings_max_width_enable_rows'] === 1) {
        foreach ($this->layout_config['rows'] as $row_key => $row_values) {
          if (isset($this->form_values['settings_max_width_value_' . $row_key]) && !empty($this->form_values['settings_max_width_value_' . $row_key])) {
            $max_width_rows[$row_key]['value'] = trim($this->form_values['settings_max_width_value_' . $row_key]);
            $max_width_rows[$row_key]['unit'] = trim($this->form_values['settings_max_width_unit_' . $row_key]);
            $max_width[$row_key] = '.pr-' . str_replace('_', '-', $row_key) . '__rw { max-width: ' .  $max_width_rows[$row_key]['value'] .  $max_width_rows[$row_key]['unit'] . '; }';
          }
        }
      }
    }

    // Don't regenerate CSS files to be removed.
    foreach ($this->form_values as $values_key => $values_value) {
      if (substr($values_key, 0, 18) === 'delete_suggestion_') {
        if ($values_value === 1) {
          $delete_suggestion_keys[] = Unicode::substr($values_key, 18);
        }
      }
    }
    if (!empty($delete_suggestion_keys)) {
      foreach ($delete_suggestion_keys as $template_to_remove) {
        unset($output[$template_to_remove]);
      }
    }

    $saved_css = array();
    foreach ($output as $suggestion => $css) {
      if (!empty($css)) {
        $message = '/* Layout CSS for: ' . str_replace('_', '-', $suggestion) . '.html.twig, generated: ' . date(DATE_RFC822) . ' */';
        $file_content = $message ."\n". $global_css . implode('', $css) . implode("\n", $max_width);
        $file_name = $this->theme_name . '.layout.' . str_replace('_', '-', $suggestion) . '.css';
        $filepath = "$generated_files_path/$file_name";
        file_unmanaged_save_data($file_content, $filepath, FILE_EXISTS_REPLACE);
        if (file_exists($filepath)) {
          $saved_css[] = $file_name;
        }
      }
    }

    if (!empty($saved_css)) {
      $saved_css_message_list = array(
        '#theme' => 'item_list',
        '#items' => $saved_css,
      );
      drupal_set_message(t('The following <b>CSS</b> files were generated in: <code>@generated_files_path</code> @saved_css', array(
          '@saved_css' => \Drupal::service('renderer')->renderPlain($saved_css_message_list),
          '@generated_files_path' => $generated_files_path . '/')
      ), 'status');
    }
  }

  /**
   * Update the themes info file with new regions.
   */
  public function saveLayoutRegions() {
    $regions = array();

    foreach ($this->layout_config['rows'] as $row => $row_values) {
      foreach ($row_values['regions'] as $region_key => $region_values) {
        if (isset($region_values['label'])) {
          $regions[$region_key] = $region_values['label'];
        }
        // BC
        else {
          $regions[$region_key] = $region_values;
        }
      }
    }

    $regions['page_top'] = 'Page top';
    $regions['page_bottom'] = 'Page bottom';

    $path = drupal_get_path('theme', $this->theme_name);
    $info_file = $this->theme_name . '.info.yml';
    $file_path = $path . '/' . $info_file;

    // Create a backup.
    if ($this->form_values['settings_enable_backups'] == 1) {

      $fileOperations = new FileOperations();
      $directoryOperations = new DirectoryOperations();

      $backup_path = $directoryOperations->directoryPrepare($backup_file_path = array($path, 'backup', 'info'));

      // Add a date time string to make unique and for easy identification,
      // save as .txt to avoid conflicts.
      $backup_file =  $info_file . '.'. date(DATE_ISO8601) . '.txt';

      $file_paths = array(
       'copy_source' => $file_path,
       'copy_dest' => $backup_path . '/' . $info_file,
       'rename_oldname' => $backup_path . '/' . $info_file,
       'rename_newname' => $backup_path . '/' . $backup_file,
      );
      $fileOperations->fileCopyRename($file_paths);
    }

    // Parse the current info file.
    $parser = new Parser();
    $theme_info_data = $parser->parse(file_get_contents($file_path));

    $theme_info_data['regions'] = $regions;

    // Prepare the array for printing in yml format.
    $buildInfo = new FileOperations();
    $rebuilt_info = $buildInfo->fileBuildInfoYml($theme_info_data);

    // Replace the existing info.yml file.
    file_unmanaged_save_data($rebuilt_info, $file_path, FILE_EXISTS_REPLACE);
  }

  /**
   * Build and save twig templates.
   * Save each suggestion template, these are saved every time the layout
   * settings are saved because the rows and regions might change, so we re-save
   * every template, every time the form is submitted.
   */
  public function saveLayoutSuggestionsMarkup() {
    $template_suggestions = array();
    $fileOperations = new FileOperations();
    $directoryOperations = new DirectoryOperations();

    if (!empty($this->form_values['settings_suggestions'])) {
      $template_suggestions = $this->form_values['settings_suggestions'];
    }

    if (!empty($this->form_values['ts_name'])) {
      $template_suggestions['page__' . $this->form_values['ts_name']] = 'page__' . $this->form_values['ts_name'];
    }

    // Don't regenerate templates to be deleted.
    foreach ($this->form_values as $values_key => $values_value) {
      if (substr($values_key, 0, 18) === 'delete_suggestion_') {
        if ($values_value === 1) {
          $delete_suggestion_keys[] = Unicode::substr($values_key, 18);
        }
      }
    }
    if (!empty($delete_suggestion_keys)) {
      foreach ($delete_suggestion_keys as $template_to_remove) {
        unset($template_suggestions[$template_to_remove]);
      }
    }

    // Template path.
    $template_file = $this->layout_path . '/' . $this->layout_name . '.html.twig';

    // Path to target theme where the template will be saved.
    $path = drupal_get_path('theme', $this->theme_name);

    // Remove if this exists, its now deprecated, this is a BC layer so to speak.
    $directoryOperations->directoryRemove($path . '/templates/page');
    $template_directory = $path . '/templates/generated';

    // Check and create the templates directory if does not exist.
    if (!file_exists($path . '/templates')) {
      \Drupal::service('file_system')->mkdir($path . '/templates');
    }
    if (!file_exists($template_directory)) {
      \Drupal::service('file_system')->mkdir($template_directory);
    }

    // Initialize vars.
    $row_regions = array();
    $templates = array();
    $saved_templates = array();

    // We have to save every template every time, in case a row has been added to the layout, all template MUST update.
    // This could be changed later to only do this IF a row has been added, we're not that flash right now :)
    foreach ($template_suggestions as $suggestion_key => $suggestions_name) {

      $output = array();
      $suggestion_key = Html::escape($suggestion_key);

      // Doc block.
      $doc = array();
      $doc[$suggestion_key][] = '{#';
      $doc[$suggestion_key][] = '/**';
      $doc[$suggestion_key][] = ' * ' . $this->layout_name . ' ' . $suggestion_key . ' template.';
      $doc[$suggestion_key][] = ' * Generated on: ' . date(DATE_RFC822);
      $doc[$suggestion_key][] = ' */';
      $doc[$suggestion_key][] = '#}' . "\n";
      $docblock[$suggestion_key] = implode("\n", $doc[$suggestion_key]);

      // Attach layout.
      $generated_files_path = $this->form_values['settings_generated_files_path'];
      $layout_file = $this->theme_name . '.layout.' . str_replace('_', '-', $suggestion_key) . '.css';
      if (file_exists($generated_files_path .'/'. $layout_file)) {
        $library = $this->theme_name .'/'. $this->theme_name . '.layout.' . $suggestion_key;
      }
      else {
        $library = $this->theme_name .'/'. $this->theme_name . '.layout.page';
      }
      $attach_layout = "{{ attach_library('$library') }}";

      // Get the template file, if not found attempt to generate template
      // code programmatically.
      if (file_exists($template_file)) {
        $template = file_get_contents($template_file);
      }
      else {
        foreach ($this->layout_config['rows'] as $row => $row_values) {

          // Row attributes.
          $attributes[$row]['class'][] = 'l-pr page__row';
          $attributes[$row]['class'][] = 'pr-' . $row;

          foreach ($row_values['attributes'] as $attribute_type => $attribute_values) {
            if (is_array($attribute_values)) {
              $attributes[$row][$attribute_type][] = implode(' ', $attribute_values);
            }
            else {
              $attributes[$row][$attribute_type][] = $attribute_values;
            }
          }

          ksort($attributes[$row], SORT_STRING);

          foreach ($attributes[$row] as $attr_type => $attr_array_vales) {
            $attr_array_vales = array_unique($attr_array_vales);
            $this_row_attr[$row][$attr_type] = $attr_type . '="' . implode(' ', $attr_array_vales) . '"';
          }

          $wrapper_element[$suggestion_key] = 'div';
          if ($row == 'header' || $row == 'footer') {
            $wrapper_element[$suggestion_key] = $row;
          }

          if ($this->form_values['settings_layout_method'] === 0) {
            $output[$suggestion_key][$row]['prefix'] = '  {% if '. $row . '.has_regions == true %}';
          }

          $output[$suggestion_key][$row]['wrapper_open'] =  '  <'. $wrapper_element[$suggestion_key] . '{{ ' .  $row . '.wrapper_attributes }}>';
          $output[$suggestion_key][$row]['container_open'] = '    <div{{ ' .  $row . '.container_attributes }}>';

          foreach ($row_values['regions'] as $region_key => $region_values) {
            $row_regions[$suggestion_key][$row][] = '      {{ page.' . $region_key . ' }}';
          }
          $output[$suggestion_key][$row]['regions'] = implode("\n", $row_regions[$suggestion_key][$row]);

          $output[$suggestion_key][$row]['container_close'] = '    </div>';
          $output[$suggestion_key][$row]['wrapper_close'] = '  </' . $wrapper_element[$suggestion_key] . '>';

          $output[$suggestion_key][$row]['suffix'] = "\n";

          if ($this->form_values['settings_layout_method'] === 0) {
            $output[$suggestion_key][$row]['suffix'] = '  {% endif %}' . "\n";
          }
        }

        $generated[$suggestion_key][] = '<div{{ attributes }}>'. "\n";

        foreach ($output[$suggestion_key] as $row_output) {
          $generated[$suggestion_key][] = implode("\n", $row_output);
        }

        $generated[$suggestion_key][] = "  {{ attribution }}" . "\n";
        $generated[$suggestion_key][] = '</div>' . "\n";
        $template[$suggestion_key] = implode($generated[$suggestion_key]);
      }

      // Prepend the docblock to the template markup.
      $template_markup[$suggestion_key] = $docblock[$suggestion_key] . $attach_layout . "\n" . $template[$suggestion_key];

      // Set the template file, either it's page or a page suggestion.
      if ($suggestion_key !== 'page') {
        $template_file = str_replace('_', '-', $suggestion_key) . '.html.twig';
      }
      else {
        $template_file = 'page.html.twig';
      }

      // Set the template path.
      $template_path = $template_directory . '/' . $template_file;

      // Build array of files to be saved.
      $templates[$suggestion_key]['markup'] = $template_markup[$suggestion_key];
      $templates[$suggestion_key]['template_path'] = $template_path;
      $templates[$suggestion_key]['template_name'] = $template_file;

      // Create a backup.
      if ($this->form_values['settings_enable_backups'] == 1) {

        $backup_path = $directoryOperations->directoryPrepare($backup_file_path = array($path, 'backup', 'templates'));

        //Add a date time string to make unique and for easy identification,
        // save as .txt to avoid conflicts.
        $backup_file =  $template_file . '.' . date(DATE_ISO8601) . '.txt';

        $file_paths = array(
         'copy_source' => $template_path,
         'copy_dest' => $backup_path . '/' . $template_file,
         'rename_oldname' => $backup_path . '/' . $template_file,
         'rename_newname' => $backup_path . '/' . $backup_file,
        );

        $fileOperations->fileCopyRename($file_paths);
      }
    }

    foreach ($templates as $suggestion => $template_values) {
       if (!file_exists($templates[$suggestion]['template_path'])) {
         $new_template = $templates[$suggestion]['template_name'];
         $new_template_message = t('It looks like you generated a new template: <b>@new_template</b>. Save the layout settings again so they will take effect.', array('@new_template' => $new_template));
       }
      file_unmanaged_save_data($templates[$suggestion]['markup'], $templates[$suggestion]['template_path'], FILE_EXISTS_REPLACE);
      if (file_exists($templates[$suggestion]['template_path'])) {
        $saved_templates[] = $templates[$suggestion]['template_name'];
      }
    }

    if (!empty($saved_templates)) {
      $saved_templates_message_list = array(
        '#theme' => 'item_list',
        '#items' => $saved_templates,
      );
      drupal_set_message(t('The following <b>templates</b> were generated in: <code>@template_directory</code> @saved_templates', array(
          '@saved_templates' => \Drupal::service('renderer')->renderPlain($saved_templates_message_list),
          '@template_directory' => $template_directory . '/')
      ), 'status');
    }
    if (isset($new_template_message)) {
      drupal_set_message($new_template_message, 'status');
    }
  }
}
