<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_db.php' );

auth_reauthenticate ();
access_ensure_global_level ( plugin_config_get ( 'access_level' ) );

html_page_top1 ( plugin_lang_get ( 'config_page_title' ) );
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/roadmappro.js"></script>';
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/jscolor/jscolor.js"></script>';
echo '<link rel="stylesheet" href="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro.css.php"/>' . "\n";

html_page_top2 ();
print_manage_menu ();

$roadmapDb = new roadmap_db();
echo '<form action="' . plugin_page ( 'config_update' ) . '" method="post">';
echo '<div class="surrounder">';
echo getChapterHeadrow ( 'config_page_general' )->saveHTML (); ?>

   <div class="row">
      <?php printWrapperInHTML ( 'div', 'gridcol-1 category_name_field', '<span class="required">*</span>' . plugin_lang_get ( 'config_page_access_level' ) ); ?>
      <div class="gridcol-5 category_value_field-0">
         <label>
            <select name="access_level">
               <?php print_enum_string_option_list ( 'access_levels', plugin_config_get ( 'access_level', ADMINISTRATOR ) ); ?>
            </select>
         </label>
      </div>
   </div>

   <div class="row">
      <?php printWrapperInHTML ( 'div', 'gridcol-1 category_name_field', plugin_lang_get ( 'config_page_show_menu' ) ); ?>

      <div class="gridcol-5 category_value_field-1">
         <label>
            <input type="radio" name="show_menu"
                   value="1"
               <?php
               if ( plugin_config_get ( 'show_menu' ) == ON )
               {
                  echo ' checked="checked"';
               }
               ?>/><?php echo lang_get ( 'yes' ); ?>
         </label>
         <label>
            <input type="radio" name="show_menu"
                   value="0"
               <?php
               if ( plugin_config_get ( 'show_menu' ) == OFF )
               {
                  echo ' checked="checked"';
               }
               ?>/><?php echo lang_get ( 'no' ); ?>
         </label>
      </div>
   </div>

   <div class="row">
      <?php printWrapperInHTML ( 'div', 'gridcol-1 category_name_field', plugin_lang_get ( 'config_page_show_footer' ) ); ?>

      <div class="gridcol-5 category_value_field-0">
         <label>
            <input type="radio" name="show_footer"
                   value="1"
               <?php
               if ( plugin_config_get ( 'show_footer' ) == ON )
               {
                  echo ' checked="checked"';
               }
               ?>/><?php echo lang_get ( 'yes' ); ?>
         </label>
         <label>
            <input type="radio" name="show_footer"
                   value="0"
               <?php
               if ( plugin_config_get ( 'show_footer' ) == OFF )
               {
                  echo ' checked="checked"';
               }
               ?>/><?php echo lang_get ( 'no' ); ?>
         </label>
      </div>
   </div>
<?php
if ( config_get ( 'enable_eta' ) )
{
   echo '<div class="row">';
   printWrapperInHTML ( 'div', 'gridcol-1 category_name_field', plugin_lang_get ( 'config_page_eta_unit' ) );
   printWrapperInHTML ( 'div', 'gridcol-5 category_value_field-1', '<label for="eta_unit"></label><input type="text" id="eta_unit" name="eta_unit" size="15" maxlength="128" value="' . $roadmapDb->dbGetEtaUnit () . '"/>' );
   echo '</div>';
   echo getChapterHeadrow ( 'config_page_eta_management' )->saveHTML ();
   echo '<div class="row">';
   printWrapperInHTML ( 'div', 'gridcol-1 category_name_field', plugin_lang_get ( 'config_page_eta_name' ) );
   printWrapperInHTML ( 'div', 'gridcol-1 category_name_field', plugin_lang_get ( 'config_page_eta_value' ) );
   printWrapperInHTML ( 'div', 'gridcol-4 category_name_field', plugin_lang_get ( 'config_page_eta_unit' ) );
   echo '</div>';
   /** eta management */

   $etaEnumString = config_get ( 'eta_enum_string' );
   $etaEnumValues = MantisEnum::getValues ( $etaEnumString );
   $rowCount = count ( $etaEnumValues );
   $catIndex = 1;
   foreach ( $etaEnumValues as $etaEnumValue )
   {
      echo '<div class="row">';
      printWrapperInHTML ( 'div', 'gridcol-1 category_name_field', string_display_line ( get_enum_element ( 'eta', $etaEnumValue ) ) );
      printWrapperInHTML ( 'div', 'gridcol-1 category_value_field-' . $catIndex, '<label><input type="text" name="eta_value[]" value="' . $roadmapDb->dbGetEtaRowByKey ( $etaEnumValue )[ 2 ] . '"/></label>' );
      printWrapperInHTML ( 'div', 'gridcol-4 category_value_field-' . $catIndex, $roadmapDb->dbGetEtaUnit () );
      echo '</div>';
      $catIndex = ( $catIndex + 1 ) % 2;
   }
}
echo getChapterHeadrow ( 'config_page_roadmap_profile_management' )->saveHTML ();
printProfileAttributesInHTML ();
echo '<div class="row">';
printWrapperInHTML ( 'div', 'gridcol-1 category_value_field-0', '<label for="profile_name"></label><input type="text" id="profile_name" name="profile_name" size="15" maxlength="128" value=""/>' );
?>
   <div class="gridcol-1 category_value_field-0">
      <label>
         <select name="profile_status[]" multiple="multiple">
            <?php print_enum_string_option_list ( 'status' ); ?>
         </select>
      </label>
   </div>
