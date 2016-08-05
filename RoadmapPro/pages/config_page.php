<?php

require_once ( __DIR__ . '/../core/roadmap_html_api.php' );

auth_reauthenticate ();

html_page_top1 ( plugin_lang_get ( 'config_page_title' ) );
echo '<link rel="stylesheet" href="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro_config.css"/>';
html_page_top2 ();
print_manage_menu ();
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/jscolor/jscolor.js"></script>';
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/roadmappro.js"></script>';
$roadmapDb = new roadmap_db();

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
      echo '<tr>';
      echo '<td>' . string_display_line ( get_enum_element ( 'eta', $etaEnumValue ) ) . '</td>';
      echo '<td><input type="text" name="eta_value[]" value="' . $roadmapDb->dbGetEtaRowByKey ( $etaEnumValue )[ 2 ] . '"/></td>';
      echo '<td colspan="4">' . plugin_lang_get ( 'config_page_eta_unit' ) . '</td>';
      echo '</tr>';
   }

   roadmap_html_api::htmlPluginConfigCloseTable ();

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
   $etaThresholdRows = $roadmapDb->dbGetEtaThresholds ();
   $thresholdCount = count ( $etaThresholdRows );
   if ( $thresholdCount > 0 )
   {
      # iterate through thresholds
      for ( $index = 0; $index < $thresholdCount; $index++ )
      {
         $thresholdRow = $etaThresholdRows[ $index ];
         $thresholdId = $thresholdRow[ 0 ];
         $thresholdFrom = $thresholdRow[ 1 ];
         $thresholdTo = $thresholdRow[ 2 ];
         $thresholdUnit = $thresholdRow[ 3 ];
         $thresholdFactor = $thresholdRow[ 4 ];

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
         echo '<a class="button" href="' . plugin_page ( 'config_delete_threshold' ) .
            '&amp;threshold_id=' . $thresholdId . '">';
         echo '<input type="button" value="' . plugin_lang_get ( 'config_page_delete_profile' ) . '" />';
         echo '</a>';
         echo '</td>';

         echo '</tr>';
      }
   }
}
roadmap_html_api::htmlPluginConfigCloseTable ();

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
   roadmap_html_api::htmlPluginConfigCloseTable ();
}

roadmap_html_api::htmlPluginConfigOpenTable ( 'config-table', 'profiles' );
# show profiles
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
$profiles = $roadmapDb->dbGetProfiles ();
$profileCount = count ( $profiles );
if ( $profileCount > 0 )
{
   # iterate through profiles
   for ( $index = 0; $index < $profileCount; $index++ )
   {
      $profile = $profiles[ $index ];
      $dbProfileId = $profile[ 0 ];
      $dbProfileName = $profile[ 1 ];
      $dbProfileColor = $profile[ 2 ];
      $dbProfileStatus = $profile[ 3 ];
      $dbProfilePriority = $profile[ 4 ];
      $dbProfileEffort = $profile[ 5 ];
      $profileStatusArray = explode ( ';', $dbProfileStatus );

      echo '<tr>';
      # profile name
      echo '<td>';
      echo '<input type="hidden" name="profile-id[]" value="' . $dbProfileId . '" />';
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
      echo '<a class="button" href="' . plugin_page ( 'config_delete_profile' ) .
         '&amp;profile_id=' . $dbProfileId . '">';
      echo '<input type="button" value="' . plugin_lang_get ( 'config_page_delete_profile' ) . '" />';
      echo '</a>';
      echo '</td>';

      echo '</tr>';
   }
}
roadmap_html_api::htmlPluginConfigCloseTable ();

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
$jsStatusEnumValueArray = json_encode ( $statusEnumValues );
$jsStatusEnumStringArray = json_encode ( $statusEnumStrings );
echo '<script type="text/javascript">var statusValues =' . $jsStatusEnumValueArray . ';var statusStrings =' . $jsStatusEnumStringArray . ';</script>';
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
roadmap_html_api::htmlPluginConfigCloseTable ();

echo '</form>';
html_page_bottom1 ();
