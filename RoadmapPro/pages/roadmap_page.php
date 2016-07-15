<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_db.php' );
require_once ( __DIR__ . '/../core/roadmap_constant_api.php' );

process_page ();

function process_page ()
{
   $roadmap_db = new roadmap_db();
   $roadmap_profile_id = $_GET[ 'profile_id' ];
   $profile_color = 'FFFFFF';
   if ( is_null ( $roadmap_profile_id ) == false )
   {
      $roadmap_profile = $roadmap_db->get_roadmap_profile ( $roadmap_profile_id );
      $profile_color = $roadmap_profile[ 2 ];
   }

   html_page_top1 ( plugin_lang_get ( 'menu_title' ) );
   echo '<link rel="stylesheet" href="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro.css.php?profile_color=' . $profile_color . '"/>' . "\n";
   html_page_top2 ();

   if ( plugin_is_installed ( 'WhiteboardMenu' ) &&
      file_exists ( config_get_global ( 'plugin_path' ) . 'WhiteboardMenu' )
   )
   {
      require_once __DIR__ . '/../../WhiteboardMenu/core/whiteboard_print_api.php';
      whiteboard_print_api::printWhiteboardMenu ();
   }


   /** print profile menu bar */
   if ( is_null ( $roadmap_db->get_roadmap_profiles () ) == false )
   {
      print_profile_switcher ();
   }

   if ( isset( $_GET[ 'profile_id' ] ) )
   {
      $profile_id = $_GET[ 'profile_id' ];
      echo '<div align="center">';
      echo '<hr size="1" width="100%" />';
      echo '<div class="table">';
      process_table ( $profile_id );
      echo '</div>';
      echo '</div>';
   }

   html_page_bottom ();
}

