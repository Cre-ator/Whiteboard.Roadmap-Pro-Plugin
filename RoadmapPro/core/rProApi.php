<?php
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rHtmlApi.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'roadmapManager.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'roadmap.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rGroupManager.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rGroup.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rProfileManager.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rProfile.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rThresholdManager.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rThreshold.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rEta.php' );

/**
 * Class roadmap_pro_api
 *
 * provides functions to calculate and process data
 *
 * @author Stefan Schwarz
 */
class rProApi
{
   /**
    * get database connection infos and connect to the database
    *
    * @return mysqli
    */
   public static function initializeDbConnection ()
   {
      $dbPath = config_get ( 'hostname' );
      $dbUser = config_get ( 'db_username' );
      $dbPass = config_get ( 'db_password' );
      $dbName = config_get ( 'database_name' );

      $mysqli = new mysqli( $dbPath, $dbUser, $dbPass, $dbName );
      $mysqli->connect ( $dbPath, $dbUser, $dbPass, $dbName );

      return $mysqli;
   }

   /**
    * returns true, if there is a duplicate entry.
    *
    * @param $array
    * @return bool
    */
   private static function checkArrayForDuplicates ( $array )
   {
      return count ( $array ) !== count ( array_unique ( $array ) );
   }

   /**
    * returns db-conform string with status values for a profile
    *
    * @param $statusValues
    * @return string
    */
   private static function generateDbValueString ( $statusValues )
   {
      $profileStatus = '';
      $limit = count ( $statusValues );
      for ( $index = 0; $index < $limit; $index++ )
      {
         $statusValue = $statusValues[ $index ];
         if ( is_numeric ( $statusValue ) )
         {
            $profileStatus .= $statusValue;
            if ( $index < ( $limit - 1 ) )
            {
               $profileStatus .= ';';
            }
         }
      }

      return $profileStatus;
   }

   /**
    * assign a given eta value to a specified eta unit
    *
    * @param $eta
    * @return array
    */
   public static function calculateEtaUnit ( $eta )
   {
      $backupString = array ();
      $backupString[ 0 ] = $eta;
      $backupString[ 1 ] = plugin_lang_get ( 'config_page_eta_unit' );
      $etaString = array ();
      $thresholdIds = rThresholdManager::getRThresholdIds ();
      $thresholdCount = count ( $thresholdIds );
      if ( $thresholdCount < 1 )
      {
         $etaString = $backupString;
      }
      else
      {
         for ( $index = 0; $index < $thresholdCount; $index++ )
         {
            $thresholdId = $thresholdIds[ $index ];
            $threshold = new rThreshold( $thresholdId );
            $thresholdFrom = $threshold->getThresholdFrom ();
            $thresholdTo = $threshold->getThresholdTo ();

            if ( ( $eta > $thresholdFrom ) && ( $eta < $thresholdTo ) )
            {
               $thresholdUnit = $threshold->getThresholdUnit ();
               $thresholdFactor = $threshold->getThresholdFactor ();

               $newEta = round ( ( $eta / $thresholdFactor ), 2 );
               $etaString[ 0 ] = $newEta;
               $etaString[ 1 ] = $thresholdUnit;
            }
         }
      }

      if ( empty( $etaString ) )
      {
         return $backupString;
      }
      else
      {
         return $etaString;
      }
   }

