<?php
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rHtmlApi.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProApi.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProfileManager.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProfile.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rGroupManager.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rGroup.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rThresholdManager.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rThreshold.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rEta.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rWeekDayManager.php' );

auth_reauthenticate ();

html_page_top1 ( plugin_lang_get ( 'config_page_title' ) );
echo '<link rel="stylesheet" href="plugins/RoadmapPro/files/roadmappro_config.css"/>';
html_page_top2 ();
print_manage_menu ();
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/jscolor/jscolor.js"></script>';
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/roadmappro.js"></script>';

echo '<br/>';
echo '<form action="' . plugin_page ( 'config_update' ) . '" method="post">';
echo form_security_field ( 'plugin_RoadmapPro_config_update' );

rHtmlApi::htmlPluginConfigOpenTable ();
# General configuration
echo '<tr>';
echo '<td class="form-title" colspan="3">' . plugin_lang_get ( 'menu_title' ) . ':&nbsp;' . plugin_lang_get ( 'config_page_general' ) . '</td>';
echo '</tr>';
# Show menu
echo '<tr>';
rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_show_menu' );
rHtmlApi::htmlPluginConfigRadio ( 'show_menu', 2 );
echo '</tr>';
# Show plugin information in footer
echo '<tr>';
rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_show_footer' );
rHtmlApi::htmlPluginConfigRadio ( 'show_footer', 2 );
echo '</tr>';
echo '</table>';

# time calculation settings
if ( config_get ( 'enable_eta' ) )
{
   rHtmlApi::htmlPluginConfigOpenTable ();
   echo '<tr>';
   rHtmlApi::htmlPluginConfigOutputCol ( 'form-title', 'config_page_time_calc_title', 3 );
   echo '</tr>';

   echo '<tr>';
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_time_calc_day' );
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_time_calc_worktime' );
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_unit_title' );
   echo '</tr>';

   $weekDayValue = 10;
   $weekDayConfigString = rWeekDayManager::getWorkDayConfig ();
   $weekDayConfigArray = explode ( ';', $weekDayConfigString );
   for ( $index = 0; $index < 7; $index++ )
   {
      echo '<tr>';
      echo '<td>' . MantisEnum::getLabel ( plugin_lang_get ( 'config_page_time_calc_weekday_enum' ), $weekDayValue ) . '</td>';
      echo '<td><input type="number" min="0" max="24" step="0.1" name="weekDayValue[]" value="' . $weekDayConfigArray[ $index ] . '" /></td>';
      echo '<td>' . plugin_lang_get ( 'config_page_eta_unit' ) . '</td>';
      echo '</tr>';
      $weekDayValue += 10;
   }

   echo '</table>';
}