function process_table ( $profile_id )
{
   $get_version_id = $_GET[ 'version_id' ];
   $get_project_id = $_GET[ 'project_id' ];

   $project_ids = roadmap_pro_api::prepare_project_ids ();

   /** specific project selected */
   if ( $get_project_id != null )
   {
      $project_ids = array ();
      array_push ( $project_ids, $get_project_id );
   }

   /** iterate through projects */
   foreach ( $project_ids as $project_id )
   {
      $project_seperator = false;
      $user_access_lecel = user_get_access_level ( auth_get_current_user_id (), $project_id );
      $has_project_level = access_has_project_level ( $user_access_lecel, $project_id );
      /** skip if user has no access to project */
      if ( $has_project_level == false )
      {
         continue;
      }

      $printed_project_title = false;
      $project_name = project_get_name ( $project_id );
      $versions = array_reverse ( version_get_all_rows ( $project_id ) );

      /** specific version selected */
      if ( $get_version_id != null )
      {
         $version = array ();
         $version[ 'id' ] = $get_version_id;
         $version[ 'version' ] = version_get_field ( $get_version_id, 'version' );
         $version[ 'date_order' ] = version_get_field ( $get_version_id, 'date_order' );
         $version[ 'released' ] = version_get_field ( $get_version_id, 'released' );
         $version[ 'description' ] = version_get_field ( $get_version_id, 'description' );

         $versions = array ();
         array_push ( $versions, $version );
      }

      /** iterate through versions */
      $version_count = count ( $versions );
      for ( $v_index = 0; $v_index < $version_count; $v_index++ )
      {
         $version = $versions[ $v_index ];
         $version_id = $version[ 'id' ];
         $version_name = $version[ 'version' ];
         $version_date = $version[ 'date_order' ];
         $version_released = $version[ 'released' ];
         $version_description = $version[ 'description' ];

         /** skip released versions */
         if ( $version_released == 1 )
         {
            continue;
         }

         $release_date = string_display_line ( date ( config_get ( 'short_date_format' ), $version_date ) );

         $roadmap_db = new roadmap_db();
         $bug_ids = $roadmap_db->get_bug_ids_by_project_and_version ( $project_id, $version_name );
         $overall_bug_amount = count ( $bug_ids );

         if ( $overall_bug_amount > 0 )
         {
            $use_eta = roadmap_pro_api::check_eta_is_set ( $bug_ids );
            $done_eta = 0;
            $profile_progress_values = array ();
            /** define and print project title */
            if ( $printed_project_title == false )
            {
               $project_title = '<span class="pagetitle">' . string_display ( $project_name ) . '&nbsp;-&nbsp;'
                  . lang_get ( 'roadmap' ) . '</span>';
               print_html_wrapper ( $project_title );
               $printed_project_title = true;
            }
            /** define and print release title */
            $release_title = '<a href="' . plugin_page ( 'roadmap_page' )
               . '&amp;profile_id=' . $profile_id . '&amp;project_id=' . $project_id . '">'
               . string_display_line ( $project_name ) . '</a>&nbsp;-'
               . '&nbsp;<a href="' . plugin_page ( 'roadmap_page' )
               . '&amp;profile_id=' . $profile_id . '&amp;version_id=' . $version_id . '">'
               . string_display_line ( $version_name ) . '</a>';

            $release_title_string = $release_title . '&nbsp;(' . lang_get ( 'scheduled_release' ) . '&nbsp;'
               . $release_date . ')&nbsp;&nbsp;[&nbsp;<a href="view_all_set.php?type=1&amp;temporary=y&amp;'
               . FILTER_PROPERTY_PROJECT_ID . '=' . $project_id . '&amp;'
               . filter_encode_field_and_value ( FILTER_PROPERTY_TARGET_VERSION, $version_name ) . '">'
               . lang_get ( 'view_bugs_link' ) . '</a>&nbsp;]';

            print_html_wrapper ( $release_title_string );
            /** print version description */
            print_html_wrapper ( $version_description );


            $done_bug_amount = 0;
            if ( $profile_id == -1 )
            {
               $scaled_data = calc_scaled_data ( $bug_ids, $use_eta, $overall_bug_amount );
               $profile_progress_values = $scaled_data[ 0 ];
               $progress_percent = $scaled_data[ 1 ];
            }
            else
            {
               $single_data = calc_single_data ( $bug_ids, $profile_id, $use_eta, $overall_bug_amount );
               $done_eta = $single_data[ 0 ];
               $progress_percent = $single_data[ 1 ];
            }

            /** print version progress bar */
            print_version_progress ( $bug_ids, $profile_id, $progress_percent, $profile_progress_values, $use_eta, $done_eta );
            /** print bug list */
            print_bug_list ( $bug_ids, $profile_id );
            /** print text progress */
            if ( $profile_id >= 0 )
            {
               print_text_version_progress ( $overall_bug_amount, $done_bug_amount, $progress_percent, $use_eta );
            }
            /** print spacer */
            echo '<div class="spacer"></div>';
            $project_seperator = true;
         }
      }
      /** print separator */
      if ( $project_seperator == true )
      {
         echo '<hr class="project-separator" />';
      }
   }
}