   /**
    * returns the generated title string
    *
    * @param $profileId
    * @param $getGroupId
    * @param $projectId
    * @param $version
    * @return string
    */
   public static function getReleasedTitleString ( $profileId, $getGroupId, $projectId, $version )
   {
      $versionId = $version[ 'id' ];
      $versionName = $version[ 'version' ];
      $projectName = string_display ( project_get_name ( $projectId ) );

      $releaseTitleString = '<a href="' . plugin_page ( 'roadmap_page' );
      if ( $getGroupId != null )
      {
         $releaseTitleString .= '&amp;group_id=' . $getGroupId;
      }
      $releaseTitleString .= '&amp;profile_id=' . $profileId . '&amp;project_id=' . $projectId . '" id="v' . $projectId . '_' . $versionId . '">'
         . string_display_line ( $projectName ) . '</a>&nbsp;-'
         . '&nbsp;<a href="' . plugin_page ( 'roadmap_page' );

      if ( $getGroupId != null )
      {
         $releaseTitleString .= '&amp;group_id=' . $getGroupId;
      }
      $releaseTitleString .= '&amp;profile_id=' . $profileId . '&amp;version_id=' . $versionId . '">'
         . string_display_line ( $versionName ) . '</a>'
         . '&nbsp;&nbsp;[&nbsp;<a href="view_all_set.php?type=1&amp;temporary=y&amp;'
         . FILTER_PROPERTY_PROJECT_ID . '=' . $projectId . '&amp;'
         . filter_encode_field_and_value ( FILTER_PROPERTY_TARGET_VERSION, $versionName ) . '">'
         . lang_get ( 'view_bugs_link' ) . '</a>&nbsp;]';

      return $releaseTitleString;
   }

   /**
    * returns version date order string
    *
    * @param $version
    * @return string
    */
   public static function getReleasedDateString ( $version )
   {
      $versionReleaseDate = string_display_line ( date ( config_get ( 'short_date_format' ), $version[ 'date_order' ] ) );
      return plugin_lang_get ( 'roadmap_page_release_date' ) . ':&nbsp;' . $versionReleaseDate;
   }

   /**
    * returns version description string
    *
    * @param $version
    * @return string
    */
   public static function getDescription ( $version )
   {
      $description = $version[ 'description' ];
      if ( strlen ( $description ) > 0 )
      {
         return lang_get ( 'description' ) . ':&nbsp;' . $description;
      }

      return null;
   }

