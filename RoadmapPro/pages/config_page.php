<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );

auth_reauthenticate ();

html_page_top1 ( plugin_lang_get ( 'config_page_title' ) );
echo '<link rel="stylesheet" href="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro.css.php/>' . "\n";
html_page_top2 ();
print_manage_menu ();
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/jscolor/jscolor.js"></script>';
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/roadmappro.js"></script>';
$roadmapDb = new roadmap_db();

echo '<br/>';
echo '<form action="' . plugin_page ( 'config_update' ) . '" method="post">';
echo form_security_field ( 'plugin_RoadmapPro_config_update' );

echo stringDisplayTableHead ( 'width75' );

/** General configuration */
print_config_table_title_row ( 3, 'config_page_general' );
/** Show menu */
print_config_table_row ();
print_config_table_category_col ( 1, 1, 'config_page_show_menu' );
print_config_table_radio_button_col ( 2, 'show_menu' );
echo '</tr>';
/** Show plugin information in footer */
print_config_table_row ();
print_config_table_category_col ( 1, 1, 'config_page_show_footer' );
print_config_table_radio_button_col ( 2, 'show_footer' );
echo '</tr>';

/** eta management */
if ( config_get ( 'enable_eta' ) )
{
   print_config_table_title_row ( 3, 'config_page_eta_management' );
   print_config_table_row ();
   print_config_table_category_col ( 1, 1, 'config_page_eta_unit' );
   echo '<td colspan="2"><label for="eta_unit"></label><input type="text" id="eta_unit" name="eta_unit" size="15" maxlength="128" value="' . $roadmapDb->dbGetEtaUnit () . '"/></td>';
   echo '</tr>';

   print_config_table_row ();
   print_config_table_category_col ( 1, 1, 'config_page_eta_name' );
   print_config_table_category_col ( 1, 1, 'config_page_eta_value' );
   print_config_table_category_col ( 1, 1, 'config_page_eta_unit' );
   echo '</tr>';
   /** eta management */

   $etaEnumString = config_get ( 'eta_enum_string' );
   $etaEnumValues = MantisEnum::getValues ( $etaEnumString );
   $rowCount = count ( $etaEnumValues );
   foreach ( $etaEnumValues as $etaEnumValue )
   {
      print_config_table_row ();
      echo '<td>' . string_display_line ( get_enum_element ( 'eta', $etaEnumValue ) ) . '</td>';
      echo '<td><label><input type="text" name="eta_value[]" value="' . $roadmapDb->dbGetEtaRowByKey ( $etaEnumValue )[ 2 ] . '"/></label></td>';
      echo '<td colspan="4">' . $roadmapDb->dbGetEtaUnit () . '</td>';
      echo '</tr>';
   }
}
echo stringDisplayTableFoot ();

echo stringDisplayTableHead ( 'width75', 'profiles' );
echo '<tbody>';

/** show profiles */
$profiles = $roadmapDb->dbGetRoadmapProfiles ();
$profileCount = count ( $profiles );
if ( $profileCount > 0 )
{
   print_config_table_title_row ( 6, 'config_page_roadmap_profile_management' );
   print_config_table_row ();
   print_config_table_category_col ( 1, 1, 'config_page_profile_name' );
   print_config_table_category_col ( 1, 1, 'config_page_profile_status' );
   print_config_table_category_col ( 1, 1, 'config_page_profile_color' );
   print_config_table_category_col ( 1, 1, 'config_page_profile_prio' );
   print_config_table_category_col ( 1, 1, 'config_page_profile_effort' );
   print_config_table_category_col ( 1, 1, 'config_page_profile_action' );
   echo '</tr>';

   /** iterate through profiles */
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

      print_config_table_row ();
      /** profile name */
      echo '<td>';
      echo '<input type="hidden" name="profile-id[]" value="' . $dbProfileId . '" />';
      echo '<input type="text" name="profile-name[]" size="15" maxlength="128" value="' . string_display_line ( $dbProfileName ) . '" />';
      echo '</td>';
      /** profile status */
      echo '<td><select name="profile-status-' . $index . '[]" multiple="multiple">';
      print_enum_string_option_list ( 'status', $profileStatusArray );
      echo '</select></td>';
      /** profile color */
      echo '<td><label>';
      echo '<input class="color {pickerFace:4,pickerClosable:true}" type="text" name="profile-color[]" value="#' . $dbProfileColor . '" />';
      echo '</label></td>';
      /** profile priority */
      echo '<td><input type="text" name="profile-prio[]" size="15" maxlength="3" value="' . $dbProfilePriority . '" /></td>';
      /** profile effort */
      echo '<td><input type="text" name="profile-effort[]" size="15" maxlength="3" value="' . $dbProfileEffort . '" /></td>';
      /** delete profile button */
      echo '<td>';
      echo '<a style="text-decoration: none;" href="' . plugin_page ( 'config_delete_profile' ) .
         '&amp;profile_id=' . $dbProfileId . '">';
      echo '<span class="input">';
      echo '<input type="button" value="' . plugin_lang_get ( 'config_page_delete_profile' ) . '" />';
      echo '</span>';
      echo '</a>';
      echo '</td>';

      echo '</tr>';
   }
   echo '</tr>';
}
echo '</tbody>';
echo stringDisplayTableFoot ();

