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
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function membershipstats_civicrm_xmlMenu(&$files) {
  _membershipstats_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function membershipstats_civicrm_uninstall() {
  _membershipstats_civix_civicrm_uninstall();
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

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function membershipstats_civicrm_disable() {
  _membershipstats_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function membershipstats_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _membershipstats_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function membershipstats_civicrm_managed(&$entities) {
  _membershipstats_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function membershipstats_civicrm_caseTypes(&$caseTypes) {
  _membershipstats_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function membershipstats_civicrm_angularModules(&$angularModules) {
_membershipstats_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function membershipstats_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _membershipstats_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

