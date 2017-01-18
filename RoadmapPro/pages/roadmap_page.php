<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProApi.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rHtmlApi.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'roadmapManager.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'roadmap.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProfile.php');

# initialize profile color
$getProfileId = 0;
$profileIdIsSet = FALSE;
$profileColor = 'FFFFFF';
if (isset($_GET['profile_id'])) {
  $getProfileId = $_GET['profile_id'];
  $profile = new rProfile($getProfileId);
  $profileColor = $profile->getProfileColor();
  $profileIdIsSet = TRUE;
}

# print page top
html_page_top1(plugin_lang_get('menu_title'));
rHtmlApi::htmlInitializeRessources($profileColor);
html_page_top2();

# print whiteboard menu bar
rHtmlApi::htmlPluginTriggerWhiteboardMenu();

# print profile menu bar
rHtmlApi::printProfileSwitcher();

# print group->profile menu bar
if (isset($_GET['group_id'])) {
  $getGroupId = $_GET['group_id'];
  rHtmlApi::htmlGroupProfileSwitcher($getGroupId);
}

# print page content
if ($profileIdIsSet) {
  echo '<div align="center"><hr size="1" width="100%" /><div class="table">';
  processTable($getProfileId);
  echo '</div></div>';
}

# print page bottom
html_page_bottom();

/**
 * generates and prints page content
 *
 * @param $profileId
 */
function processTable($profileId)
{
  $getProjectId = NULL;
  $getVersionId = NULL;
  $getGroupId = NULL;
  $getSort = NULL;
  if (isset($_GET['project_id'])) {
    $getProjectId = $_GET['project_id'];
  }
  if (isset($_GET['version_id'])) {
    $getVersionId = $_GET['version_id'];
  }
  if (isset($_GET['group_id'])) {
    $getGroupId = $_GET['group_id'];
  }
  if (isset($_GET['sort'])) {
    $getSort = $_GET['sort'];
  }

  $roadmapManager = new roadmapManager($getVersionId, $getProjectId);
  $projectIds = $roadmapManager->getProjectIds();
  $pVVersions = $roadmapManager->getVersions();

  # initialize directory
  rHtmlApi::htmlPluginDirectory();

  # print content title
  rHtmlApi::htmlPluginContentTitle();

  if ($getSort == 'vp') {
    $vPVersions = array();
    # no specific version selected - get all versions for selected project which are not released
    if ($getVersionId == NULL) {
      $vPVersions = rProApi::getVPVersions($projectIds, $getVersionId);
    } else {
      $versionArray = array();
      $versionArray['id'] = $getVersionId;
      $versionArray['project_id'] = version_get_field($getVersionId, 'project_id');
      $versionArray['version'] = version_get_field($getVersionId, 'version');
      $versionArray['description'] = version_get_field($getVersionId, 'description');
      $versionArray['released'] = version_get_field($getVersionId, 'released');
      $versionArray['obsolete'] = version_get_field($getVersionId, 'obsolete');
      $versionArray['date_order'] = version_get_field($getVersionId, 'date_order');
      array_push($vPVersions, $versionArray);
    }

    foreach ($vPVersions as $version) {
      $versionTitlePrinted = FALSE;
      $vPProjects = rProApi::getVPProjects($version);
      foreach ($vPProjects as $projectId) {
        # skip if user has no access to project
        $userAccessLevel = user_get_access_level(auth_get_current_user_id(), $projectId);
        $userHasProjectLevel = access_has_project_level($userAccessLevel, $projectId);
        if (!$userHasProjectLevel) {
          continue;
        }

        $bugIds = rProApi::dbGetBugIdsByProjectAndTargetVersion($projectId, $version['version']);
        if (count($bugIds) > 0) {
          # define and print version title
          if (!$versionTitlePrinted) {
            processProjectTitle($getSort, $version, $profileId);
            $versionTitlePrinted = TRUE;
          }
          processRoadmap($projectId, $profileId, $getGroupId, $version, $bugIds, $getSort);
        }
      }
    }
  } else {
    if ($getVersionId != NULL) {
      $tmpProjectIds = array();
      $bugIds = rProApi::dbGetBugIdsByTargetVersion(version_get_field($getVersionId, 'version'));
      foreach ($bugIds as $bugId) {
        array_push($tmpProjectIds, bug_get_field($bugId, 'project_id'));
      }

      $tmpProjectIds = array_unique($tmpProjectIds);
      $projectIds = $tmpProjectIds;
    }

    # iterate through projects
    foreach ($projectIds as $projectId) {
      # skip if user has no access to project
      $userAccessLevel = user_get_access_level(auth_get_current_user_id(), $projectId);
      $userHasProjectLevel = access_has_project_level($userAccessLevel, $projectId);
      if (!$userHasProjectLevel) {
        continue;
      }

      # no specific version selected - get all versions for selected project which are not released
      if ($getVersionId == NULL) {
        $pVVersions = array_reverse(version_get_all_rows($projectId, FALSE));
      }

      # iterate through versions
      $versionTitlePrinted = FALSE;
      foreach ($pVVersions as $version) {
        $bugIds = rProApi::dbGetBugIdsByProjectAndTargetVersion($projectId, $version['version']);
        if (count($bugIds) > 0) {
          # define and print project title
          if (!$versionTitlePrinted) {
            processProjectTitle($getSort, $profileId, $projectId);
            $versionTitlePrinted = TRUE;
          }
          processRoadmap($projectId, $profileId, $getGroupId, $version, $bugIds, $getSort);
        }
      }
    }
  }

  if ($profileId == -1) {
    rHtmlApi::htmlInfoFooter();
  }
}

