<?php

/**
 * @file
 * Preset lists of font options.
 */

/**
 * Websafe fonts list.
 */
function websafe_fonts() {
  $websafe_fonts = array(
    'Verdana, Geneva, sans-serif',
    '"Segoe UI", Segoe, Tahoma, Geneva, sans-serif',
    '"Helvetica Neue", Helvetica, Arial, sans-serif',
    'Georgia, Utopia, Palatino, "Palatino Linotype", serif',
    '"Times New Roman", Times, serif',
    'Impact, "Helvetica Inserat", "Arial Black", sans-serif',
    '"Courier New", Courier, monospace, sans-serif',
    'sans-serif',
    'serif',
  );

  return $websafe_fonts;
}

/**
 * @file
 * Font Elements Config Array.
 *
 * The fonts array is used by the fonts sub-system to generate forms,
 * build stylesheets and load font styles.
 */
function font_elements() {
  $elements_array = &drupal_static(__FUNCTION__, array());
  if (empty($elements_array)) {
    $elements_array = array(
      'base' => array(
        'label' => 'Base',
        'selector' => 'body',
      ),
      'sitename' => array(
        'label' => 'Site name',
        'selector' => '.site-branding__name-link',
      ),
      'siteslogan' => array(
        'label' => 'Site slogan',
        'selector' => '.site-branding__slogan',
      ),
      'pagetitle' => array(
        'label' => 'Page title',
        'selector' => '.node__title, .page__title, .user__title',
      ),
      'nodetitle' => array(
        'label' => 'Node title (full)',
        'selector' => '.node--view-mode-full .node__title, .node--view-mode-full .node__title a',
      ),
      'nodetitle_teaser' => array(
        'label' => 'Node title (teaser)',
        'selector' => '.node--view-mode-teaser .node__title, .node--view-mode-teaser .node__title-link',
      ),
      'commenttitle' => array(
        'label' => 'Comment title',
        'selector' => '.comment__title, .comment__title a',
      ),
      'blocktitle' => array(
        'label' => 'Block title',
        'selector' => '.block__title, .block-menu__title, .pane__title',
      ),
      'links' => array(
        'label' => 'Links',
        'selector' => 'a, .un-button',
      ),
      'menus' => array(
        'label' => 'Menus',
        'selector' => '.menu a',
      ),
      'responsivemenu' => array(
        'label' => 'Responsive menu',
        'selector' => '.rm-block, .rm-block a',
      ),
      'responsivemenu_toggle' => array(
        'label' => 'Responsive menu toggle',
        'selector' => '.rm-toggle, .rm-toggle__link',
      ),
      'image_captions' => array(
        'label' => 'Image captions',
        'selector' => 'figcaption, .field-type-image__figcaption',
      ),
      'slideshow_captions' => array(
        'label' => 'Slideshow captions',
        'selector' => 'p.flex-caption',
      ),
      'tabs' => array(
        'label' => 'Tabs',
        'selector' => '.tabs a',
      ),
      'breadcrumbs' => array(
        'label' => 'Breadcrumbs',
        'selector' => '.breadcrumb__list-item, .breadcrumb__link, .breadcrumb__title',
      ),
      'h1h4' => array(
        'label' => 'h1 to h4 headings',
        'selector' => 'h1, h2, h3, h4',
      ),
      'h5h6' => array(
        'label' => 'h5, h6 headings',
        'selector' => 'h5, h6',
      ),
      'h1' => array(
        'label' => 'h1',
        'selector' => 'h1',
      ),
      'h2' => array(
        'label' => 'h2',
        'selector' => 'h2',
      ),
      'h3' => array(
        'label' => 'h3',
        'selector' => 'h3',
      ),
      'h4' => array(
        'label' => 'h4',
        'selector' => 'h4',
      ),
      'h5' => array(
        'label' => 'h5',
        'selector' => 'h5',
      ),
      'h6' => array(
        'label' => 'h6',
        'selector' => 'h6',
      ),
      'formitems' => array(
        'label' => 'Form elements',
        'selector' => 'form, input, select, textarea',
      ),
      'buttons' => array(
        'label' => 'Buttons',
        'selector' => 'button, .button',
      ),
      'tables' => array(
        'label' => 'Tables',
        'selector' => 'table',
      ),
      'blockquotes' => array(
        'label' => 'Blockquotes & citations',
        'selector' => 'blockquote, cite, q',
      ),
      'code' => array(
        'label' => 'Code',
        'selector' => 'code, pre, var, kbd, samp',
      ),
      'custom_selectors' => array(
        'label' => 'Custom selectors',
        'selector' => 'custom_selectors',
      ),
    );
  }

  return $elements_array;
}

/**
 * Reusable style options for titles.
 * @param $style
 * @return array
 */
function title_style_options($style) {
  $options = &drupal_static(__FUNCTION__, array());
  switch ($style) {
    case ('case'):
      $options = array(
        'none'       => t('Normal'),
        'uppercase'  => t('Upper case'),
        'lowercase'  => t('Lower case'),
        'capitalize' => t('Capitalize'),
        'small-caps' => t('Small caps'),
        'inherit'    => t('Inherit'),
      );
    break;
    case ('weight'):
      $options = array(
        '400' => t('Normal'),
        '700' => t('Bold'),
        '600' => t('Semi-bold'),
        '300' => t('Light'),
      );
    break;
    case ('alignment'):
      $options = array(
        'left'   => t('Left'),
        'right'  => t('Right'),
        'center' => t('Centered'),
      );
    break;
  }

  return $options;
}

/**
 * Reusable valid type options for titles.
 */
function title_valid_type_options() {
  $options = array(
    'sitename',
    'siteslogan',
    'pagetitle',
    'nodetitle',
    'nodetitle_teaser',
    'blocktitle',
    'commenttitle',
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
  );

  return $options;
}
