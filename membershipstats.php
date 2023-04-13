<?php
/*-------------------------------------------------------+
| SYSTOPIA Membership Stats Extension                    |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| http://www.systopia.de                                 |
+--------------------------------------------------------+
| License: AGPLv3, see LICENSE file                      |
+--------------------------------------------------------*/

require_once 'membershipstats.civix.php';

// some constants
define('CIVICRM_MEMBERSHIPSTATS_TABLE',        'civicrm_value_membershipstats');
define('CIVICRM_MEMBERSHIPSTATS_CURRENT',      'membership_current');
define('CIVICRM_MEMBERSHIPSTATS_CURRENT_TYPE', 'membership_current_type');
define('CIVICRM_MEMBERSHIPSTATS_MEMBER_SINCE', 'membership_member_since');
define('CIVICRM_MEMBERSHIPSTATS_END_DATE',     'membership_end_date');

/**
 * Implements the POST hook:
 *  Trigger status update on changes to membership entities
 */
function membershipstats_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName == 'Membership') {
    if ($op == 'edit' || $op == 'create' || $op == 'delete') {
      $calculator = new CRM_Membershipstats_Calculator();
      if (empty($objectRef->contact_id)) {
        // contact_id NOT known -> try to load membership
        $memberships = civicrm_api3('Membership', 'get', array('id' => $objectId, 'return' => 'contact_id'));
        if ($memberships['count'] == 1) {
          $membership = reset($memberships['values']);
          $calculator->update($membership['contact_id']);
        }
      } else {
        // contact_id known -> update contact
        $calculator->update($objectRef->contact_id);
      }
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function membershipstats_civicrm_config(&$config) {
  _membershipstats_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function membershipstats_civicrm_install() {
  // create custom group
  require_once 'membershipstats.customgroup.php';
  membershipstats_create_customgroup();

  _membershipstats_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function membershipstats_civicrm_enable() {
  // update stats for all contacts
  require_once 'membershipstats.customgroup.php';
  require_once 'CRM/Membershipstats/Calculator.php';
  require_once 'CRM/Membershipstats/Form/Settings.php';
  $calculator = new CRM_Membershipstats_Calculator();
  $calculator->updateAll();

  _membershipstats_civix_civicrm_enable();
}