function calc_scaled_data ( $bug_ids, $use_eta, $overall_bug_amount )
{
   $roadmap_db = new roadmap_db();

   $profile_progress_values = array ();
   $roadmap_profiles = $roadmap_db->get_roadmap_profiles ();
   $profile_count = count ( $roadmap_profiles );
   $sum_progress_done_bug_amount = 0;
   $sum_progress_done_percent = 0;
   $sum_progress_done_eta = 0;
   $sum_progress_done_eta_percent = 0;
   $full_eta = ( roadmap_pro_api::get_full_eta ( $bug_ids ) ) * $profile_count;
   foreach ( $roadmap_profiles as $roadmap_profile )
   {
      $temp_profile_id = $roadmap_profile[ 0 ];
      $temp_done_bug_amount = roadmap_pro_api::get_done_bug_amount ( $bug_ids, $temp_profile_id );
      $sum_progress_done_bug_amount += $temp_done_bug_amount;
      if ( $use_eta )
      {
         /** calculate eta for profile */
         $done_eta = 0;
         $done_bug_ids = roadmap_pro_api::get_done_bug_ids ( $bug_ids, $temp_profile_id );
         foreach ( $done_bug_ids as $done_bug_id )
         {
            $done_eta += roadmap_pro_api::get_single_eta ( $done_bug_id );
         }
         $done_eta_percent = round ( ( ( $done_eta / $full_eta ) * 100 ), 2 );
         $sum_progress_done_eta += $done_eta;
         $sum_progress_done_eta_percent += $done_eta_percent;

         $profile_hash = $temp_profile_id . ';' . $sum_progress_done_eta_percent . ';' . $done_eta_percent;
      }
      else
      {
         $temp_version_progress = round ( ( $temp_done_bug_amount / $overall_bug_amount ), 4 );
         $progess_done_percent = round ( ( $temp_version_progress * 100 / $profile_count ), 1 );
         $sum_progress_done_percent += $progess_done_percent;
         $profile_hash = $temp_profile_id . ';' . $sum_progress_done_percent . ';' . $progess_done_percent;
      }

      array_push ( $profile_progress_values, $profile_hash );
   }

   /** whole progress of the version */
   if ( $use_eta )
   {
      $whole_progress = round ( ( $sum_progress_done_eta / $full_eta ), 2 );
   }
   else
   {
      $whole_progress = round ( ( ( $sum_progress_done_bug_amount / $profile_count ) / $overall_bug_amount ), 4 );
   }
   $progress_percent = round ( ( $whole_progress * 100 ), 2 );

   $result = [ 0 => $profile_progress_values, 1 => $progress_percent ];

   return $result;
}

function calc_single_data ( $bug_ids, $profile_id, $use_eta, $overall_bug_amount )
{
   $full_eta = ( roadmap_pro_api::get_full_eta ( $bug_ids ) );
   $done_eta = 0;
   if ( $use_eta )
   {
      $done_bug_ids = roadmap_pro_api::get_done_bug_ids ( $bug_ids, $profile_id );
      foreach ( $done_bug_ids as $done_bug_id )
      {
         $done_eta += roadmap_pro_api::get_single_eta ( $done_bug_id );
      }

      $progress_percent = 0;
      if ( $full_eta > 0 )
      {
         $progress_percent = round ( ( ( $done_eta / $full_eta ) * 100 ), 2 );
      }
   }
   else
   {
      $done_bug_amount = roadmap_pro_api::get_done_bug_amount ( $bug_ids, $profile_id );
      $progress = round ( ( $done_bug_amount / $overall_bug_amount ), 4 );
      $progress_percent = round ( ( $progress * 100 ), 2 );
   }

   $result = [ 0 => $done_eta, 1 => $progress_percent ];

   return $result;
}

function print_html_wrapper ( $content )
{
   echo '<div class="tr">' . PHP_EOL;
   echo '<div class="td">';
   echo $content;
   echo '</div>' . PHP_EOL;
   echo '</div>' . PHP_EOL;
}

function print_profile_switcher ()
{
   $get_version_id = $_GET[ 'version_id' ];
   $get_project_id = $_GET[ 'project_id' ];

   $roadmap_db = new roadmap_db();
   $roadmap_profiles = $roadmap_db->get_roadmap_profiles ();

   echo '<div class="table_center">' . PHP_EOL;
   echo '<div class="tr">' . PHP_EOL;
   /** print roadmap_profile-links */
   foreach ( $roadmap_profiles as $roadmap_profile )
   {
      $profile_id = $roadmap_profile[ 0 ];
      $profile_name = $roadmap_profile[ 1 ];

      echo '<div class="td">';
      echo '[ <a href="' . plugin_page ( 'roadmap_page' ) . '&amp;profile_id=' . $profile_id;
      if ( $get_version_id != null )
      {
         echo '&amp;version_id=' . $get_version_id;
      }
      if ( $get_project_id != null )
      {
         echo '&amp;project_id=' . $get_project_id;
      }
      echo '">';
      echo string_display ( $profile_name );
      echo '</a> ]';
      echo '</div>' . PHP_EOL;
   }
   /** show whole progress, when there is more then one different profile */
   if ( count ( $roadmap_profiles ) > 1 )
   {
      echo '<div class="td">';
      echo '[ <a href="' . plugin_page ( 'roadmap_page' ) . '&amp;profile_id=-1">';
      echo plugin_lang_get ( 'roadmap_page_whole_progress' );
      echo '</a> ]';
      echo '</div>' . PHP_EOL;
   }

   echo '</div>' . PHP_EOL;
   echo '</div>' . PHP_EOL;
}