/**
 * process project title
 *
 * @param $getSort
 * @param $subject
 * @param $id
 */
function processProjectTitle($getSort, $subject, $id)
{
  if ($getSort == 'vp') {
    rHtmlApi::htmlPluginVersionTitle($subject, $id);
    rHtmlApi::htmlPluginAddDirectoryVersionEntry($subject);
  } else {
    rHtmlApi::htmlPluginProjectTitle($subject, $id);
    rHtmlApi::htmlPluginAddDirectoryProjectEntry($id);
  }
}

/**
 * process roadmap data
 *
 * @param $projectId
 * @param $profileId
 * @param $getGroupId
 * @param $version
 * @param $bugIds
 * @param $getSort
 */
function processRoadmap($projectId, $profileId, $getGroupId, $version, $bugIds, $getSort)
{
  #roadmap object
  $roadmap = new roadmap($bugIds, $profileId, $getGroupId, $projectId, $version['id']);
  # add version to directory
  if ($getSort == 'vp') {
    rHtmlApi::htmlPluginAddDirectorySubProjectEntry($version, $projectId, project_get_name($projectId));
  } else {
    rHtmlApi::htmlPluginAddDirectorySubVersionEntry($projectId, $version['id'], $version['version']);
  }
  # define and print title
  $releaseTitleString = rProApi::getReleasedTitleString($profileId, $getGroupId, $projectId, $version);
  rHtmlApi::printWrapperInHTML($releaseTitleString);
  # print version description
  rHtmlApi::printWrapperInHTML(rProApi::getDescription($version));
  # print version progress bar
  rHtmlApi::printVersionProgress($roadmap);
  # print bug list
  if ($profileId == -1) {
    $doneBugIds = rProApi::getDoneIssueIdsForAllProfiles($bugIds, $getGroupId);
    $doingBugIds = array_diff($bugIds, $doneBugIds);
    rHtmlApi::printBugList($doingBugIds);
    rHtmlApi::printBugList($doneBugIds, TRUE);
  } else {
    rHtmlApi::printBugList($roadmap->getDoingBugIds());
    rHtmlApi::printBugList($roadmap->getDoneBugIds(), TRUE);
  }
  # print text progress
  ($profileId >= 0) ? rHtmlApi::printSingleTextProgress($roadmap) : rHtmlApi::printScaledTextProgress($roadmap);
  # print spacer
  rHtmlApi::htmlPluginSpacer();
}