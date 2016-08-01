<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_html_api.php' );
require_once ( __DIR__ . '/../core/roadmap_db.php' );
require_once ( __DIR__ . '/../core/roadmap_constant_api.php' );

$roadmapDb = new roadmap_db();
processPage ();

function processPage ()
{
   global $roadmapDb;
   $defaultProfileColor = 'FFFFFF';
   if ( isset( $_GET[ 'profile_id' ] ) )
   {
      $getProfileId = $_GET[ 'profile_id' ];
      $roadmapProfile = $roadmapDb->dbGetRoadmapProfile ( $getProfileId );
      $defaultProfileColor = $roadmapProfile[ 2 ];
   }

   html_page_top1 ( plugin_lang_get ( 'menu_title' ) );
   echo '<link rel="stylesheet" href="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro.css.php?profile_color=' . $defaultProfileColor . '"/>';
   echo '<script type="text/javascript" src="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro.js"></script>';
   html_page_top2 ();
   if ( plugin_is_installed ( 'WhiteboardMenu' ) &&
      file_exists ( config_get_global ( 'plugin_path' ) . 'WhiteboardMenu' )
   )
   {
      require_once __DIR__ . '/../../WhiteboardMenu/core/whiteboard_print_api.php';
      whiteboard_print_api::printWhiteboardMenu ();
   }

   /** print profile menu bar */
   roadmap_html_api::printProfileSwitcher ();

   if ( isset( $_GET[ 'profile_id' ] ) )
   {
      $getProfileId = $_GET[ 'profile_id' ];
      echo '<div align="center">';
      echo '<hr size="1" width="100%" />';
      echo '<div class="table">';
      processTable ( $getProfileId );
      echo '</div>';
      echo '</div>';
   }

   if ( true )
   {
//      print_successful_redirect( 'plugin.php?page=RoadmapPro/roadmap_page' );
   }

   html_page_bottom ();
}

function processTable ( $profileId )
{
   global $roadmapDb;
   $getVersionId = $_GET[ 'version_id' ];
   $getProjectId = $_GET[ 'project_id' ];

   $projectIds = roadmap_pro_api::prepareProjectIds ();

   /** specific project selected */
   if ( $getProjectId != null )
   {
      $projectIds = array ();
      array_push ( $projectIds, $getProjectId );
   }

   /** iterate through projects */
   foreach ( $projectIds as $projectId )
   {
      $projectSeperator = false;
      $userAccessLevel = user_get_access_level ( auth_get_current_user_id (), $projectId );
      $userHasProjectLevel = access_has_project_level ( $userAccessLevel, $projectId );
      /** skip if user has no access to project */
      if ( $userHasProjectLevel == false )
      {
         continue;
      }

      $printedProjectTitle = false;
      $projectName = string_display ( project_get_name ( $projectId ) );
      $versions = array_reverse ( version_get_all_rows ( $projectId ) );

      /** specific version selected */
      if ( $getVersionId != null )
      {
         $version = array ();
         $version[ 'id' ] = $getVersionId;
         $version[ 'version' ] = version_get_field ( $getVersionId, 'version' );
         $version[ 'date_order' ] = version_get_field ( $getVersionId, 'date_order' );
         $version[ 'released' ] = version_get_field ( $getVersionId, 'released' );
         $version[ 'description' ] = version_get_field ( $getVersionId, 'description' );

         $versions = array ();
         array_push ( $versions, $version );
      }

      /** iterate through versions */
      $versionCount = count ( $versions );
      for ( $index = 0; $index < $versionCount; $index++ )
      {
         $version = $versions[ $index ];
         $versionId = $version[ 'id' ];
         $versionName = $version[ 'version' ];
         $versionDate = $version[ 'date_order' ];
         $versionReleased = $version[ 'released' ];
         $versionDescription = $version[ 'description' ];

         /** skip released versions */
         if ( $versionReleased == 1 )
         {
            continue;
         }

         $versionReleaseDate = string_display_line ( date ( config_get ( 'short_date_format' ), $versionDate ) );

         $bugIds = $roadmapDb->dbGetBugIdsByProjectAndVersion ( $projectId, $versionName );
         $overallBugAmount = count ( $bugIds );

         if ( $overallBugAmount > 0 )
         {
            $useEta = roadmap_pro_api::checkEtaIsSet ( $bugIds );
            $doneEta = 0;
            $profileHashMap = array ();
            /** define and print project title */
            if ( $printedProjectTitle == false )
            {
               roadmap_html_api::htmlPluginProjectTitle ( $profileId, $projectName );
               $printedProjectTitle = true;
            }
            /** define and print release title */
            $releaseTitle = '<a href="' . plugin_page ( 'roadmap_page' )
               . '&amp;profile_id=' . $profileId . '&amp;project_id=' . $projectId . '">'
               . string_display_line ( $projectName ) . '</a>&nbsp;-'
               . '&nbsp;<a href="' . plugin_page ( 'roadmap_page' )
               . '&amp;profile_id=' . $profileId . '&amp;version_id=' . $versionId . '">'
               . string_display_line ( $versionName ) . '</a>';

            $releaseTitleString = $releaseTitle . '&nbsp;(' . lang_get ( 'scheduled_release' ) . '&nbsp;'
               . $versionReleaseDate . ')&nbsp;&nbsp;[&nbsp;<a href="view_all_set.php?type=1&amp;temporary=y&amp;'
               . FILTER_PROPERTY_PROJECT_ID . '=' . $projectId . '&amp;'
               . filter_encode_field_and_value ( FILTER_PROPERTY_TARGET_VERSION, $versionName ) . '">'
               . lang_get ( 'view_bugs_link' ) . '</a>&nbsp;]';

            roadmap_html_api::printWrapperInHTML ( $releaseTitleString );
            /** print version description */
            roadmap_html_api::printWrapperInHTML ( $versionDescription );

            if ( $profileId == -1 )
            {
               $scaledData = roadmap_pro_api::calcScaledData ( $bugIds, $useEta, $overallBugAmount );
               $profileHashMap = $scaledData[ 0 ];
               $progressInPercent = $scaledData[ 1 ];
            }
            else
            {
               $singleData = roadmap_pro_api::calcSingleData ( $bugIds, $profileId, $useEta, $overallBugAmount );
               $doneEta = $singleData[ 0 ];
               $progressInPercent = $singleData[ 1 ];
            }

            /** print version progress bar */
            roadmap_html_api::printVersionProgress ( $bugIds, $profileId, $progressInPercent, $profileHashMap, $useEta, $doneEta );
            /** print bug list */
            roadmap_html_api::printBugList ( $bugIds, $profileId );
            /** print text progress */
            if ( $profileId >= 0 )
            {
               $doneBugAmount = roadmap_pro_api::getDoneBugAmount ( $bugIds, $profileId );
               roadmap_html_api::printVersionProgressAsText ( $overallBugAmount, $doneBugAmount, $progressInPercent, $useEta );
            }
            /** print spacer */
            roadmap_html_api::htmlPluginSpacer ();
            $projectSeperator = true;
         }
      }
      /** print separator */
      if ( $projectSeperator == true )
      {
         roadmap_html_api::htmlPluginSeparator ();
      }
   }
}