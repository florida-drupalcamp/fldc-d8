<?php

namespace Drupal\yamlform\Tests;

/**
 * Tests for YAML form submission list builder.
 *
 * @group YamlForm
 */
class YamlFormSubmissionListBuilderTest extends YamlFormTestBase {

  /**
   * Tests results.
   */
  public function testResults() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface[] $submissions */
    list($yamlform, $submissions) = $this->createYamlFormWithSubmissions();

    // Make the second submission to be starred (aka sticky).
    $submissions[1]->setSticky(TRUE)->save();

    $this->drupalLogin($this->adminSubmissionUser);

    /* Filter */

    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table');

    // Check state options with totals.
    $this->assertRaw('<select data-drupal-selector="edit-state" id="edit-state" name="state" class="form-select"><option value="" selected="selected">All [3]</option><option value="starred">Starred [1]</option><option value="unstarred">Unstarred [2]</option></select>');

    // Check results with no filtering.
    $this->assertLinkByHref($submissions[0]->toUrl()->toString());
    $this->assertLinkByHref($submissions[1]->toUrl()->toString());
    $this->assertLinkByHref($submissions[2]->toUrl()->toString());
    $this->assertRaw($submissions[0]->getData('first_name'));
    $this->assertRaw($submissions[1]->getData('first_name'));
    $this->assertRaw($submissions[2]->getData('first_name'));
    $this->assertNoFieldById('edit-reset', 'reset');

    // Check results filtered by key(word).
    $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table', ['search' => $submissions[0]->getData('first_name')], t('Filter'));
    $this->assertUrl('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table?search=' . $submissions[0]->getData('first_name') . '&state=');
    $this->assertRaw($submissions[0]->getData('first_name'));
    $this->assertNoRaw($submissions[1]->getData('first_name'));
    $this->assertNoRaw($submissions[2]->getData('first_name'));
    $this->assertFieldById('edit-reset', 'Reset');

    // Check results filtered by state.
    $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table', ['state' => 'starred'], t('Filter'));
    $this->assertUrl('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table?search=&state=starred');
    $this->assertRaw('<option value="starred" selected="selected">Starred [1]</option>');
    $this->assertNoRaw($submissions[0]->getData('first_name'));
    $this->assertRaw($submissions[1]->getData('first_name'));
    $this->assertNoRaw($submissions[2]->getData('first_name'));
    $this->assertFieldById('edit-reset', 'Reset');

    /* Customize */

    // Check that sid is visible and changed is hidden.
    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table');
    $this->assertRaw('sort by Created');
    $this->assertNoRaw('sort by Changed');

    // Check that first name is before last name.
    $this->assertPattern('#First name.+Last name#s');

    // Check that no pager is being displayed.
    $this->assertNoRaw('<nav class="pager" role="navigation" aria-labelledby="pagination-heading">');

    // Check that table is sorted by sid.
    $this->assertRaw('<th specifier="sid" aria-sort="descending" class="is-active">');

    // Check the table results order by sid.
    $this->assertPattern('#Hillary.+Abraham.+George#ms');

    // Customize to results table.
    $edit = [
      'columns[created][name]' => FALSE,
      'columns[changed][name]' => TRUE,
      'columns[element__first_name][weight]' => '8',
      'columns[element__last_name][weight]' => '7',
      'sort' => 'element__first_name',
      'direction' => 'desc',
      'limit' => 20,
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table/custom', $edit, t('Save'));
    $this->assertRaw('The customized columns and results per page limit have been saved.');

    // Check that sid is hidden and changed is visible.
    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table');
    $this->assertNoRaw('sort by Created');
    $this->assertRaw('sort by Changed');

    // Check that first name is now after last name.
    $this->assertPattern('#Last name.+First name#ms');

    // Check the table results order by first name.
    $this->assertPattern('#Hillary.+George.+Abraham#ms');

    // Manually set the limit to 1.
    $yamlform->setState('results.custom.limit', 1);

    // Check that only one result (Hillary #2) is displayed with pager.
    $this->drupalGet('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table');
    $this->assertNoRaw($submissions[0]->getData('first_name'));
    $this->assertNoRaw($submissions[1]->getData('first_name'));
    $this->assertRaw($submissions[2]->getData('first_name'));
    $this->assertRaw('<nav class="pager" role="navigation" aria-labelledby="pagination-heading">');
  }

}
