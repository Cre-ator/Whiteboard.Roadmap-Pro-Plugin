<?php
require_once ( __DIR__ . '/../core/roadmap_html_api.php' );
require_once ( __DIR__ . '/../core/rProfileManager.php' );
require_once ( __DIR__ . '/../core/rProfile.php' );
require_once ( __DIR__ . '/../core/rGroupManager.php' );
require_once ( __DIR__ . '/../core/rGroup.php' );
require_once ( __DIR__ . '/../core/rThresholdManager.php' );
require_once ( __DIR__ . '/../core/rThreshold.php' );
require_once ( __DIR__ . '/../core/rEta.php' );

auth_reauthenticate ();

html_page_top1 ( plugin_lang_get ( 'config_page_title' ) );
echo '<link rel="stylesheet" href="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro_config.css"/>';
html_page_top2 ();
print_manage_menu ();
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/jscolor/jscolor.js"></script>';
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/roadmappro.js"></script>';

echo '<br/>';
echo '<form action="' . plugin_page ( 'config_update' ) . '" method="post">';
echo form_security_field ( 'plugin_RoadmapPro_config_update' );

roadmap_html_api::htmlPluginConfigOpenTable ( 'config-table' );

# General configuration
echo '<tr>';
roadmap_html_api::htmlPluginConfigOutputCol ( 'form-title', 'config_page_general', 3 );
echo '</tr>';
# Show menu
echo '<tr>';
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_show_menu' );
roadmap_html_api::htmlPluginConfigRadio ( 'show_menu', 2 );
echo '</tr>';
# Show plugin information in footer
echo '<tr>';
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_show_footer' );
roadmap_html_api::htmlPluginConfigRadio ( 'show_footer', 2 );
echo '</tr>';

# eta management
$thresholdCount = 0;
if ( config_get ( 'enable_eta' ) )
{
   echo '<tr>';
   roadmap_html_api::htmlPluginConfigOutputCol ( 'form-title', 'config_page_eta_management', 3 );
   echo '</tr>';

   echo '<tr>';
   roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_name' );
   roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_value' );
   roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_unit_title' );
   echo '</tr>';
   # eta management

   $etaEnumString = config_get ( 'eta_enum_string' );
   $etaEnumValues = MantisEnum::getValues ( $etaEnumString );
   $rowCount = count ( $etaEnumValues );
   foreach ( $etaEnumValues as $etaEnumValue )
   {
      $eta = new rEta( $etaEnumValue );
      echo '<tr>';
      echo '<td>' . string_display_line ( get_enum_element ( 'eta', $etaEnumValue ) ) . '</td>';
      echo '<td><input type="text" name="eta_value[]" value="' . $eta->getEtaUser () . '"/></td>';
      echo '<td colspan="4">' . plugin_lang_get ( 'config_page_eta_unit' ) . '</td>';
      echo '</tr>';
   }
   echo '</table>';

   # thresholds
   roadmap_html_api::htmlPluginConfigOpenTable ( 'config-table', 'thresholds' );
   echo '<tr>';
   roadmap_html_api::htmlPluginConfigOutputCol ( 'form-title', 'config_page_roadmap_eta_threshold_management', 6 );
   echo '</tr>';
   echo '<tr>';
   roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_threshold_from' );
   roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_threshold_to' );
   roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_unit_title' );
   roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_threshold_factor' );
   roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_action' );
   echo '</tr>';
   $thresholdIds = rThresholdManager::getRThresholdIds ();
   $thresholdCount = count ( $thresholdIds );
   if ( $thresholdCount > 0 )
   {
      # iterate through thresholds
      for ( $index = 0; $index < $thresholdCount; $index++ )
      {
         $thresholdId = $thresholdIds[ $index ];
         $threshold = new rThreshold( $thresholdId );
         $thresholdFrom = $threshold->getThresholdFrom ();
         $thresholdTo = $threshold->getThresholdTo ();
         $thresholdUnit = $threshold->getThresholdUnit ();
         $thresholdFactor = $threshold->getThresholdFactor ();

         echo '<tr>';
         # threshold from
         echo '<td>';
         echo '<input type="hidden" name="threshold-id[]" value="' . $thresholdId . '" />';
         echo '<input type="text" name="threshold-from[]" size="15" maxlength="128" value="' . string_display_line ( $thresholdFrom ) . '" />';
         echo '</td>';
         echo '<td>';
         echo '<input type="text" name="threshold-to[]" size="15" maxlength="128" value="' . string_display_line ( $thresholdTo ) . '" />';
         echo '</td>';
         echo '<td>';
         echo '<input type="text" name="threshold-unit[]" size="15" maxlength="128" value="' . string_display_line ( $thresholdUnit ) . '" />';
         echo '</td>';
         echo '<td>';
         echo '<input type="text" name="threshold-factor[]" size="15" maxlength="128" value="' . string_display_line ( $thresholdFactor ) . '" />';
         echo '</td>';

         echo '<td>';
         echo '<a class="button" href="' . plugin_page ( 'config_delete' ) .
            '&amp;threshold_id=' . $thresholdId . '">';
         echo '<input type="button" value="' . plugin_lang_get ( 'config_page_delete_profile' ) . '" />';
         echo '</a>';
         echo '</td>';

         echo '</tr>';
      }
   }
}
echo '</table>';