   /**
    * checks relationships for a bug and assign relevant symbols
    *
    * @author Rainer Dierck, Stefan Schwarz
    * @param $bugId
    */
   public static function calcBugSmybols ( $bugId )
   {
      $bugStatus = bug_get_field ( $bugId, 'status' );
      $allRelationships = relationship_get_all ( $bugId, $t_show_project );
      $allRelationshipsCount = count ( $allRelationships );
      $stopFlag = false;
      $forbiddenFlag = false;
      $warningFlag = false;
      $bugEta = bug_get_field ( $bugId, 'eta' );
      $useEta = ( $bugEta != ETA_NONE ) && config_get ( 'enable_eta' );
      $stopAltText = "";
      $forbiddenAltText = "";
      $warningAltText = "";
      $href = string_get_bug_view_url ( $bugId ) . '#relationships_open';

      for ( $index = 0; $index < $allRelationshipsCount; $index++ )
      {
         $relationShip = $allRelationships [ $index ];
         if ( $bugId == $relationShip->src_bug_id )
         {  # root bug is in the src side, related bug in the dest side
            $destinationBugId = $relationShip->dest_bug_id;
            $relationshipDescription = relationship_get_description_src_side ( $relationShip->type );
         }
         else
         {  # root bug is in the dest side, related bug in the src side
            $destinationBugId = $relationShip->src_bug_id;
            $relationshipDescription = relationship_get_description_dest_side ( $relationShip->type );
         }

         # get the information from the related bug and prepare the link
         $destinationBugStatus = bug_get_field ( $destinationBugId, 'status' );
         if ( ( $bugStatus < CLOSED )
            && ( $destinationBugStatus < CLOSED )
            && ( $relationShip->type != BUG_REL_NONE )
         )
         {
            $isStop = ( $relationShip->type == BUG_DEPENDANT )
               && ( $bugId == $relationShip->src_bug_id );
            $isForbidden = $isStop;
            $isWarning = ( $relationShip->type == BUG_DEPENDANT )
               && ( $bugId != $relationShip->src_bug_id );
            if ( ( $isStop ) && ( $bugStatus == $destinationBugStatus ) )
            {
               if ( $stopAltText != "" )
               {
                  $stopAltText .= ", ";
               }
               if ( !$stopFlag )
               {
                  $stopAltText .= trim ( utf8_str_pad ( $relationshipDescription, 20 ) ) . ' ';
               }
               $stopAltText .= string_display_line ( bug_format_id ( $destinationBugId ) );
               $stopFlag = true;
            }
            if ( ( $isForbidden ) && ( $bugStatus > $destinationBugStatus ) )
            {
               if ( $forbiddenAltText != "" )
               {
                  $forbiddenAltText .= ", ";
               }
               if ( !$forbiddenFlag )
               {
                  $forbiddenAltText .= trim ( utf8_str_pad ( $relationshipDescription, 20 ) ) . ' ';
               }
               $forbiddenAltText .= string_display_line ( bug_format_id ( $destinationBugId ) );
               $forbiddenFlag = true;
            }
            if ( ( $isWarning ) && ( $bugStatus >= $destinationBugStatus ) )
            {
               if ( $warningAltText != "" )
               {
                  $warningAltText .= ", ";
               }
               if ( !$warningFlag )
               {
                  $warningAltText .= trim ( utf8_str_pad ( $relationshipDescription, 20 ) ) . ' ';
               }
               $warningAltText .= string_display_line ( bug_format_id ( $destinationBugId ) );
               $warningFlag = true;
            }
         }
      }

      echo '&nbsp;';

      if ( $useEta )
      {
         echo '<img class="symbol" src="plugins/RoadmapPro/files/clock.png' . '" alt="clock" />&nbsp;';
      }
      if ( $forbiddenFlag )
      {
         echo '<a href="' . $href . '"><img class="symbol" src="plugins/RoadmapPro/files/sign_forbidden.png" alt="' . $forbiddenAltText . '" title="' . $forbiddenAltText . '" /></a>&nbsp;';
      }
      if ( $stopFlag )
      {
         echo '<a href="' . $href . '"><img class="symbol" src="plugins/RoadmapPro/files/sign_stop.png" alt="' . $stopAltText . '" title="' . $stopAltText . '" /></a>&nbsp;';
      }
      if ( $warningFlag )
      {
         echo '<a href="' . $href . '"><img class="symbol" src="plugins/RoadmapPro/files/sign_warning.png" alt="' . $warningAltText . '" title="' . $warningAltText . '" /></a>&nbsp;';
      }

      echo '&nbsp;';
   }

   /**
    * get all different profiles in an array
    *
    * @return array
    */
   public static function getProfileEnumNames ()
   {
      $profileEnumNameArray = array ();
      $profileIds = rProfileManager::getRProfileIds ();
      $profileCount = count ( $profileIds );
      if ( $profileCount > 0 )
      {
         for ( $index = 0; $index < $profileCount; $index++ )
         {
            $profileId = $profileIds[ $index ];
            $profile = new rProfile( $profileId );
            $profileName = $profile->getProfileName ();
            array_push ( $profileEnumNameArray, $profileName );
         }
      }

      return $profileEnumNameArray;
   }

   /**
    * get all different profile ids in an array
    *
    * @return array
    */
   public static function getProfileEnumIds ()
   {
      $profileEnumIdArray = array ();
      $profileIds = rProfileManager::getRProfileIds ();
      $profileCount = count ( $profileIds );
      if ( $profileCount > 0 )
      {
         for ( $index = 0; $index < $profileCount; $index++ )
         {
            $profileId = $profileIds[ $index ];
            array_push ( $profileEnumIdArray, $profileId );
         }
      }

      return $profileEnumIdArray;
   }

