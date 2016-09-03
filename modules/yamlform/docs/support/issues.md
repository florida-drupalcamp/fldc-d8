Known Issues
------------

### Configuration Management

**[Issue #1920902: Unable to tidy the bulk export of YamlForm and YamlFormOptions config files 
because Drupal's YAML utility is not a service.](https://www.drupal.org/node/1920902)**

> The YAML Form module provides drush command to 'tidy' exported YAML  
> configuration files, so that they are easier to read and edit.

### Form Elements
    
**[Drupal core forms system issues](https://www.drupal.org/project/issues/drupal?status=Open&version=8.x&component=forms+system)**
  
> Any changes, improvements, and bug fixes for Drupal's Form API may directly
> impact the YAML Form module.
  
- [Issue #1593964: Allow FAPI usage of the datalist element](https://www.drupal.org/node/1593964)

**[Issue #2502195: Regression: Form throws LogicException when trying to render a form with object as an element's default value.](https://www.drupal.org/node/2502195)**  

> Impacts previewing entity autocomplete elements.

**[Issue #2207383: Create a tooltip component](https://www.drupal.org/node/2207383)

> Impacts displaying element description in a tooltip. jQUery UI's tooltip's UX
> is not great.

**Drupal's CKEditor link dialog replaces open dialog.

> Makes it impossible to display the CKEditor's in a dialog.
> Workaround: Use CKEditor's link dialog.

_Not sure this issue should be addressed by core._

### Submission Display

**[Issue #2484693: Telephone Link field formatter breaks Drupal with 5 digits or less in the number](https://www.drupal.org/node/2720923)**

> Workaround is to manually build a static HTML link.
> See: \Drupal\yamlform\Plugin\YamlFormElement\Telephone::formatHtml

### Access Control

**[Issue #2636066: Access control is not applied to config entity queries](https://www.drupal.org/node/2636066)**

> Workaround: Manually check YAML form access.
> See: Drupal\yamlform\YamlFormEntityListBuilder

### User Interface

**[Issue #2235581: Make Token Dialog support inserting in WYSIWYGs (TinyMCE, CKEditor, etc.)](https://www.drupal.org/node/2235581)**

> This blocks tokens from being inserted easily into the CodeMirror widget.
> Workaround: Disable '\#click_insert' functionality from the token dialog.
   
**Config entity does NOT support [Entity Validation API](https://www.drupal.org/node/2015613)**

> Validation constraints are only applicable to content entities and fields.
>
> In D8 all config entity validation is handled via 
  \Drupal\Core\Form\FormInterface::validateForm
>
> Workaround: Created the YamlFormEntityElementsValidator service.      
  
**[Issue #2585169: Unable to alter local actions prior to rendering](https://www.drupal.org/node/2585169)**

> Makes it impossible to open an action in a dialog.  
> Workaround: Add local action to a controller's response.