if ( config_get ( 'enable_eta' ) )
{
   roadmap_html_api::htmlPluginConfigOpenTable ( 'config-table' );
   echo '<tbody>';
   echo '<tr class="foot-row">';
   echo '<td class="left">';
   echo '<input type="button" value="+" onclick="addThresholdRow()" />&nbsp;';
   echo '<input type="button" value="-" onclick="delRow(' . $thresholdCount . ', \'thresholds\')" />&nbsp;';
   echo '</td>';
   echo '</tr>';
   echo '</tbody>';
   echo '</table>';
}

# profile groups
roadmap_html_api::htmlPluginConfigOpenTable ( 'config-table', 'profilegroups' );
echo '<tr>';
roadmap_html_api::htmlPluginConfigOutputCol ( 'form-title', 'config_page_prfgr_management', 2 );
echo '</tr>';
echo '<tr>';
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_name' );
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_prfgr_profiles' );
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_action' );
echo '</tr>';

# iterate through groups
$groupIds = rGroupManager::getRGroupIds ();
$groupCount = count ( $groupIds );
if ( $groupCount > 0 )
{
   for ( $index = 0; $index < $groupCount; $index++ )
   {
      $groupId = $groupIds[ $index ];
      $group = new rGroup( $groupId );
      $dbGroudName = $group->getGroupName ();
      $dbGroupProfiles = $group->getGroupProfiles ();

      $groupProfileEnumNames = array ();
      $profileEnumIds = roadmap_pro_api::getProfileEnumIds ();
      $profileEnumNames = roadmap_pro_api::getProfileEnumNames ();
      $profileEnumCount = count ( $profileEnumIds );
      $groupProfileArray = explode ( ';', $dbGroupProfiles );
      foreach ( $groupProfileArray as $profileId )
      {
         $profile = new rProfile( $profileId );
         $profileName = $profile->getProfileName ();

         array_push ( $groupProfileEnumNames, $profileName );
      }

      echo '<tr>';
      # group name
      echo '<td>';
      echo '<input type="hidden" name="group-id[]" value="' . $groupId . '" />';
      echo '<input type="text" name="group-name[]" size="15" maxlength="128" value="' . string_display_line ( $dbGroudName ) . '" />';
      echo '</td>';
      # group profiles
      echo '<td><select name="group-profile-' . $index . '[]" multiple="multiple">';
      for ( $pindex = 0; $pindex < $profileEnumCount; $pindex++ )
      {
         $profileId = $profileEnumIds[ $pindex ];
         $profileName = $profileEnumNames[ $pindex ];
         echo '<option value="' . $profileId . '"';
         check_selected ( $groupProfileEnumNames, $profileName );
         echo '>' . $profileName . '</option>';
      }
      echo '</select></td>';

      # delete group button
      echo '<td>';
      echo '<a class="button" href="' . plugin_page ( 'config_delete' ) .
         '&amp;group_id=' . $groupId . '">';
      echo '<input type="button" value="' . plugin_lang_get ( 'config_page_delete_profile' ) . '" />';
      echo '</a>';
      echo '</td>';

      echo '</tr>';
   }
}
echo '</table>';

roadmap_html_api::htmlPluginConfigOpenTable ( 'config-table' );
echo '<tbody>';
echo '<tr class="foot-row">';
$profileEnumIds = roadmap_pro_api::getProfileEnumIds ();
$profileEnumNames = roadmap_pro_api::getProfileEnumNames ();