   /**
    * returns all assigned bug ids to a given target version
    *
    * @param $projectId
    * @param $versionName
    * @return array|null
    */
   public static function dbGetBugIdsByProjectAndTargetVersion ( $projectId, $versionName )
   {
      $mysqli = self::initializeDbConnection ();

      $bugIds = null;
      if ( is_numeric ( $projectId ) )
      {
         $query = /** @lang sql */
            "SELECT id FROM mantis_bug_table
            WHERE target_version = '" . $versionName . "'
            AND project_id = " . $projectId;

         $result = $mysqli->query ( $query );

         if ( 0 != $result->num_rows )
         {
            while ( $row = $result->fetch_row () )
            {
               $bugIds[] = $row[ 0 ];
            }
         }
      }
      $mysqli->close ();

      return $bugIds;
   }


   /**
    * Reset all plugin-related data
    *
    * - config entries
    * - database entities
    */
   public static function dbResetPlugin ()
   {
      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_profile_table";

      $mysqli->query ( $query );

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_profilegroup_table";

      $mysqli->query ( $query );

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_eta_table";

      $mysqli->query ( $query );

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_etathreshold_table";

      $mysqli->query ( $query );

      $query = /** @lang sql */
         "DELETE FROM mantis_config_table
            WHERE config_id LIKE 'plugin_RoadmapPro%'";

      $mysqli->query ( $query );

      $mysqli->close ();

      print_successful_redirect ( 'manage_plugin_page.php' );
   }

   public static function setDefault ()
   {
      if ( count ( rProfileManager::getRProfileIds () ) == 0 )
      {
         $mysqli = self::initializeDbConnection ();

         $query = /** @lang sql */
            'INSERT INTO mantis_plugin_RoadmapPro_profile_table ( id, profile_name, profile_color, profile_status, profile_prio, profile_effort )
            SELECT null,\'Resolved\',\'D2F5B0\',\'80;90\',\'1\',\'75\'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_profile_table
            WHERE profile_name=\'Resolved\')';

         $mysqli->query ( $query );

         $query = /** @lang sql */
            'INSERT INTO mantis_plugin_RoadmapPro_profile_table ( id, profile_name, profile_color, profile_status, profile_prio, profile_effort )
            SELECT null,\'Verified\',\'C9CCC4\',\'90\',\'2\',\'25\'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_profile_table
            WHERE profile_name=\'Verified\')';

         $mysqli->query ( $query );

         $mysqli->close ();
      }
   }

   /**
    * get progress for a roadmap
    *
    * @param $useEta
    * @param $tempEta
    * @param $hashProgress
    * @return string
    */
   public static function getRoadmapProgress ( $useEta, $tempEta, $hashProgress )
   {
      $pageProgress = '';
      if ( $useEta == true )
      {
         $calculatedEta = self::calculateEtaUnit ( $tempEta );
         $pageProgress .= $calculatedEta[ 0 ] . '&nbsp;' . $calculatedEta[ 1 ];
      }
      else
      {
         $pageProgress .= round ( $hashProgress ) . '%';
      }

      return $pageProgress;
   }

