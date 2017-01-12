<?php
/*-------------------------------------------------------+
| SYSTOPIA Membership Stats Extension                    |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| http://www.systopia.de                                 |
+--------------------------------------------------------+
| License: AGPLv3, see LICENSE file                      |
+--------------------------------------------------------*/

/**
 * Algorithm for calculating the membership stats
 *
 * @author Björn Endres (SYSTOPIA) <endres@systopia.de>
 * @license AGPL-3.0
 */
class CRM_Membershipstats_Calculator {

  protected $membershipstats_table    = CIVICRM_MEMBERSHIPSTATS_TABLE;
  protected $membership_types         = NULL; // i.e. ALL
  protected $active_status_ids        = NULL; // to be set
  
  // fields
  protected $custom_field_list          = NULL; // to be set
  protected $current_member_field       = NULL; // to be set
  protected $current_member_type_field  = NULL; // to be set
  protected $member_since_field         = NULL; // to be set

  /**
   * Init calculator, e.g. look up some values
   */
  public function __construct() {
    $this->active_status_ids = array();
    $statuses = civicrm_api3('MembershipStatus', 'get', array(
      'is_current_member' => '1',
      'return'            => 'id'));
    foreach ($statuses['values'] as $status) {
      $this->active_status_ids[] = $status['id'];
    }

    $customgroup = civicrm_api3('CustomGroup', 'getsingle', array(
      'table_name' => CIVICRM_MEMBERSHIPSTATS_TABLE,
      'return'     => 'id',
      ));

    $fields = civicrm_api3('CustomField', 'get', array(
      'custom_group_id' => $customgroup['id'],
      'return'          => 'id,column_name',
      'option.limit'    => 0,
      ));

    foreach ($fields['values'] as $field) {
      switch ($field['column_name']) {
        case CIVICRM_MEMBERSHIPSTATS_CURRENT:
          $this->current_member_field = 'custom_' . $field['id'];
          break;
        case CIVICRM_MEMBERSHIPSTATS_CURRENT_TYPE:
          $this->current_member_type_field = 'custom_' . $field['id'];
          break;
        case CIVICRM_MEMBERSHIPSTATS_MEMBER_SINCE:
          $this->member_since_field = 'custom_' . $field['id'];
          break;
        default:
          break;
      }
    }

    $this->custom_field_list = implode(',', array($this->current_member_field, $this->current_member_type_field, $this->member_since_field));
  }

  /**
   * Update membership stats for a given contact
   */
  public function update($contact_id) {
    error_log("UPDATING $contact_id");
    $contact_id = (int) $contact_id;
    if ($contact_id < 1) return;

    // first: load all memberships
    $query = array(
      'option.limit'  => 0,
      'is_test'       => 0,
      'contact_id'    => $contact_id,
      // this will hide membership_name: 'return'        => 'join_date,membership_name,status_id',
    );
    if ($this->membership_types) {
      $query['membership_type_id'] = array('IN' => $this->membership_types);
    }
    $memberships = civicrm_api3('Membership', 'get', $query);


    // then extract data by iterating through memberships
    $current_member      = 0;
    $current_member_type = '';
    $member_since        = NULL;

    foreach ($memberships['values'] as $membership) {
      // update current_member
      if (in_array($membership['status_id'], $this->active_status_ids)) {
        // this is an active membership
        $current_member = 1;

        // update current_member_type
        if (!empty($membership['membership_name'])) {
          if ($current_member_type == '') {
            $current_member_type = $membership['membership_name'];
          } elseif ($current_member_type != $membership['membership_name']) {
            $current_member_type = 'Multiple';
          }
        }
      }

      // update member_since
      if (!empty($membership['join_date'])) {
        $join_date = substr($membership['join_date'], 0, 10);
        if ($member_since == NULL) {
          $member_since = $join_date;
        } elseif ($member_since > $join_date) {
          $member_since = $join_date;
        }
      }
    }

    // see if the contact needs updating
    $current_data = civicrm_api3('Contact', 'getsingle', array(
      'id'     => $contact_id,
      'return' => $this->custom_field_list,
      ));

    // compile update
    $update = array();
    if ($current_data[$this->current_member_field] != $current_member) {
      $update[$this->current_member_field] = $current_member;
    }

    if ($current_data[$this->current_member_type_field] != $current_member_type) {
      $update[$this->current_member_type_field] = $current_member_type;
    }

    if (substr($current_data[$this->member_since_field], 0, 10) != $member_since) {
      $update[$this->member_since_field] = $member_since;
    }

    // If there is something to update, store new values
    if (!empty($update)) {
      $update['id'] = $contact_id;
      civicrm_api3('Contact', 'create', $update);
    }
  }

  /**
   * Update membership stats for *all* contacts
   */
  public function updateAll() {
    // first: update all contacts with at least one membership entity
    $query = CRM_Core_DAO::executeQuery("SELECT DISTINCT(contact_id) FROM civicrm_membership;");
    while ($query->fetch()) {
      $this->update($query->contact_id);
    }

    // secondly: delete stats entry for contacts that don't have a membership entity (any more)
    CRM_Core_DAO::executeQuery("DELETE FROM `{$this->membershipstats_table}` WHERE entity_id IN (SELECT civicrm_contact.id FROM civicrm_contact LEFT JOIN civicrm_membership ON civicrm_membership.contact_id = civicrm_contact.id WHERE civicrm_membership.id IS NULL);");
  }
}