function print_version_progress ( $bug_ids, $profile_id, $progress_percent, $profile_progress_values, $use_eta, $done_eta )
{
   $roadmap_db = new roadmap_db();

   echo '<div class="tr">' . PHP_EOL;
   echo '<div class="td">';
   echo '<div class="progress9000">';
   if ( $use_eta && config_get ( 'enable_eta' ) )
   {
      if ( $profile_id == -1 )
      {
         print_scaled_progressbar ( $profile_progress_values, $progress_percent, $bug_ids, true );
      }
      else
      {
         $full_eta = roadmap_pro_api::get_full_eta ( $bug_ids );
         $progress_string = $done_eta . '&nbsp;' . lang_get ( 'from' ) . '&nbsp;' . $full_eta . '&nbsp;' . $roadmap_db->get_eta_unit ();
         print_single_progressbar ( $progress_percent, $progress_string );
      }
   }
   else
   {
      if ( $profile_id == -1 )
      {
         print_scaled_progressbar ( $profile_progress_values, $progress_percent, $bug_ids );
      }
      else
      {
         $bug_count = count ( $bug_ids );
         $progress_string = $progress_percent . '%&nbsp;' . lang_get ( 'from' ) . '&nbsp;' . $bug_count . '&nbsp;' . lang_get ( 'issues' );
         print_single_progressbar ( $progress_percent, $progress_string );
      }
   }
   echo '</div>';
   echo '</div>' . PHP_EOL;
   echo '</div>' . PHP_EOL;
}

function print_single_progressbar ( $progress, $progress_string )
{
   echo '<span class="bar" style="width: ' . $progress . '%; white-space: nowrap;">' . $progress_string . '</span>';
}

function print_scaled_progressbar ( $profile_hash_map, $progress_percent, $bug_ids, $use_eta = false )
{
   $roadmap_db = new roadmap_db();
   if ( empty( $profile_hash_map ) == false )
   {
      $profile_hash_map = array_reverse ( $profile_hash_map );
      foreach ( $profile_hash_map as $profile_hash )
      {
         /** extract profile data */
         $profile_hash = explode ( ';', $profile_hash );
         $hash_profile_id = $profile_hash[ 0 ];
         $hash_sum_progress = $profile_hash[ 1 ];
         $hash_progress = round ( $profile_hash[ 2 ], 1 );

         /** get profile color */
         $profile_db_row = $roadmap_db->get_roadmap_profile ( $hash_profile_id );
         $profile_color = '#' . $profile_db_row[ 2 ];

         echo '<span class="scaledbar" style="width: ' . $hash_sum_progress . '%; background: ' . $profile_color . '; border: solid 1px ' . $profile_color . '">' . $hash_progress . '%</span>';
      }
   }

   echo '</div>';
   echo '<div class="progress-suffix">';
   echo '&nbsp;(' . $progress_percent . '%';
   if ( $use_eta == true )
   {
      $full_eta = roadmap_pro_api::get_full_eta ( $bug_ids );
      echo '&nbsp;' . lang_get ( 'from' ) . '&nbsp;' . $full_eta . '&nbsp;' . $roadmap_db->get_eta_unit ();
   }
   else
   {
      $bug_count = count ( $bug_ids );
      echo '&nbsp;' . lang_get ( 'from' ) . '&nbsp;' . $bug_count . '&nbsp;' . lang_get ( 'issues' );
   }
   echo ')';
}