   /**
    * change eta values when
    */
   public static function configProcessEta ()
   {
      $postEtaThresholdIds = $_POST[ 'threshold-id' ];
      $postEtaThresholdFrom = $_POST[ 'threshold-from' ];
      $postEtaThresholdTo = $_POST[ 'threshold-to' ];
      $postEtaThresholdUnit = $_POST[ 'threshold-unit' ];
      $postEtaThresholdFactor = $_POST[ 'threshold-factor' ];

      $postEtaValue = $_POST[ 'eta_value' ];
      $etaEnumString = config_get ( 'eta_enum_string' );
      $etaEnumValues = MantisEnum::getValues ( $etaEnumString );

      for ( $index = 0; $index < count ( $etaEnumValues ); $index++ )
      {
         $etaConfig = $etaEnumValues[ $index ];
         $etaUser = $postEtaValue[ $index ];
         $eta = new rEta( $etaConfig );
         $etaIsSet = $eta->getEtaIsSet ();
         if ( $etaIsSet )
         {
            $eta->setEtaConfig ( $etaConfig );
            $eta->setEtaUser ( $etaUser );
            $eta->triggerUpdateInDb ();
         }
         else
         {
            $eta->setEtaConfig ( $etaConfig );
            $eta->setEtaUser ( $etaUser );
            $eta->triggerInsertIntoDb ();
         }
      }

      if ( $postEtaThresholdFrom != null )
      {
         # process existing thresholds
         $thresholdIdCount = count ( $postEtaThresholdIds );
         for ( $index = 0; $index < $thresholdIdCount; $index++ )
         {
            $thresholdUnit = $postEtaThresholdUnit[ $index ];
            if ( strlen ( $thresholdUnit ) > 0 )
            {
               $thresholdId = $postEtaThresholdIds[ $index ];
               $threshold = new rThreshold( $thresholdId );
               $threshold->setThresholdFrom ( $postEtaThresholdFrom[ $index ] );
               $threshold->setThresholdTo ( $postEtaThresholdTo[ $index ] );
               $threshold->setThresholdUnit ( $thresholdUnit );
               $threshold->setThresholdFactor ( $postEtaThresholdFactor[ $index ] );
               $threshold->triggerUpdateInDb ();
            }
         }

         # process new thresholds
         $overallThresholdCount = count ( $postEtaThresholdFrom );
         $newThresholdIndex = 0;
         for ( $newIndex = $thresholdIdCount; $newIndex < $overallThresholdCount; $newIndex++ )
         {
            $newThreshold = new rThreshold();
            $newThreshold->setThresholdFrom ( $postEtaThresholdFrom[ $newIndex ] );
            $newThreshold->setThresholdTo ( $postEtaThresholdTo[ $newIndex ] );
            $newThresholdUnit = $_POST[ 'new-threshold-unit-' . $newThresholdIndex ];
            $newThreshold->setThresholdUnit ( $newThresholdUnit );
            $newThreshold->setThresholdFactor ( $postEtaThresholdFactor[ $newIndex ] );
            $newThreshold->triggerInsertIntoDb ();
            $newThresholdIndex++;
         }
      }
   }

   /**
    * change profile values
    */
   public static function configProcessProfiles ()
   {
      $postProfileIds = $_POST[ 'profile-id' ];
      $postProfileNames = $_POST[ 'profile-name' ];
      $postProfileColor = $_POST[ 'profile-color' ];
      $postProfilePriority = $_POST[ 'profile-prio' ];
      $postProfileEffort = $_POST[ 'profile-effort' ];

      if ( $postProfileNames != null )
      {
         if ( self::checkArrayForDuplicates ( $postProfileNames ) == true )
         {
            # error message
         }
         else
         {
            # process existing profiles
            $profileIdCount = count ( $postProfileIds );
            for ( $index = 0; $index < $profileIdCount; $index++ )
            {
               $profileName = $postProfileNames[ $index ];
               if ( strlen ( $profileName ) > 0 )
               {
                  $profileId = $postProfileIds[ $index ];
                  $profile = new rProfile( $profileId );
                  $profile->setProfileName ( $profileName );
                  $postProfileStatus = $_POST[ 'profile-status-' . $index ];
                  $profile->setProfileStatus ( self::generateDbValueString ( $postProfileStatus ) );
                  $profile->setProfileColor ( $postProfileColor[ $index ] );
                  $profile->setProfilePriority ( $postProfilePriority[ $index ] );
                  $profile->setProfileEffort ( $postProfileEffort[ $index ] );
                  $profile->triggerUpdateInDb ();
               }
            }

            # process new profiles
            $overallProfileCount = count ( $postProfileNames );
            $newStatusIndex = 0;
            for ( $newIndex = $profileIdCount; $newIndex < $overallProfileCount; $newIndex++ )
            {
               $newProfileName = $postProfileNames[ $newIndex ];
               if ( strlen ( $newProfileName ) > 0 )
               {
                  $newProfile = new rProfile();
                  $newProfile->setProfileName ( $newProfileName );
                  $postNewProfileStatus = $_POST[ 'new-status-' . $newStatusIndex ];
                  $newProfile->setProfileStatus ( self::generateDbValueString ( $postNewProfileStatus ) );
                  $newProfile->setProfileColor ( $postProfileColor[ $newIndex ] );
                  $newProfile->setProfilePriority ( $postProfilePriority[ $newIndex ] );
                  $newProfile->setProfileEffort ( $postProfileEffort[ $newIndex ] );
                  $newProfile->triggerInsertIntoDb ();
               }

               $newStatusIndex++;
            }
         }
      }
   }