$jsProfileEnumIdArray = json_encode ( $profileEnumIds );
$jsProfileEnumNameArray = json_encode ( $profileEnumNames );
echo '<script type="text/javascript">var profileIds =' . $jsProfileEnumIdArray . ';var profileNames =' . $jsProfileEnumNameArray . ';</script>';
echo '<td class="left">';
echo '<input type="button" value="+" onclick="addGroupRow(profileIds,profileNames)" />&nbsp;';
echo '<input type="button" value="-" onclick="delRow(' . $groupCount . ', \'profilegroups\')" />&nbsp;';
echo '</td>';

echo '</tr>';
echo '</tbody>';
echo '</table>';

# show profiles
roadmap_html_api::htmlPluginConfigOpenTable ( 'config-table', 'profiles' );
echo '<tr>';
roadmap_html_api::htmlPluginConfigOutputCol ( 'form-title', 'config_page_roadmap_profile_management', 6 );
echo '</tr>';
echo '<tr>';
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_name' );
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_status' );
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_color' );
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_prio' );
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_effort' );
roadmap_html_api::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_action' );
echo '</tr>';

# iterate through profiles
$profileIds = rProfileManager::getRProfileIds ();
$profileCount = count ( $profileIds );
if ( $profileCount > 0 )
{
   for ( $index = 0; $index < $profileCount; $index++ )
   {
      $profileId = $profileIds[ $index ];
      $profile = new rProfile( $profileId );
      $dbProfileName = $profile->getProfileName ();
      $dbProfileColor = $profile->getProfileColor ();
      $dbProfileStatus = $profile->getProfileStatus ();
      $dbProfilePriority = $profile->getProfilePriority ();
      $dbProfileEffort = $profile->getProfileEffort ();
      $profileStatusArray = explode ( ';', $dbProfileStatus );

      echo '<tr>';
      # profile name
      echo '<td>';
      echo '<input type="hidden" name="profile-id[]" value="' . $profileId . '" />';
      echo '<input type="text" name="profile-name[]" size="15" maxlength="128" value="' . string_display_line ( $dbProfileName ) . '" />';
      echo '</td>';
      # profile status
      echo '<td><select name="profile-status-' . $index . '[]" multiple="multiple">';
      print_enum_string_option_list ( 'status', $profileStatusArray );
      echo '</select></td>';
      # profile color
      echo '<td><label>';
      echo '<input class="color {pickerFace:4,pickerClosable:true}" type="text" name="profile-color[]" value="#' . $dbProfileColor . '" />';
      echo '</label></td>';
      # profile priority
      echo '<td><input type="text" name="profile-prio[]" size="15" maxlength="3" value="' . $dbProfilePriority . '" /></td>';
      # profile effort
      echo '<td><input type="text" name="profile-effort[]" size="15" maxlength="3" value="' . $dbProfileEffort . '" /></td>';
      # delete profile button
      echo '<td>';
      echo '<a class="button" href="' . plugin_page ( 'config_delete' ) .
         '&amp;profile_id=' . $profileId . '">';
      echo '<input type="button" value="' . plugin_lang_get ( 'config_page_delete_profile' ) . '" />';
      echo '</a>';
      echo '</td>';

      echo '</tr>';
   }
}
echo '</table>';

roadmap_html_api::htmlPluginConfigOpenTable ( 'config-table' );
echo '<tbody>';
echo '<tr class="foot-row">';
$statusEnumConfig = config_get ( 'status_enum_string' );
$statusEnumValues = MantisEnum::getValues ( $statusEnumConfig );
$statusEnumStrings = array ();
foreach ( $statusEnumValues as $statusEnumValue )
{
   array_push ( $statusEnumStrings, get_enum_element ( 'status', $statusEnumValue ) );
}
$jsProfileEnumIdArray = json_encode ( $statusEnumValues );
$jsProfileEnumNameArray = json_encode ( $statusEnumStrings );
echo '<script type="text/javascript">var statusValues =' . $jsProfileEnumIdArray . ';var statusStrings =' . $jsProfileEnumNameArray . ';</script>';
echo '<td class="left">';
echo '<input type="button" value="+" onclick="addProfileRow(statusValues,statusStrings)" />&nbsp;';
echo '<input type="button" value="-" onclick="delRow(' . $profileCount . ', \'profiles\')" />&nbsp;';
echo '</td>';

echo '<td class="center" colspan="5">';
echo '<input type="submit" name="config_change" class="button" value="' . lang_get ( 'update_prefs_button' ) . '"/>&nbsp';
echo '<input type="submit" name="config_reset" class="button" value="' . lang_get ( 'reset_prefs_button' ) . '"/>';
echo '</td>';
echo '</tr>';
echo '</tbody>';
echo '</table>';

echo '</form>';
html_page_bottom1 ();
