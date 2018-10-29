<?php
/*-------------------------------------------------------+
| SYSTOPIA Membership Stats Extension                    |
| Copyright (C) 2018 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| http://www.systopia.de                                 |
+--------------------------------------------------------+
| License: AGPLv3, see LICENSE file                      |
+--------------------------------------------------------*/

use CRM_Membershipstats_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Membershipstats_Form_Settings extends CRM_Core_Form {
  public function buildQuickForm() {

    // add form elements
    $this->add(
      'select',
      'membership_type_ids',
      E::ts("Membership Types"),
      $this->getAllMembershipTypes(),
      FALSE,
      ['class' => 'crm-select2', 'multiple' => 'multiple']
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    parent::buildQuickForm();
  }

  public function setDefaultValues() {
    $default_values = parent::setDefaultValues();

    $currently_selected = self::getSelectedMembershipTypeIDs();
    if ($currently_selected) {
      $default_values['membership_type_ids'] = $currently_selected;
    }

    return $default_values;
  }

  /**
   * Store provided value
   */
  public function postProcess() {
    $values = $this->exportValues();
    self::setSelectedMembershipTypeIDs($values['membership_type_ids']);
    parent::postProcess();
  }

  /**
   * Get all membership types
   */
  protected function getAllMembershipTypes() {
    $types_list = [];
    $types = civicrm_api3('MembershipType', 'get', [
        'option.limit' => 0,
        'return'       => 'id,name']);
    foreach ($types['values'] as $type) {
      $types_list[$type['id']] = $type['name'];
    }
    return $types_list;
  }

  /**
   * Get the list of selected membership types
   */
  public static function getSelectedMembershipTypeIDs() {
    $current_value = CRM_Core_BAO_Setting::getItem('de.systopia.membershipstats', 'selected_membership_type_ids');
    if (!is_array($current_value)) {
      return NULL;
    } else {
      return $current_value;
    }
  }

  /**
   * Get the list of selected membership types
   */
  protected static function setSelectedMembershipTypeIDs($type_ids) {
    if (is_array($type_ids)) {
      CRM_Core_BAO_Setting::setItem($type_ids, 'de.systopia.membershipstats', 'selected_membership_type_ids');
    }
  }
}
