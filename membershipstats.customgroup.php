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
 * this function will ensure that the necessary custom group 
 * and custom fields will be available
 */
function membershipstats_create_customgroup() {
  // CREATE GROUP
  try {
    $customgroup = civicrm_api3('CustomGroup', 'getsingle', array('table_name' => CIVICRM_MEMBERSHIPSTATS_TABLE));
  } catch (Exception $e) {
    // doesn't exist:
    $customgroup = civicrm_api3('CustomGroup', 'create', array(
      'name'                 => 'Membership_Stats',
      'title'                => 'Membership Stats',
      'extends'              => 'Contact',
      'style'                => 'Inline',
      'collapse_display'     => '1',
      'is_active'            => '1',
      'table_name'           => CIVICRM_MEMBERSHIPSTATS_TABLE,
      'is_multiple'          => '0',
      'collapse_adv_display' => '0',
      'is_reserved'          => '0'
    ));
  }
  $custom_group_id = (int) $customgroup['id'];

  // CREATE CURRENT FIELD
  $fieldsearch = civicrm_api3('CustomField', 'getcount', array('column_name' => CIVICRM_MEMBERSHIPSTATS_CURRENT, 'custom_group_id' => $customgroup['id']));
  if (empty($fieldsearch)) {
    // doesn't exist:
    civicrm_api3('CustomField', 'create', array(
      'name'                 => 'Current_Member',
      'label'                => 'Current Member',
      'custom_group_id'      => $custom_group_id,
      'column_name'          => CIVICRM_MEMBERSHIPSTATS_CURRENT,
      'data_type'            => 'Boolean',
      'html_type'            => 'Radio',
      'default_value'        => '0',
      'is_required'          => '0',
      'is_searchable'        => '1',
      'is_search_range'      => '0',
      'is_view'              => '1',
      'is_active'            => '1',
    ));
  }

  // CREATE CURRENT TYPE FIELD
  $fieldsearch = civicrm_api3('CustomField', 'getcount', array('column_name' => CIVICRM_MEMBERSHIPSTATS_CURRENT_TYPE, 'custom_group_id' => $custom_group_id));
  if (empty($fieldsearch)) {
    // doesn't exist:
    civicrm_api3('CustomField', 'create', array(
      'name'                 => 'Current_Membership_Type',
      'label'                => 'Current Membership Type',
      'custom_group_id'      => $custom_group_id,
      'column_name'          => CIVICRM_MEMBERSHIPSTATS_CURRENT_TYPE,
      'data_type'            => 'String',
      'html_type'            => 'Text',
      'default_value'        => '',
      'is_required'          => '0',
      'is_searchable'        => '1',
      'is_search_range'      => '0',
      'is_view'              => '1',
      'is_active'            => '1',
      'text_length'          => '128',
    ));
  }

  // CREATE MEMBER SINCE FIELD
  $fieldsearch = civicrm_api3('CustomField', 'getcount', array('column_name' => CIVICRM_MEMBERSHIPSTATS_MEMBER_SINCE, 'custom_group_id' => $custom_group_id));
  if (empty($fieldsearch)) {
    // doesn't exist:
    $test = civicrm_api3('CustomField', 'create', array(
      'name'                 => 'Member_Since',
      'label'                => 'Member Since',
      'custom_group_id'      => $custom_group_id,
      'column_name'          => CIVICRM_MEMBERSHIPSTATS_MEMBER_SINCE,
      'data_type'            => 'Date',
      'html_type'            => 'Select Date',
      'default_value'        => 'NULL',
      'is_required'          => '0',
      'is_searchable'        => '1',
      'is_search_range'      => '0',
      'is_view'              => '1',
      'is_active'            => '1',
      'date_format'          => 'yy-mm-dd',
    ));
  }

  // // CREATE MEMBER FOR FIELD
  // $fieldsearch = civicrm_api3('CustomField', 'getcount', array('column_name' => CIVICRM_MEMBERSHIPSTATS_MEMBER_FOR, 'custom_group_id' => $custom_group_id));
  // if (empty($fieldsearch)) {
  //   // doesn't exist:
  //   civicrm_api3('CustomField', 'create', array(
  //     'name'                 => 'Member_Days',
  //     'label'                => 'Member Days',
  //     'custom_group_id'      => $custom_group_id,
  //     'column_name'          => CIVICRM_MEMBERSHIPSTATS_MEMBER_FOR,
  //     'data_type'            => 'Int',
  //     'html_type'            => 'Text',
  //     'default_value'        => '0',
  //     'is_required'          => '0',
  //     'is_searchable'        => '1',
  //     'is_search_range'      => '1',
  //     'is_view'              => '1',
  //     'is_active'            => '1',
  //   ));
  // }
}