echo stringDisplayTableHead ( 'width75' );
echo '<tbody>';
echo '<tr>';
$statusEnumConfig = config_get ( 'status_enum_string' );
$statusEnumValues = MantisEnum::getValues ( $statusEnumConfig );
$statusEnumStrings = array ();
foreach ( $statusEnumValues as $statusEnumValue )
{
   array_push ( $statusEnumStrings, get_enum_element ( 'status', $statusEnumValue ) );
}
$jsStatusEnumValueArray = json_encode ( $statusEnumValues );
$jsStatusEnumStringArray = json_encode ( $statusEnumStrings );
echo '<script type="text/javascript">var statusValues =' . $jsStatusEnumValueArray . ';</script>';
echo '<script type="text/javascript">var statusStrings =' . $jsStatusEnumStringArray . ';</script>';
echo '<td class="left">';
echo '<input type="button" value="+" onclick="addProfileRow(statusValues,statusStrings)" />&nbsp;';
echo '<input type="button" value="-" onclick="delProfileRow(' . $profileCount . ')" />&nbsp;';
echo '</td>';

echo '<td class="center" colspan="5">';
echo '<input type="submit" name="config_change" class="button" value="' . lang_get ( 'update_prefs_button' ) . '"/>&nbsp';
echo '<input type="submit" name="config_reset" class="button" value="' . lang_get ( 'reset_prefs_button' ) . '"/>';
echo '</td>';
echo '</tr>';
echo '</tbody>';
echo stringDisplayTableFoot ();

echo '</form>';
html_page_bottom1 ();


/**
 * Prints a table row in the plugin config area
 */
function print_config_table_row ()
{
   if ( roadmap_pro_api::check_mantis_version_is_released () )
   {
      echo '<tr ' . helper_alternate_class () . '>';
   }
   else
   {
      echo '<tr>';
   }
}

/**
 * Prints a category column in the plugin config area
 *
 * @param $colspan
 * @param $rowspan
 * @param $lang_string
 */
function print_config_table_category_col ( $colspan, $rowspan, $lang_string )
{
   echo '<td class="category" colspan="' . $colspan . '" rowspan="' . $rowspan . '">';
   echo plugin_lang_get ( $lang_string );
   echo '</td>';
}

/**
 * Prints a title row in the plugin config area
 *
 * @param $colspan
 * @param $lang_string
 */
function print_config_table_title_row ( $colspan, $lang_string )
{
   echo '<tr>';
   echo '<td class="form-title" colspan="' . $colspan . '">';
   echo plugin_lang_get ( $lang_string );
   echo '</td>';
   echo '</tr>';
}

/**
 * Prints a radio button element in the plugin config area
 *
 * @param $colspan
 * @param $name
 */
function print_config_table_radio_button_col ( $colspan, $name )
{
   echo '<td width="100px" colspan="' . $colspan . '">';
   echo '<label>';
   echo '<input type="radio" name="' . $name . '" value="1"';
   echo ( ON == plugin_config_get ( $name ) ) ? 'checked="checked"' : '';
   echo '/>' . lang_get ( 'yes' );
   echo '</label>';
   echo '<label>';
   echo '<input type="radio" name="' . $name . '" value="0"';
   echo ( OFF == plugin_config_get ( $name ) ) ? 'checked="checked"' : '';
   echo '/>' . lang_get ( 'no' );
   echo '</label>';
   echo '</td>';
}

/**
 * Prints a color picker element in the plugin config area
 *
 * @param $colspan
 * @param $name
 */
function print_config_table_color_picker_row ( $colspan, $name )
{
   echo '<td width="100px" colspan="' . $colspan . '">';
   echo '<label>';
   echo '<input class="color {pickerFace:4,pickerClosable:true}" type="text" name="' . $name . '" value="" />';
   echo '</label>';
   echo '</td>';
}

/**
 * @param $colspan
 * @param $name
 */
function print_config_table_text_input_field ( $colspan, $name )
{
   $profile = gpc_get_string ( 'profile', '' );
   echo '<td width="100px" colspan="' . $colspan . '">';
   echo '<input type="text" id="type" name="' . $name . '" size="15" maxlength="128" value="' . $profile . '">&nbsp';
   echo '<input type="submit" name="add_profile" class="button" value="' . plugin_lang_get ( 'config_page_add_profile' ) . '">';
   echo '</td>';
}

function stringDisplayTableHead ( $class, $id = null )
{
   $htmlString = '';
   if ( roadmap_pro_api::check_mantis_version_is_released () == false )
   {
      $htmlString .= '<div class="form-container">';
   }
   $htmlString .= '<table align="center" cellspacing="1" class="' . $class . '"';
   if ( is_null ( $id ) == false )
   {
      $htmlString .= ' id="' . $id . '"';
   }
   $htmlString .= '>';
   return $htmlString;
}

function stringDisplayTableFoot ()
{
   $htmlString = '</table>';
   if ( roadmap_pro_api::check_mantis_version_is_released () == false )
   {
      $htmlString .= '</div>';
   }

   return $htmlString;
}