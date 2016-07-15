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

$roadmap_db = new roadmap_db();
echo '<form action="' . plugin_page ( 'config_update' ) . '" method="post">';
?>
   <div class="surrounder">
      <?php echo get_chapter_headrow ( 'config_page_general' )->saveHTML (); ?>

      <div class="row">
         <div class="gridcol-1 category_name_field">
            <span class="required">*</span><?php echo plugin_lang_get ( 'config_page_access_level' ); ?>
         </div>

         <div class="gridcol-5 category_value_field-0">
            <label>
               <select name="roadmap_pro_access_level">
                  <?php print_enum_string_option_list ( 'access_levels', plugin_config_get ( 'access_level', ADMINISTRATOR ) ); ?>
               </select>
            </label>
         </div>
      </div>

      <div class="row">
         <div class="gridcol-1 category_name_field">
            <?php echo plugin_lang_get ( 'config_page_show_menu' ); ?>
         </div>


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
         <div class="gridcol-1 category_name_field">
            <?php echo plugin_lang_get ( 'config_page_show_footer' ); ?>
         </div>

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
         ?>
         <div class="row">
            <div class="gridcol-1 category_name_field">
               <?php echo plugin_lang_get ( 'config_page_eta_unit' ); ?>
            </div>

            <div class="gridcol-5 category_value_field-1">
               <label for="eta_unit"></label>
               <input type="text" id="eta_unit" name="eta_unit" size="15" maxlength="128"
                      value="<?php echo $roadmap_db->get_eta_unit () ?>"/>
            </div>
         </div>

         <?php echo get_chapter_headrow ( 'config_page_eta_management' )->saveHTML (); ?>
         <div class="row">
            <div class="gridcol-1 category_name_field">
               <?php echo plugin_lang_get ( 'config_page_eta_name' ); ?>
            </div>
            <div class="gridcol-1 category_name_field">
               <?php echo plugin_lang_get ( 'config_page_eta_value' ); ?>
            </div>
            <div class="gridcol-4 category_name_field">
               <?php echo plugin_lang_get ( 'config_page_eta_unit' ); ?>
            </div>
         </div>
         <?php
         /** eta management */

         $eta_enum_string = config_get ( 'eta_enum_string' );
         $eta_enum_values = MantisEnum::getValues ( $eta_enum_string );
         $row_count = count ( $eta_enum_values );
         $index = 1;
         foreach ( $eta_enum_values as $eta_enum_value )
         {
            ?>
            <div class="row">
               <div class="gridcol-1 category_name_field">
                  <?php echo string_display_line ( get_enum_element ( 'eta', $eta_enum_value ) ); ?>
               </div>
               <div class="gridcol-1 category_value_field-<?php echo $index; ?>">
                  <label>
                     <input type="text" name="eta_value[]"
                            value="<?php echo $roadmap_db->get_eta_row_by_key ( $eta_enum_value )[ 2 ] ?>"/>
                  </label>
               </div>
               <div class="gridcol-4 category_value_field-<?php echo $index; ?>">
                  <?php echo $roadmap_db->get_eta_unit () ?>
               </div>
            </div>
            <?php
            $index = ( $index + 1 ) % 2;
         }
      }
      $road_profiles = $roadmap_db->get_roadmap_profiles ();
      $profile_count = count ( $road_profiles );
      ?>
      <?php echo get_chapter_headrow ( 'config_page_roadmap_profile_management' )->saveHTML (); ?>
      <div class="row">
         <div class="gridcol-1 category_name_field">
            <?php echo plugin_lang_get ( 'config_page_profile_name' ); ?>
         </div>

         <div class="gridcol-1 category_value_field-0">
            <label for="profile_name"></label>
            <input type="text" id="profile_name" name="profile_name" size="15" maxlength="128" value=""/>
         </div>

         <div class="gridcol-1 category_value_field-0">
            <label>
               <select name="profile_status[]" multiple="multiple">
                  <?php print_enum_string_option_list ( 'status' ); ?>
               </select>
            </label>
         </div>

         <div class="gridcol-1 category_value_field-0">
            <label>
               <input class="color {pickerFace:4,pickerClosable:true}" type="text" name="new_profile_color"
                      value=""/>
            </label>
         </div>

         <div class="gridcol-1 category_value_field-0">
            <label for="profile_priority"></label>
            <input type="text" id="profile_priority" name="profile_priority" size="5" maxlength="128" value=""/>
         </div>

         <div class="gridcol-1 category_value_field-0">
            <label>
               <input type="submit" name="add_profile" class="button"
                      value="<?php echo plugin_lang_get ( 'config_page_add_profile' ); ?>"/>
            </label>
         </div>
      </div>
      <?php
      $profiles = $roadmap_db->get_roadmap_profiles ();
      if ( empty( $profiles ) == false )
      {
         ?>
         <div id="profile_container">
            <?php echo get_chapter_headrow ( 'config_page_profile_list' )->saveHTML (); ?>
            <div class="row">
               <div class="gridcol-1 category_name_field">
                  <?php echo plugin_lang_get ( 'config_page_profile_name' ); ?>
               </div>
               <div class="gridcol-1 category_name_field">
                  <?php echo plugin_lang_get ( 'config_page_profile_status' ); ?>
               </div>
               <div class="gridcol-1 category_name_field">
                  <?php echo plugin_lang_get ( 'config_page_profile_color' ); ?>
               </div>
               <div class="gridcol-1 category_name_field">
                  <?php echo plugin_lang_get ( 'config_page_profile_prio' );
                  print_priority_description_field () ?>
               </div>
               <div class="gridcol-2 category_name_field">
                  <?php echo plugin_lang_get ( 'config_page_profile_action' ); ?>
               </div>
            </div>
            <?php
            $index = 1;
            foreach ( $profiles as $profile )
            {
               $profile_id = $profile[ 0 ];
               $profile_name = $profile[ 1 ];
               $db_profile_color = $profile[ 2 ];
               $db_profile_status = $profile[ 3 ];
               $db_profile_priority = $profile[ 4 ];
               $profile_status_array = explode ( ';', $db_profile_status );

               $status_string = '';
               $counter = count ( $profile_status_array );
               for ( $status_index = 0; $status_index < $counter; $status_index++ )
               {
                  $profile_status = $profile_status_array[ $status_index ];
                  $status_string .= string_display_line ( get_enum_element ( 'status', $profile_status ) );
                  if ( $status_index < ( $counter - 1 ) )
                  {
                     $status_string .= ',&nbsp;';
                  }
               }
               ?>
               <div class="row" id="profile_row">
                  <div class="gridcol-1 category_value_field-<?php echo $index; ?>">
                     <?php echo string_display_line ( $profile_name ); ?>
                  </div>
                  <div class="gridcol-1 category_value_field-<?php echo $index; ?>">
                     <?php echo $status_string; ?>
                  </div>

                  <div class="gridcol-1 category_value_field-<?php echo $index; ?>">
                     <label>
                        <input class="color {pickerFace:4,pickerClosable:true}" type="text" name="profile_color"
                               value="#<?php echo $db_profile_color; ?>"/>
                     </label>
                  </div>

                  <div class="gridcol-1 category_value_field-<?php echo $index; ?>">
                     <?php echo $db_profile_priority; ?>
                  </div>

                  <div class="gridcol-2 category_value_field-<?php echo $index; ?>">
                     <a style="text-decoration: none;"
                        href="<?php echo plugin_page ( 'config_delete_profile' ); ?>&amp;profile_id=<?php echo $profile_id; ?>">
                        <input type="button"
                               value="<?php echo plugin_lang_get ( 'config_page_delete_profile' ); ?>"/>
                     </a>
                  </div>
               </div>
               <?php
               $index = ( $index + 1 ) % 2;
            }
            ?>
         </div>
         <?php
      }
      $initial_row_count = ( count ( $profiles ) );
      $status_enum_string = config_get ( 'status_enum_string' );
      $status_enum_values = MantisEnum::getValues ( $status_enum_string );
      $status_enum_strings = array ();
      foreach ( $status_enum_values as $status_enum_value )
      {
         array_push ( $status_enum_strings, get_enum_element ( 'status', $status_enum_value ) );
      }
      $i = json_encode ( $status_enum_strings );
      echo '<script type="text/javascript">var status_array =' . $i . ';</script>';
      ?>
      <div class="row grid_center">
         <div class="gridcol-6 ">
            <input type="submit" name="config_change" class="button"
                   value="<?php echo lang_get ( 'update_prefs_button' ); ?>"/>&nbsp;
            <input type="submit" name="config_reset" class="button"
                   value="<?php echo lang_get ( 'reset_prefs_button' ); ?>"/>
         </div>
      </div>
   </div>
<?php
echo '</form>';
html_page_bottom1 ();

function get_chapter_headrow ( $lang_string )
{
   $dom = new DOMDocument();

   $row_element = $dom->createElement ( 'div' );
   $row_attribute = $dom->createAttribute ( 'class' );
   $row_attribute->value = 'row';
   $row_element->appendChild ( $row_attribute );
   $col_element = $dom->createElement ( 'div', plugin_lang_get ( $lang_string ) );
   $col_attribute = $dom->createAttribute ( 'class' );
   $col_attribute->value = 'gridcol-6 title_row';
   $col_element->appendChild ( $col_attribute );
   $row_element->appendChild ( $col_element );

   $dom->appendChild ( $row_element );

   return $dom;
}

function print_priority_description_field ()
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
