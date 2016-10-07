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
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rWeekDayManager.php' );

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
    * returns array with 1/0 values when plugin comprehensive table is installed
    *
    * @return array
    */
   public static function checkWhiteboardTablesExist ()
   {
      $boolArray = array ();

      $boolArray[ 0 ] = self::checkTable ( 'menu' );
      $boolArray[ 1 ] = self::checkTable ( 'eta' );
      $boolArray[ 2 ] = self::checkTable ( 'etathreshold' );
      $boolArray[ 3 ] = self::checkTable ( 'workday' );

      return $boolArray;
   }

   /**
    * checks if given table exists
    *
    * @param $tableName
    * @return bool
    */
   private static function checkTable ( $tableName )
   {
      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT COUNT(id) FROM mantis_plugin_whiteboard_' . $tableName . '_table';
      $result = $mysqli->query ( $query );
      $mysqli->close ();
      if ( $result->num_rows != 0 )
      {
         return TRUE;
      }
      else
      {
         return FALSE;
      }
   }

   public static function checkPluginIsRegisteredInWhiteboardMenu ()
   {
      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT COUNT(id) FROM mantis_plugin_whiteboard_menu_table
         WHERE plugin_name=\'' . plugin_get_current () . '\'';

      $result = $mysqli->query ( $query );
      $mysqli->close ();
      if ( $result->num_rows != 0 )
      {
         $resultCount = mysqli_fetch_row ( $result )[ 0 ];
         if ( $resultCount > 0 )
         {
            return TRUE;
         }
         else
         {
            return FALSE;
         }
      }

      return NULL;
   }

   /**
    * register plugin in whiteboard menu
    */
   public static function addPluginToWhiteboardMenu ()
   {
      $pluginName = plugin_get_current ();
      $pluginAccessLevel = VIEWER;
      $pluginShowMenu = ON;
      $pluginPath = plugin_page ( 'roadmap_page' );

      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         'INSERT INTO mantis_plugin_whiteboard_menu_table (id, plugin_name, plugin_access_level, plugin_show_menu, plugin_menu_path)
         SELECT null,\'' . $pluginName . '\',' . $pluginAccessLevel . ',' . $pluginShowMenu . ',\'' . $pluginPath . '\'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM mantis_plugin_whiteboard_menu_table
         WHERE plugin_name=\'' . $pluginName . '\')';

      $mysqli->query ( $query );
      $mysqli->close ();
   }

   /**
    * edit plugin data in whiteboard menu
    *
    * @param $field
    * @param $value
    */
   public static function editPluginInWhiteboardMenu ( $field, $value )
   {
      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         'UPDATE mantis_plugin_whiteboard_menu_table
         SET ' . $field . '=\'' . $value . '\'
         WHERE plugin_name=\'' . plugin_get_current () . '\'';

      $mysqli->query ( $query );
      $mysqli->close ();
   }


   /**
    * remove plugin from whiteboard menu
    */
   public static function removePluginFromWhiteboardMenu ()
   {
      $mysqli = self::initializeDbConnection ();

      $query = /** @lang sql */
         'DELETE FROM mantis_plugin_whiteboard_menu_table
         WHERE plugin_name=\'' . plugin_get_current () . '\'';

      $mysqli->query ( $query );
      $mysqli->close ();
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
    * todo
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
      $backupString[ 2 ] = NULL;
      $etaString = array ();
      $thresholdIds = rThresholdManager::getRThresholdIds ();
      $thresholdCount = count ( $thresholdIds );
      if ( $thresholdCount == 0 )
      {
         $etaString = $backupString;
      }
      else
      {
         $calcDone = FALSE;
         $thresholdFactor = 1;
         $thresholdUnit = plugin_lang_get ( 'config_page_eta_unit' );
         for ( $index = 0; $index < $thresholdCount; $index++ )
         {
            $thresholdId = $thresholdIds[ $index ];
            $threshold = new rThreshold( $thresholdId );
            $thresholdTo = $threshold->getThresholdTo ();

            $nextThresholdExists = FALSE;
            $usedThreshold = NULL;
            $nextThreshold = NULL;
            if ( $index < ( $thresholdCount - 1 ) )
            {
               $nextThresholdId = $thresholdIds[ $index + 1 ];
               $nextThreshold = new rThreshold( $nextThresholdId );
               $nextThresholdExists = TRUE;
            }

            if ( !$calcDone )
            {
               if ( ( $eta > $thresholdTo ) && ( $nextThresholdExists ) )
               {
                  $thresholdUnit = $nextThreshold->getThresholdUnit ();
                  $thresholdFactor = $nextThreshold->getThresholdFactor ();
                  $usedThreshold = $nextThreshold;

               }
               else
               {
                  $thresholdUnit = $threshold->getThresholdUnit ();
                  $thresholdFactor = $threshold->getThresholdFactor ();
                  $usedThreshold = $threshold;
                  $calcDone = TRUE;
               }
            }

            $newEta = ( $eta / $thresholdFactor );
            $etaString[ 0 ] = $newEta;
            $etaString[ 1 ] = $thresholdUnit;
            $etaString[ 2 ] = $usedThreshold;
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
      $getSort = $_GET[ 'sort' ];

      $releaseTitleString = '<a href="' . plugin_page ( 'roadmap_page' );
      if ( $getGroupId != NULL )
      {
         $releaseTitleString .= '&amp;group_id=' . $getGroupId;
      }
      $releaseTitleString .= '&amp;profile_id=' . $profileId . '&amp;project_id=' . $projectId;
      if ( $getSort != NULL )
      {
         $releaseTitleString .= '&amp;sort=' . $getSort;
      }
      $releaseTitleString .= '" id="v' . $projectId . '_' . $versionId . '">'
         . string_display_line ( $projectName ) . '</a>&nbsp;-'
         . '&nbsp;<a href="' . plugin_page ( 'roadmap_page' );

      if ( $getGroupId != NULL )
      {
         $releaseTitleString .= '&amp;group_id=' . $getGroupId;
      }
      $releaseTitleString .= '&amp;profile_id=' . $profileId . '&amp;version_id=' . $versionId;
      if ( $getSort != NULL )
      {
         $releaseTitleString .= '&amp;sort=' . $getSort;
      }
      $releaseTitleString .= '">'
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
    * @param $versionReleaseDate
    * @return string
    */
   public static function getReleasedDateString ( $versionReleaseDate )
   {
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

      return NULL;
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
      $stopFlag = FALSE;
      $forbiddenFlag = FALSE;
      $warningFlag = FALSE;
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
               $stopFlag = TRUE;
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
               $forbiddenFlag = TRUE;
            }
            if ( ( $isWarning ) && ( $bugStatus < $destinationBugStatus ) )
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
               $warningFlag = TRUE;
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

      $bugIds = NULL;
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
    * returns all assigned bug ids to a given target version
    *
    * @param $versionName
    * @return array|null
    */
   public static function dbGetBugIdsByTargetVersion ( $versionName )
   {
      $mysqli = self::initializeDbConnection ();

      $bugIds = NULL;
      $query = /** @lang sql */
         'SELECT id FROM mantis_bug_table
            WHERE target_version = \'' . $versionName . '\'';

      $result = $mysqli->query ( $query );

      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row () )
         {
            $bugIds[] = $row[ 0 ];
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
         'DROP TABLE mantis_plugin_RoadmapPro_profile_table';

      $mysqli->query ( $query );

      $query = /** @lang sql */
         'DROP TABLE mantis_plugin_RoadmapPro_profilegroup_table';

      $mysqli->query ( $query );

      $query = /** @lang sql */
         'DROP TABLE mantis_plugin_RoadmapPro_eta_table';

      $mysqli->query ( $query );

      $query = /** @lang sql */
         'DROP TABLE mantis_plugin_RoadmapPro_etathreshold_table';

      $mysqli->query ( $query );

      $query = /** @lang sql */
         'DELETE FROM mantis_config_table WHERE config_id LIKE \'plugin_RoadmapPro%\'';

      $mysqli->query ( $query );

      $mysqli->close ();

      print_successful_redirect ( 'manage_plugin_page.php' );
   }

   /**
    * set default plugin config
    */
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

         $query = /** @lang sql */
            'INSERT INTO mantis_plugin_whiteboard_workday_table ( id, workday_values )
            SELECT null,\'0;0;0;0;0;0;0\'';

         $mysqli->query ( $query );

         $mysqli->close ();
      }
   }

   /**
    * Updates the value set by an input text field
    *
    * @param $value
    * @param $constant
    */
   public static function updateSingleValue ( $value, $constant )
   {
      $actualValue = NULL;

      if ( is_int ( $value ) )
      {
         $actualValue = gpc_get_int ( $value, $constant );
      }

      if ( is_string ( $value ) )
      {
         $actualValue = gpc_get_string ( $value, $constant );
      }

      if ( plugin_config_get ( $value ) != $actualValue )
      {
         plugin_config_set ( $value, $actualValue );
      }
   }

   /**
    * get progress for a roadmap
    *
    * @param $useEta
    * @param $tempEta
    * @param $profileHash
    * @param rThreshold $maxThreshold
    * @return string
    */
   public static function getRoadmapProgress ( $useEta, $tempEta, $profileHash, rThreshold $maxThreshold )
   {
      $hashProgress = $profileHash[ 1 ];
      $pageProgress = '';
      if ( $useEta == TRUE )
      {
         if ( $maxThreshold != NULL )
         {
            $calculatedEta = array ();
            $factor = $maxThreshold->getThresholdFactor ();
            $calculatedEta[ 0 ] = $tempEta / $factor;
            $calculatedEta[ 1 ] = $maxThreshold->getThresholdUnit ();
         }
         else
         {
            $calculatedEta = self::calculateEtaUnit ( $tempEta );
         }
         setlocale ( LC_NUMERIC, lang_get_current () );
         $pageProgress .= round ( ( $calculatedEta[ 0 ] ), 1 ) . $calculatedEta[ 1 ];
      }
      else
      {
         $profileEffortFactor = $profileHash[ 2 ];
         $pageProgress .= round ( $hashProgress / $profileEffortFactor ) . '%';
      }

      return $pageProgress;
   }

   /**
    * change time calc values
    */
   public static function configProcessTimeCalc ()
   {
      $weekDayValueArray = $_POST[ 'weekDayValue' ];
      $weekDayConfigString = implode ( ';', $weekDayValueArray );
      rWeekDayManager::setWorkDayConfig ( $weekDayConfigString );
   }

   /**
    * change eta values when
    */
   public static function configProcessEta ()
   {
      $postEtaThresholdIds = $_POST[ 'threshold-id' ];
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

      if ( $postEtaThresholdTo != NULL )
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
               $threshold->setThresholdTo ( $postEtaThresholdTo[ $index ] );
               $threshold->setThresholdUnit ( $thresholdUnit );
               $threshold->setThresholdFactor ( $postEtaThresholdFactor[ $index ] );
               $threshold->triggerUpdateInDb ();
            }
         }

         # process new thresholds
         $overallThresholdCount = count ( $postEtaThresholdTo );
         $newThresholdIndex = 0;
         for ( $newIndex = $thresholdIdCount; $newIndex < $overallThresholdCount; $newIndex++ )
         {
            $newThreshold = new rThreshold();
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

      if ( $postProfileNames != NULL )
      {
         if ( self::checkArrayForDuplicates ( $postProfileNames ) == TRUE )
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

      if ( $postGroupNames != NULL )
      {
         if ( self::checkArrayForDuplicates ( $postGroupNames ) == TRUE )
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
      if ( $groupId == NULL )
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

            $done = FALSE;
            foreach ( $profileStatusArray as $profileStatus )
            {
               if ( $bugStatus == $profileStatus )
               {
                  $done = TRUE;
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

   /**
    * calculate and return the profile effort factor
    *
    * @param roadmap $roadmap
    * @return float|int
    */
   public static function getProfileEffortFactor ( roadmap $roadmap )
   {
      $profileId = $roadmap->getProfileId ();
      $groupId = $roadmap->getGroupId ();

      $profile = new rProfile( $profileId );
      $profileEffort = $profile->getProfileEffort ();
      $roadmapProfileIds = rProfileManager::getGroupSpecProfileIds ( $groupId );
      $profileCount = count ( $roadmapProfileIds );
      $sumProfileEffort = rProfileManager::getSumRProfileEffort ( $groupId );

      if ( $sumProfileEffort == 0 )
      {
         $profileEffortFactor = ( 1 / $profileCount );
      }
      else
      {
         $profileEffortFactor = round ( ( $profileEffort / $sumProfileEffort ), 2 );
      }

      return $profileEffortFactor;
   }

   public static function array_multi_unique ( $multiArray )
   {
      /* array_unique() für multidimensionale Arrays
       * @param    array    $multiArray = array(array(..), array(..), ..)
       * @return   array    Array mit einmaligen Elementen
      **/
      $uniqueArray = array ();
      // alle Array-Elemente durchgehen
      foreach ( $multiArray as $subArray )
      {
         // prüfen, ob Element bereits im Unique-Array
         if ( !in_array ( $subArray, $uniqueArray ) )
         {
            // Element hinzufügen, wenn noch nicht drin
            $uniqueArray[] = $subArray;
         }
      }
      return $uniqueArray;
   }

   public static function getVPVersions ( $projectIds, $getVersionId )
   {
      $vPVersions = array ();
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
         $tmpVersions = array ();
         if ( $getVersionId == NULL )
         {
            $tmpVersions = array_reverse ( version_get_all_rows ( $projectId, FALSE ) );
         }

         foreach ( $tmpVersions as $tmpVersion )
         {
            $tmpBugIds = rProApi::dbGetBugIdsByProjectAndTargetVersion ( $projectId, $tmpVersion[ 'version' ] );
            if ( count ( $tmpBugIds ) > 0 )
            {
               array_push ( $vPVersions, $tmpVersion );
            }
         }
      }

      return self::array_multi_unique ( $vPVersions );
   }

   public static function getVPProjects ( $version )
   {
      $bugIds = rProApi::dbGetBugIdsByTargetVersion ( $version[ 'version' ] );
      $vPProjectIds = array ();
      foreach ( $bugIds as $bugId )
      {
         array_push ( $vPProjectIds, bug_get_field ( $bugId, 'project_id' ) );
      }

      return array_unique ( $vPProjectIds );
   }
}
