<?php
require_once ( __DIR__ . '/../core/rProApi.php' );
require_once ( __DIR__ . '/../core/rHtmlApi.php' );
require_once ( __DIR__ . '/../core/roadmapManager.php' );
require_once ( __DIR__ . '/../core/roadmap.php' );
require_once ( __DIR__ . '/../core/rProfile.php' );

# initialize profile color
$getProfileId = 0;
$profileIdIsSet = false;
$profileColor = 'FFFFFF';
if ( isset( $_GET[ 'profile_id' ] ) )
{
   $getProfileId = $_GET[ 'profile_id' ];
   $profile = new rProfile( $getProfileId );
   $profileColor = $profile->getProfileColor ();
   $profileIdIsSet = true;
}

# print page top
html_page_top1 ( plugin_lang_get ( 'menu_title' ) );
rHtmlApi::htmlInitializeRessources ( $profileColor );
html_page_top2 ();

# print whiteboard menu bar
rHtmlApi::htmlPluginTriggerWhiteboardMenu ();

# print profile menu bar
rHtmlApi::printProfileSwitcher ();

# print group->profile menu bar
if ( isset( $_GET[ 'group_id' ] ) )
{
   $groupId = $_GET[ 'group_id' ];
   rHtmlApi::htmlGroupProfileSwitcher ( $groupId );
}

# print page content
if ( $profileIdIsSet )
{
   echo '<div align="center"><hr size="1" width="100%" /><div class="table">';
   processTable ( $getProfileId );
   echo '</div></div>';
}

# print page bottom
html_page_bottom ();

/**
 * generates and prints page content
 *
 * @param $profileId
 */
function processTable ( $profileId )
{
   $getVersionId = $_GET[ 'version_id' ];
   $getProjectId = $_GET[ 'project_id' ];

   $roadmapManager = new roadmapManager( $getVersionId, $getProjectId );
   $projectIds = $roadmapManager->getProjectIds ();
   $versions = $roadmapManager->getVersions ();

   # initialize directory
   rHtmlApi::htmlPluginDirectory ();

   # print content title
   rHtmlApi::htmlPluginContentTitle ();

   # iterate through projects
   foreach ( $projectIds as $projectId )
   {
      # skip if user has no access to project
      $userAccessLevel = user_get_access_level ( auth_get_current_user_id (), $projectId );
      $userHasProjectLevel = access_has_project_level ( $userAccessLevel, $projectId );
      if ( !$userHasProjectLevel )
      {
         continue;
      }

      # no specific version selected - get all versions for selected project which are not released
      if ( $getVersionId == null )
      {
         $versions = array_reverse ( version_get_all_rows ( $projectId, false ) );
      }

      # iterate through versions
      $projectTitlePrinted = false;
      foreach ( $versions as $version )
      {
         $bugIds = rProApi::dbGetBugIdsByProjectAndTargetVersion ( $projectId, $version[ 'version' ] );
         if ( count ( $bugIds ) > 0 )
         {
            # define and print project title
            if ( !$projectTitlePrinted )
            {
               # print project title
               rHtmlApi::htmlPluginProjectTitle ( $profileId, $projectId );
               # add project title to directory
               rHtmlApi::htmlPluginAddDirectoryProjectEntry ( $projectId );
               $projectTitlePrinted = true;
            }
            #roadmap object
            $getGroupId = $_GET[ 'group_id' ];
            $roadmap = new roadmap( $bugIds, $profileId, $getGroupId, $version[ 'id' ] );
            # add version to directory
            rHtmlApi::htmlPluginAddDirectoryVersionEntry ( $projectId, $version[ 'id' ], $version[ 'version' ] );
            # define and print title
            $releaseTitleString = rProApi::getReleasedTitleString ( $profileId, $getGroupId, $projectId, $version );
            rHtmlApi::printWrapperInHTML ( $releaseTitleString );
            # define and print realease date
            rHtmlApi::printWrapperInHTML ( rProApi::getReleasedDateString ( $version ) );
            # print version description
            rHtmlApi::printWrapperInHTML ( rProApi::getDescription ( $version ) );
            # print version progress bar
            rHtmlApi::printVersionProgress ( $roadmap, $projectId );
            # print bug list
            if ( $profileId == -1 )
            {
               $doneBugIds = rProApi::getDoneIssueIdsForAllProfiles ( $bugIds, $getGroupId );
               $doingBugIds = array_diff ( $bugIds, $doneBugIds );
               rHtmlApi::printBugList ( $doingBugIds );
               rHtmlApi::printBugList ( $doneBugIds, true );
            }
            else
            {
               rHtmlApi::printBugList ( $roadmap->getDoingBugIds () );
               rHtmlApi::printBugList ( $roadmap->getDoneBugIds (), true );
            }
            # print text progress
            ( $profileId >= 0 ) ? rHtmlApi::printVersionProgressAsText ( $roadmap ) : null;
            # print spacer
            rHtmlApi::htmlPluginSpacer ();
         }
      }
   }
}