<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_html_api.php' );
require_once ( __DIR__ . '/../core/roadmapManager.php' );
require_once ( __DIR__ . '/../core/roadmap_constant_api.php' );
require_once ( __DIR__ . '/../core/roadmap.php' );
require_once ( __DIR__ . '/../core/rProfile.php' );

$profileColor = 'FFFFFF';
if ( isset( $_GET[ 'profile_id' ] ) )
{
   $getProfileId = $_GET[ 'profile_id' ];
   $profile = new rProfile( $getProfileId );
   $profileColor = $profile->getProfileColor ();
}

# #################################################################################################################### #
# print page top
html_page_top1 ( plugin_lang_get ( 'menu_title' ) );
echo '<link rel="stylesheet" href="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro.css.php?profile_color=' . $profileColor . '"/>';
echo '<script type="text/javascript" src="' . ROADMAPPRO_PLUGIN_URL . 'files/roadmappro.js"></script>';
echo '<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>';
echo '<script type="text/javascript">';
echo 'backToTop();';
echo '</script>';
html_page_top2 ();

# print whiteboard menu bar
roadmap_html_api::htmlPluginTriggerWhiteboardMenu ();

# print profile menu bar
roadmap_html_api::printProfileSwitcher ();

# print group->profile menu bar
if ( isset( $_GET[ 'group_id' ] ) )
{
   $groupId = $_GET[ 'group_id' ];
   roadmap_html_api::htmlGroupProfileSwitcher ( $groupId );
}

# print page content
if ( isset( $_GET[ 'profile_id' ] ) )
{
   $getProfileId = $_GET[ 'profile_id' ];
   echo '<div align="center"><hr size="1" width="100%" /><div class="table">';
   processTable ( $getProfileId );
   echo '</div></div>';
}

# print page bottom
html_page_bottom ();
# #################################################################################################################### #

/**
 * generates and prints page content
 *
 * @param $profileId
 */
function processTable ( $profileId )
{
   $getVersionId = $_GET[ 'version_id' ];
   $getProjectId = $_GET[ 'project_id' ];
   $getGroupId = $_GET[ 'group_id' ];

   $roadmap = new roadmapManager( $getVersionId, $getProjectId );
   $roadmap->calcProjectVersionContent ();

   $projectIds = $roadmap->getProjectIds ();
   $versions = $roadmap->getVersions ();

   # initialize directoy
   roadmap_html_api::htmlPluginDirectory ();

   # iterate through projects
   foreach ( $projectIds as $projectId )
   {
      $userAccessLevel = user_get_access_level ( auth_get_current_user_id (), $projectId );
      $userHasProjectLevel = access_has_project_level ( $userAccessLevel, $projectId );

      # skip if user has no access to project
      if ( $userHasProjectLevel == false )
      {
         continue;
      }

      if ( $getVersionId == null )
      {
         $versions = array_reverse ( version_get_all_rows ( $projectId ) );
      }

      # iterate through versions
      $projectTitlePrinted = false;
      $versionCount = count ( $versions );
      for ( $index = 0; $index < $versionCount; $index++ )
      {
         $version = $versions[ $index ];
         $versionId = $version[ 'id' ];

         # skip released versions
         $versionReleased = $version[ 'released' ];
         if ( $versionReleased == 1 )
         {
            continue;
         }

         $versionName = $version[ 'version' ];
         $bugIds = roadmap_pro_api::dbGetBugIdsByProjectAndTargetVersion ( $projectId, $versionName );
         $overallBugAmount = count ( $bugIds );

         if ( $overallBugAmount > 0 )
         {
            $roadmap = new roadmap( $bugIds, $profileId, $getGroupId );
            $roadmap->setVersionId ( $versionId );
            # define and print project title
            if ( !$projectTitlePrinted )
            {
               # print project title
               roadmap_html_api::htmlPluginProjectTitle ( $profileId, $projectId );
               # add project title to directory
               roadmap_html_api::htmlPluginAddDirectoryProjectEntry ( $projectId );
               $projectTitlePrinted = true;
            }
            # add version to directory
            roadmap_html_api::htmlPluginAddDirectoryVersionEntry ( project_get_name ( $projectId ), $versionId, $versionName );
            # define and print release title
            $releaseTitleString = roadmap_pro_api::getReleasedTitleString ( $profileId, $getGroupId, $projectId, $version );
            roadmap_html_api::printWrapperInHTML ( $releaseTitleString );
            # print version description
            $versionDescription = $version[ 'description' ];
            roadmap_html_api::printWrapperInHTML ( $versionDescription );
            # print version progress bar
            roadmap_html_api::printVersionProgress ( $roadmap );
            # print bug list
            $doingBugIds = $roadmap->getDoingBugIds ();
            $doneBugIds = $roadmap->getDoneBugIds ();
            roadmap_html_api::printBugList ( $doingBugIds );
            roadmap_html_api::printBugList ( $doneBugIds, true );
            # print text progress
            if ( $profileId >= 0 )
            {
               roadmap_html_api::printVersionProgressAsText ( $roadmap );
            }
            # print spacer
            roadmap_html_api::htmlPluginSpacer ();
         }
      }
   }
}