<?php
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProApi.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rHtmlApi.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'roadmapManager.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'roadmap.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProfile.php' );

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
   $tmpVersions = $roadmapManager->getVersions ();

   # initialize directory
   rHtmlApi::htmlPluginDirectory ();

   # print content title
   rHtmlApi::htmlPluginContentTitle ();

   if ( $_GET[ 'sort' ] == 'vp' )
   {
      $vPVersions = rProApi::getVPVersions ( $projectIds, $getVersionId );
      foreach ( $vPVersions as $version )
      {
         $versionTitlePrinted = false;
         $vPProjects = rProApi::getVPProjects ( $version );
         foreach ( $vPProjects as $projectId )
         {
            $bugIds = rProApi::dbGetBugIdsByProjectAndTargetVersion ( $projectId, $version[ 'version' ] );
            if ( count ( $bugIds ) > 0 )
            {
               # define and print version title
               if ( !$versionTitlePrinted )
               {
                  # print version title
                  rHtmlApi::htmlPluginVersionTitle ( $version, $profileId );
                  # add version title to directory
                  rHtmlApi::htmlPluginAddDirectoryVersionEntry ( $version );
                  $versionTitlePrinted = true;
               }
               rProApi::goRoadmap( $profileId, $projectId, $version, $bugIds );
            }
         }
      }
   }
   else
   {
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
            $tmpVersions = array_reverse ( version_get_all_rows ( $projectId, false ) );
         }

         # iterate through versions
         $versionTitlePrinted = false;
         foreach ( $tmpVersions as $version )
         {
            $bugIds = rProApi::dbGetBugIdsByProjectAndTargetVersion ( $projectId, $version[ 'version' ] );
            if ( count ( $bugIds ) > 0 )
            {
               # define and print project title
               if ( !$versionTitlePrinted )
               {
                  # print project title
                  rHtmlApi::htmlPluginProjectTitle ( $profileId, $projectId );
                  # add project title to directory
                  rHtmlApi::htmlPluginAddDirectoryProjectEntry ( $projectId );
                  $versionTitlePrinted = true;
               }
               rProApi::goRoadmap( $profileId, $projectId, $version, $bugIds );
            }
         }
      }
   }

   if ( $profileId == -1 )
   {
      rHtmlApi::htmlInfoFooter ();
   }
}