Below is the current roadmap for the YAML Form module.

★ Indicates areas that I need help with. 

Phase I (before Release Candidate)
----------------------------------

### Forms & Elements 

**Finalize elements**

- [#2765797](https://www.drupal.org/node/2765797) 
  \#pattern support with regex validation **DONE** 
- Autocompletion element & property support **DONE**
- [#2758671](https://www.drupal.org/node/2758671) 
  Select2 support.  **DONE**
- [#2346973](https://www.drupal.org/node/2346973) 
  Entity query / Views entity reference support for #options 

**User Interface**

- [#2759527](https://www.drupal.org/node/2759527) 
  Build #options UI. **DONE** 
- [#2764503](https://www.drupal.org/node/2764503) 
  Build #states (conditional logic) UI. 
- Customizable reports **DONE**
- [#2722601](https://www.drupal.org/node/2722601)
  Sortable reports **DONE**
- [#2778907](https://www.drupal.org/node/2778907)
  Improve WYSIWYG support

### Design & UX 

**Templating ★**

- [#2757991](https://www.drupal.org/node/2757991) **DONE**
  Review and finalize templates 
- Support for multi column responsive layouts. (ie 2 column form) **POSTPONED**

**Examples & Templates**

- Add better documentation to yamlform_examples.module **DONE**
- Review out-of-the-box templates provide by the yamlform_templates.module. ★ 

### Code & APIs 

**Code Review**

- Testability
- Refactorability
- Plugin definitions ★
- Entity API implementation ★
- Form API implementation ★

**Security Review ★**

- Callback injection
- JS/CSS injection
- Access controls

**API Review**

- Review doc blocks

**Libraries**

- [#2745325](https://www.drupal.org/node/2745325)
  Finalize external library support 
- Add external libraries to composer.json ★

**Testing**

- Refactor PHPUnit tests
- Improve SimpleTest setUp performance.
- Configuration Management
- Default configuration
- Finalize default admin settings

###  Multilingual 

- Finalize how YAML form's elements are translated. ★
- Make sure the YAML Form module is completely translatable. ★

### Documentation & Help 

**General**

- Decide if any documentation should live on Drupal.org
- [#2759591](https://www.drupal.org/node/2759591)
  What is YAML and why we are using it? **POSTPONED**
- Move features into a dedicated file. **DONE**
- Create a related project page **DONE**
- Simplify the project page. **DONE**
- Update screenshots **DONE**

**Module**

- Review hook_help() **DONE**
- Review hardcoded messages.

**Editorial ★**

- Unified tone
- General typos, grammar, and wording. ★

### Other 

**Screencasts**

- Form Builder - How to build forms? **DONE**
- Form Developer - How to customize forms? **DONE**
- Contributor - How to extend the YAML form API and plugins?
- More to come...

**Maintainers/Developer**

- Document issue queue policies. **DONE**
- Build full documented plugin examples.


Phase II (after Stable Release)
-------------------------------

**Forms**

- [#2781481](https://www.drupal.org/node/2781481)
  Custom validation error messages
- [#2757491](https://www.drupal.org/node/2757491) 
  AJAX support for forms ★ 

**Rules/Actions**

- [#2779461](https://www.drupal.org/node/2779461) 
  Rules/Action integration ★

**Results**

- Bulk export of attached files.
- Create trash bin for deleted results.   
  _Copy D8 core's solutions_ 

**Views**

- [#2769977](https://www.drupal.org/node/2769977) 
  Views integration ★

**APIs** 

- REST API endpoint for CRUD operations.
- Headless Drupal Forms

**Other** 

- Code snippets repository
- Template repository