<?php
printWrapperInHTML ( 'div', 'gridcol-1 category_value_field-0', '<label><input class="color {pickerFace:4,pickerClosable:true}" type="text" name="new_profile_color" value=""/></label>' );
printWrapperInHTML ( 'div', 'gridcol-1 category_value_field-0', '<label for="profile_priority"></label><input type="text" id="profile_priority" name="profile_priority" size="5" maxlength="128" value=""/>' );
printWrapperInHTML ( 'div', 'gridcol-2 category_value_field-0', '<label><input type="submit" name="add_profile" class="button" value="' . plugin_lang_get ( 'config_page_add_profile' ) . '"/></label>' );
echo '</div>';

$profiles = $roadmapDb->dbGetRoadmapProfiles ();
if ( empty( $profiles ) == false )
{
   echo '<div id="profile_container">';
   echo getChapterHeadrow ( 'config_page_profile_list' )->saveHTML ();
   printProfileAttributesInHTML ();
   $catIndex = 1;
   foreach ( $profiles as $profile )
   {
      $profileId = $profile[ 0 ];
      $dbProfileName = $profile[ 1 ];
      $dbProfileColor = $profile[ 2 ];
      $dbProfileStatus = $profile[ 3 ];
      $dbProfilePriority = $profile[ 4 ];
      $profileStatusArray = explode ( ';', $dbProfileStatus );

      $statusString = '';
      $counter = count ( $profileStatusArray );
      for ( $index = 0; $index < $counter; $index++ )
      {
         $profileStatus = $profileStatusArray[ $index ];
         $statusString .= string_display_line ( get_enum_element ( 'status', $profileStatus ) );
         if ( $index < ( $counter - 1 ) )
         {
            $statusString .= ',&nbsp;';
         }
      }

      echo '<div class="row" id="profile_row">';
      printWrapperInHTML ( 'div', 'gridcol-1 category_value_field-' . $catIndex, string_display_line ( $dbProfileName ) );
      printWrapperInHTML ( 'div', 'gridcol-1 category_value_field-' . $catIndex, $statusString );
      printWrapperInHTML ( 'div', 'gridcol-1 category_value_field-' . $catIndex, '<label><input class="color {pickerFace:4,pickerClosable:true}" type="text" name="profile_color" value="#' . $dbProfileColor . '"/></label>' );
      printWrapperInHTML ( 'div', 'gridcol-1 category_value_field-' . $catIndex, $dbProfilePriority );
      printWrapperInHTML ( 'div', 'gridcol-2 category_value_field-' . $catIndex, '<a style="text-decoration: none;"href="' . plugin_page ( 'config_delete_profile' ) . '&amp;profile_id=' . $profileId . '"><input type="button" value="' . plugin_lang_get ( 'config_page_delete_profile' ) . '"/></a>' );
      echo '</div>';
      $catIndex = ( $catIndex + 1 ) % 2;
   }
   echo '</div>';
}
$statusEnumString = config_get ( 'status_enum_string' );
$statusEnumValues = MantisEnum::getValues ( $statusEnumString );
$statusEnumStringArray = array ();
foreach ( $statusEnumValues as $statusEnumValue )
{
   array_push ( $statusEnumStringArray, get_enum_element ( 'status', $statusEnumValue ) );
}
$i = json_encode ( $statusEnumStringArray );
echo '<script type="text/javascript">var status_array =' . $i . ';</script>';
echo '<div class="row grid_center">';
printWrapperInHTML ( 'div', 'gridcol-6', '<input type="submit" name="config_change" class="button" value="' . lang_get ( 'update_prefs_button' ) . '"/>&nbsp;<input type="submit" name="config_reset" class="button" value="' . lang_get ( 'reset_prefs_button' ) . '"/>' );
echo '</div>';
echo '</div>';
echo '</form>';
html_page_bottom1 ();

function getChapterHeadrow ( $langString )
{
   $dom = new DOMDocument();

   $rowElement = $dom->createElement ( 'div' );
   $rowAttribute = $dom->createAttribute ( 'class' );
   $rowAttribute->value = 'row';
   $rowElement->appendChild ( $rowAttribute );
   $colElement = $dom->createElement ( 'div', plugin_lang_get ( $langString ) );
   $colAttribute = $dom->createAttribute ( 'class' );
   $colAttribute->value = 'gridcol-6 title_row';
   $colElement->appendChild ( $colAttribute );
   $rowElement->appendChild ( $colElement );

   $dom->appendChild ( $rowElement );

   return $dom;
}

function printPriorityDescriptionInHTML ()
{
   echo '<a class="rcv_tooltip">';
   echo '&nbsp[i]';
   echo '<span>';
   echo '<div class="rcv_tooltip_content">';
   echo utf8_substr ( string_email_links ( plugin_lang_get ( 'config_page_prio_decription' ) ), 0, 255 );
   echo '</div>';
   echo '</span>';
   echo '</a>';
}

function printWrapperInHTML ( $htmlTag, $htmlClass, $content )
{
   echo '<' . $htmlTag . ' class="' . $htmlClass . '">' . $content . '</' . $htmlTag . '>';
}

function printProfileAttributesInHTML ()
{
   echo '<div class="row">';
   printWrapperInHTML ( 'div', 'gridcol-1 category_name_field', plugin_lang_get ( 'config_page_profile_name' ) );
   printWrapperInHTML ( 'div', 'gridcol-1 category_name_field', plugin_lang_get ( 'config_page_profile_status' ) );
   printWrapperInHTML ( 'div', 'gridcol-1 category_name_field', plugin_lang_get ( 'config_page_profile_color' ) );
   echo '<div class="gridcol-1 category_name_field">' . plugin_lang_get ( 'config_page_profile_prio' );
   printPriorityDescriptionInHTML ();
   echo '</div>';
   printWrapperInHTML ( 'div', 'gridcol-2 category_name_field', plugin_lang_get ( 'config_page_profile_action' ) );
   echo '</div>';
}