   /**
    * change group values
    */
   public static function configProcessGroups ()
   {
      $postGroupIds = $_POST[ 'group-id' ];
      $postGroupNames = $_POST[ 'group-name' ];

      if ( $postGroupNames != null )
      {
         if ( self::checkArrayForDuplicates ( $postGroupNames ) == true )
         {
            # error message
         }
         else
         {
            # process existing groups
            $groupIdCount = count ( $postGroupIds );
            for ( $index = 0; $index < $groupIdCount; $index++ )
            {
               $groupName = $postGroupNames[ $index ];
               if ( strlen ( $groupName ) > 0 )
               {
                  $groupId = $postGroupIds[ $index ];
                  $group = new rGroup( $groupId );
                  $group->setGroupName ( $groupName );
                  $postGroupProfiles = $_POST[ 'group-profile-' . $index ];
                  $group->setGroupProfiles ( self::generateDbValueString ( $postGroupProfiles ) );
                  $group->triggerUpdateInDb ();
               }
            }

            # process new groups
            $overallGroupCount = count ( $postGroupNames );
            $newGroupProfileIndex = 0;
            for ( $newIndex = $groupIdCount; $newIndex < $overallGroupCount; $newIndex++ )
            {
               $newGroupName = $postGroupNames[ $newIndex ];
               if ( strlen ( $newGroupName ) > 0 )
               {
                  $newGroup = new rGroup();
                  $newGroup->setGroupName ( $newGroupName );
                  $postNewGroupProfiles = $_POST[ 'new-group-profile-' . $newGroupProfileIndex ];
                  $newGroup->setGroupProfiles ( self::generateDbValueString ( $postNewGroupProfiles ) );
                  $newGroup->triggerInsertIntoDb ();
               }

               $newGroupProfileIndex++;
            }
         }
      }
   }

   /**
    * Updates the value set by a button
    *
    * @param $config
    */
   public static function configUpdateButton ( $config )
   {
      $button = gpc_get_int ( $config );

      if ( plugin_config_get ( $config ) != $button )
      {
         plugin_config_set ( $config, $button );
      }
   }

   /**
    * get done issues for all profiles
    *
    * @param $bugIds
    * @param $groupId
    * @return array
    */
   public static function getDoneIssueIdsForAllProfiles ( $bugIds, $groupId )
   {
      $doneIssuesForAllProfiles = array ();
      if ( $groupId == null )
      {
         $profileIdArray = rProfileManager::getRProfileIds ();
      }
      else
      {
         $group = new rGroup( $groupId );
         $profileIds = $group->getGroupProfiles ();
         $profileIdArray = explode ( ';', $profileIds );
      }

      foreach ( $bugIds as $bugId )
      {
         $bugStatus = bug_get_field ( $bugId, 'status' );
         $profileCount = count ( $profileIdArray );
         $doneCount = 0;
         foreach ( $profileIdArray as $profileId )
         {
            $profile = new rProfile( $profileId );
            $profileStatus = $profile->getProfileStatus ();
            $profileStatusArray = explode ( ';', $profileStatus );

            $done = false;
            foreach ( $profileStatusArray as $profileStatus )
            {
               if ( $bugStatus == $profileStatus )
               {
                  $done = true;
               }
            }

            if ( $done )
            {
               $doneCount++;
            }
         }

         if ( $doneCount == $profileCount )
         {
            array_push ( $doneIssuesForAllProfiles, $bugId );
         }
      }

      return $doneIssuesForAllProfiles;
   }
}
