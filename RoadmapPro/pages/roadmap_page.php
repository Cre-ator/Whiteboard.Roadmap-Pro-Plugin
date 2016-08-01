<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_html_api.php' );
require_once ( __DIR__ . '/../core/roadmap_db.php' );
require_once ( __DIR__ . '/../core/roadmap_data.php' );
require_once ( __DIR__ . '/../core/roadmap_constant_api.php' );
require_once ( __DIR__ . '/../core/roadmap_bugdata.php' );

$roadmapDb = new roadmap_db();
$defaultProfileColor = 'FFFFFF';
if ( isset( $_GET[ 'profile_id' ] ) )
{
   $getProfileId = $_GET[ 'profile_id' ];
   $roadmapProfile = $roadmapDb->dbGetRoadmapProfile ( $getProfileId );
   $defaultProfileColor = $roadmapProfile[ 2 ];
}

/** ################################################################################################################# */
/** print page top */
html_page_top1 ( plugin_lang_get ( 'menu_title' ) );
echo '<link rel="stylesheet" href="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro.css.php?profile_color=' . $defaultProfileColor . '"/>';
echo '<script type="text/javascript" src="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro.js"></script>';
html_page_top2 ();

/** print whiteboard menu bar */
roadmap_html_api::htmlPluginTriggerWhiteboardMenu ();

/** print profile menu bar */
roadmap_html_api::printProfileSwitcher ();

/** print page content */
if ( isset( $_GET[ 'profile_id' ] ) )
{
   $getProfileId = $_GET[ 'profile_id' ];
   echo '<div align="center"><hr size="1" width="100%" /><div class="table">';
   processTable ( $getProfileId );
   echo '</div></div>';
}

/** print page bottom */
html_page_bottom ();
/** ################################################################################################################# */

/**
 * generates and prints page content
 *
 * @param $profileId
 */
function processTable ( $profileId )
{
   global $roadmapDb;

   $getVersionId = $_GET[ 'version_id' ];
   $getProjectId = $_GET[ 'project_id' ];
   $roadmapData = new roadmap_data( $getVersionId, $getProjectId );
   $roadmapData->calcProjectVersionContent ();

   $projectIds = $roadmapData->getProjectIds ();
   $versions = $roadmapData->getVersions ();

   /** iterate through projects */
   foreach ( $projectIds as $projectId )
   {
      $projectSeperator = false;
      $printedProjectTitle = false;
      $userAccessLevel = user_get_access_level ( auth_get_current_user_id (), $projectId );
      $userHasProjectLevel = access_has_project_level ( $userAccessLevel, $projectId );

      /** skip if user has no access to project */
      if ( $userHasProjectLevel == false )
      {
         continue;
      }

      if ( $getVersionId == null )
      {
         $versions = array_reverse ( version_get_all_rows ( $projectId ) );
      }

      /** iterate through versions */
      $versionCount = count ( $versions );
      for ( $index = 0; $index < $versionCount; $index++ )
      {
         $version = $versions[ $index ];
         $versionName = $version[ 'version' ];
         $versionReleased = $version[ 'released' ];
         $versionDescription = $version[ 'description' ];

         /** skip released versions */
         if ( $versionReleased == 1 )
         {
            continue;
         }

         $bugIds = $roadmapDb->dbGetBugIdsByProjectAndVersion ( $projectId, $versionName );
         $overallBugAmount = count ( $bugIds );

         if ( $overallBugAmount > 0 )
         {
            $roadmapData = new roadmap_bugdata( $bugIds, $profileId );
            $useEta = $roadmapData->getEtaIsSet ();
            $doneEta = 0;
            $profileHashMap = array ();
            /** define and print project title */
            if ( $printedProjectTitle == false )
            {
               roadmap_html_api::htmlPluginProjectTitle ( $profileId, $projectId );
               $printedProjectTitle = true;
            }
            /** define and print release title */
            $releaseTitleString = roadmap_pro_api::getReleasedTitleString ( $profileId, $projectId, $version );
            roadmap_html_api::printWrapperInHTML ( $releaseTitleString );
            /** print version description */
            roadmap_html_api::printWrapperInHTML ( $versionDescription );

            if ( $profileId == -1 )
            {
               $scaledRoadmap = new roadmap_bugdata( $bugIds, $profileId );
               $scaledRoadmap->calcData ();
               $profileHashMap = $scaledRoadmap->getProfileProgressValueArray ();
               $progressInPercent = $scaledRoadmap->getProgressPercent ();
            }
            else
            {
               $singleRoadmap = new roadmap_bugdata( $bugIds, $profileId );
               $doneEta = $singleRoadmap->getDoneEta ();
               $progressInPercent = $singleRoadmap->getSingleProgressPercent ();
            }

            /** print version progress bar */
            roadmap_html_api::printVersionProgress ( $bugIds, $profileId, $progressInPercent, $profileHashMap, $useEta, $doneEta );
            /** print bug list */
            roadmap_html_api::printBugList ( $bugIds, $profileId );
            /** print text progress */
            if ( $profileId >= 0 )
            {
               $doneBugAmount = count ( $roadmapData->getDoneBugIds () );
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