function print_bug_list ( $bug_ids, $profile_id )
{
   $bug_ids_detailed = roadmap_pro_api::calculate_bug_relationships ( $bug_ids );
   foreach ( $bug_ids_detailed as $bug )
   {
      $bug_id = $bug[ 'id' ];
      $user_id = bug_get_field ( $bug_id, 'handler_id' );
      $bug_eta = bug_get_field ( $bug_id, 'eta' );
      $bug_blocking_ids = $bug[ 'blocking_ids' ];
      $bug_blocked_ids = $bug[ 'blocked_ids' ];
      echo '<div class="tr">' . PHP_EOL;
      echo '<div class="td">';
      /** line through, if bug is done */
      if ( roadmap_pro_api::check_issue_is_done_by_id ( $bug_id, $profile_id ) )
      {
         echo '<span style="text-decoration: line-through;">';
      }
      echo print_bug_link ( $bug_id, bug_format_id ( $bug_id ) ) . ':&nbsp;';
      /** symbol when eta is set */
      if ( ( $bug_eta > 10 ) && config_get ( 'enable_eta' ) )
      {
         echo '<img src="' . ROADMAPPRO_PLUGIN_URL . 'files/clock.png' . '" alt="clock" height="12" width="12" />&nbsp;';
      }
      /** symbol when bug is blocking */
      if ( empty ( $bug_blocked_ids ) == false )
      {
         $blocked_id_string = lang_get ( 'blocks' ) . '&nbsp;';
         $blocked_id_count = count ( $bug_blocked_ids );
         for ( $i = 0; $i < $blocked_id_count; $i++ )
         {
            $blocked_id_string .= bug_format_id ( $bug_blocked_ids[ $i ] );
            if ( $i < ( $blocked_id_count - 1 ) )
            {
               $blocked_id_string .= ',&nbsp;';
            }
         }
         echo '<img src="' . ROADMAPPRO_PLUGIN_URL . 'files/sign_warning.png' . '" alt="' . $blocked_id_string . '" title="' . $blocked_id_string . '" height="12" width="12" />&nbsp;';
      }
      /** symbol when bug is blocked by */
      if ( empty ( $bug_blocking_ids ) == false )
      {
         $blocking_id_string = lang_get ( 'dependant_on' ) . '&nbsp;';
         $blocking_id_count = count ( $bug_blocking_ids );
         for ( $i = 0; $i < $blocking_id_count; $i++ )
         {
            $blocking_id_string .= bug_format_id ( $bug_blocking_ids[ $i ] );
            if ( $i < ( $blocking_id_count - 1 ) )
            {
               $blocking_id_string .= ',&nbsp;';
            }
         }
         echo '<img src="' . ROADMAPPRO_PLUGIN_URL . 'files/sign_stop.png' . '" alt="' . $blocking_id_string . '" title="' . $blocking_id_string . '" height="12" width="12" />&nbsp;';
      }
      echo string_display ( bug_get_field ( $bug_id, 'summary' ) );
      if ( $user_id > 0 )
      {
         echo '&nbsp;(<a href="' . config_get ( 'path' ) . '/view_user_page.php?id=' . $user_id . '">';
         echo user_get_name ( $user_id );
         echo '</a>' . ')';
      }

      echo '&nbsp;-&nbsp;'
         . string_display_line ( get_enum_element ( 'status', bug_get_field ( $bug_id, 'status' ) ) ) . '.';
      if ( roadmap_pro_api::check_issue_is_done_by_id ( $bug_id, $profile_id ) )
      {
         echo '</span>';
      }
      echo '</div>' . PHP_EOL;
      echo '</div>' . PHP_EOL;
   }
}

function print_text_version_progress ( $overall_bug_amount, $done_bug_amount, $progress_percent, $use_time_calculation )
{
   echo '<div class="tr">' . PHP_EOL;
   echo '<div class="td">';
   if ( $use_time_calculation && config_get ( 'enable_eta' ) )
   {
      echo sprintf ( plugin_lang_get ( 'roadmap_page_resolved_time' ), $done_bug_amount, $overall_bug_amount );
   }
   else
   {
      echo sprintf ( lang_get ( 'resolved_progress' ), $done_bug_amount, $overall_bug_amount, $progress_percent );
   }
   echo '</div>' . PHP_EOL;
   echo '</div>' . PHP_EOL;
}