# eta management
$thresholdCount = 0;
if ( config_get ( 'enable_eta' ) )
{
   $etaEnumValues = MantisEnum::getValues ( config_get ( 'eta_enum_string' ) );
   rHtmlApi::htmlPluginConfigOpenTable ();
   echo '<tr>';
   rHtmlApi::htmlPluginConfigOutputCol ( 'form-title', 'config_page_eta_management', 3 );
   echo '</tr>';

   echo '<tr>';
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_default_eta', 1 );
   echo '<td colspan="2">';
   echo '<span class="select"><select ' . helper_get_tab_index () . ' id="defaulteta" name="defaulteta">';
   foreach ( $etaEnumValues as $etaEnumValue )
   {
      echo '<option value="' . $etaEnumValue . '"';
      check_selected ( plugin_config_get ( 'defaulteta' ), $etaEnumValue );
      echo '>' . string_display_line ( get_enum_element ( 'eta', $etaEnumValue ) ) . '</option>';
   }
   echo '</select></span>';
   echo '</td>';
   echo '</tr>';

   echo '<tr>';
   echo '<td class="category" colspan="1">' . plugin_lang_get ( 'config_page_calc_threshold' ) . '<br />';
   echo '<span class="small">' . plugin_lang_get ( 'config_page_calc_threshold_detail' ) . '</span>';
   echo '</td>';
   echo '<td colspan="2">';
   echo '<input type="number" step="1" name="calcthreshold" min="0" max="100" value="' . plugin_config_get ( 'calcthreshold' ) . '"/>';
   echo '</td>';
   echo '</tr>';

   echo '<tr class="foot-row"><td colspan="3">&nbsp;</td></tr>';

   echo '<tr>';
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_name' );
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_value' );
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_unit_title' );
   echo '</tr>';

   foreach ( $etaEnumValues as $etaEnumValue )
   {
      $eta = new rEta( $etaEnumValue );
      echo '<tr>';
      echo '<td>' . string_display_line ( get_enum_element ( 'eta', $etaEnumValue ) ) . '</td>';
      if ( $eta->getEtaUser () == NULL )
      {
         $eta->setEtaUser ( 0 );
         $eta->triggerInsertIntoDb ();
      }
      if ( $eta->getEtaConfig () == ETA_NONE )
      {
         echo '<td><input type="hidden" name="eta_value[]" value="0"/>' . plugin_lang_get ( 'config_page_eta_none_value' ) . '</td>';
      }
      else
      {
         echo '<td><input type="number" step="0.1" name="eta_value[]" value="' . $eta->getEtaUser () . '"/></td>';
      }
      echo '<td colspan="4">' . plugin_lang_get ( 'config_page_eta_unit' ) . '</td>';
      echo '</tr>';
   }
   echo '</table>';

   # thresholds
   rHtmlApi::htmlPluginConfigOpenTable ( 'thresholds' );
   echo '<tr>';
   rHtmlApi::htmlPluginConfigOutputCol ( 'form-title', 'config_page_roadmap_eta_threshold_management', 6 );
   echo '</tr>';
   echo '<tr>';
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_threshold_to' );
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_unit_title' );
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_eta_threshold_factor' );
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_action' );
   echo '</tr>';
   $thresholdIds = rThresholdManager::getRThresholdIds ();
   $thresholdCount = count ( $thresholdIds );
   if ( $thresholdCount > 0 )
   {
      # iterate through thresholds
      foreach ( $thresholdIds as $thresholdId )
      {
         $threshold = new rThreshold( $thresholdId );
         $thresholdTo = $threshold->getThresholdTo ();
         $thresholdUnit = $threshold->getThresholdUnit ();
         $thresholdFactor = $threshold->getThresholdFactor ();

         echo '<tr>';
         echo '<td>';
         echo '<input type="hidden" name="threshold-id[]" value="' . $thresholdId . '" />';
         echo '<input type="number" step="0.1" name="threshold-to[]" size="15" maxlength="128" value="' . string_display_line ( $thresholdTo ) . '" />';
         echo '</td>';
         echo '<td>';
         echo '<input type="text" name="threshold-unit[]" size="15" maxlength="128" value="' . string_display_line ( $thresholdUnit ) . '" />';
         echo '</td>';
         echo '<td>';
         echo '<input type="number" step="0.1" name="threshold-factor[]" size="15" maxlength="128" value="' . string_display_line ( $thresholdFactor ) . '" />';
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
   rHtmlApi::htmlPluginConfigOpenTable ();
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

# show profiles
rHtmlApi::htmlPluginConfigOpenTable ( 'profiles' );
echo '<tr>';
rHtmlApi::htmlPluginConfigOutputCol ( 'form-title', 'config_page_roadmap_profile_management', 6 );
echo '</tr>';
echo '<tr>';
rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_name' );
rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_status' );
rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_color' );
rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_prio' );
rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_effort' );
rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_action' );
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
      echo '<td><input type="number" name="profile-prio[]" size="15" maxlength="3" value="' . $dbProfilePriority . '" /></td>';
      # profile effort
      echo '<td><input type="number" name="profile-effort[]" size="15" maxlength="3" value="' . $dbProfileEffort . '" /></td>';
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

rHtmlApi::htmlPluginConfigOpenTable ();
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

echo '</tr>';
echo '</tbody>';
echo '</table>';

if ( $profileCount > 1 )
{
# profile groups
   rHtmlApi::htmlPluginConfigOpenTable ( 'profilegroups' );
   echo '<tr>';
   rHtmlApi::htmlPluginConfigOutputCol ( 'form-title', 'config_page_prfgr_management', 3 );
   echo '</tr>';
   echo '<tr>';
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_name' );
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_prfgr_profiles' );
   rHtmlApi::htmlPluginConfigOutputCol ( 'category', 'config_page_profile_action' );
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
         $profileEnumIds = rProApi::getProfileEnumIds ();
         $profileEnumNames = rProApi::getProfileEnumNames ();
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
}

rHtmlApi::htmlPluginConfigOpenTable ();
echo '<tbody>';
echo '<tr class="foot-row">';
if ( $profileCount > 1 )
{
   $profileEnumIds = rProApi::getProfileEnumIds ();
   $profileEnumNames = rProApi::getProfileEnumNames ();

   $jsProfileEnumIdArray = json_encode ( $profileEnumIds );
   $jsProfileEnumNameArray = json_encode ( $profileEnumNames );
   echo '<script type="text/javascript">var profileIds =' . $jsProfileEnumIdArray . ';var profileNames =' . $jsProfileEnumNameArray . ';</script>';
   echo '<td class="left">';
   echo '<input type="button" value="+" onclick="addGroupRow(profileIds,profileNames)" />&nbsp;';
   echo '<input type="button" value="-" onclick="delRow(' . $groupCount . ', \'profilegroups\')" />&nbsp;';
   echo '</td>';
}

echo '</tr>';
echo '</tbody>';
echo '</table>';

echo '<table align="center">';
echo '<tr>';
echo '<td><input type="submit" name="config_change" class="button" value="' . lang_get ( 'update_prefs_button' ) . '"/></td>';
echo '<td><input type="submit" name="config_reset" class="button" value="' . lang_get ( 'reset_prefs_button' ) . '"/></td>';
echo '</tr>';
echo '</table>';

echo '</form>';
html_page_bottom1 ();
