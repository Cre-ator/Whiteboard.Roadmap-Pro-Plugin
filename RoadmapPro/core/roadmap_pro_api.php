<?php
require_once ( __DIR__ . '/roadmap_html_api.php' );
require_once ( __DIR__ . '/roadmap.php' );
require_once ( __DIR__ . '/rProfileManager.php' );
require_once ( __DIR__ . '/rProfile.php' );
require_once ( __DIR__ . '/rThresholdManager.php' );
require_once ( __DIR__ . '/rThreshold.php' );

/**
 * Class version_management_api
 *
 * Contains functions for the plugin specific content
 */
class roadmap_pro_api
{
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
   public static function checkArrayForDuplicates ( $array )
   {
      return count ( $array ) !== count ( array_unique ( $array ) );
   }

   /**
    * returns db-conform string with status values for a profile
    *
    * @param $statusValues
    * @return string
    */
   public static function generateDbValueString ( $statusValues )
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

      if ( empty( $etaString ) == false )
      {
         return $etaString;
      }
      else
      {
         return $backupString;
      }
   }

   /**
    * returns the generated title string
    *
    * @param $profileId
    * @param $projectId
    * @param $version
    * @return string
    */
   public static function getReleasedTitleString ( $profileId, $projectId, $version )
   {
      $versionId = $version[ 'id' ];
      $versionName = $version[ 'version' ];
      $versionDate = $version[ 'date_order' ];
      $versionReleaseDate = string_display_line ( date ( config_get ( 'short_date_format' ), $versionDate ) );
      $projectName = string_display ( project_get_name ( $projectId ) );

      $releaseTitleString = '<a href="' . plugin_page ( 'roadmap_page' )
         . '&amp;profile_id=' . $profileId . '&amp;project_id=' . $projectId . '" id="v' . $versionId . '">'
         . string_display_line ( $projectName ) . '</a>&nbsp;-'
         . '&nbsp;<a href="' . plugin_page ( 'roadmap_page' )
         . '&amp;profile_id=' . $profileId . '&amp;version_id=' . $versionId . '">'
         . string_display_line ( $versionName ) . '</a>'
         . '&nbsp;(' . lang_get ( 'scheduled_release' ) . '&nbsp;'
         . $versionReleaseDate . ')&nbsp;&nbsp;[&nbsp;<a href="view_all_set.php?type=1&amp;temporary=y&amp;'
         . FILTER_PROPERTY_PROJECT_ID . '=' . $projectId . '&amp;'
         . filter_encode_field_and_value ( FILTER_PROPERTY_TARGET_VERSION, $versionName ) . '">'
         . lang_get ( 'view_bugs_link' ) . '</a>&nbsp;]';

      return $releaseTitleString;
   }

   public static function calcBugSmybols ( $bugId )
   {
      $bugStatus = bug_get_field ( $bugId, 'status' );
      $allRelationships = relationship_get_all ( $bugId, $t_show_project );
      $allRelationshipsCount = count ( $allRelationships );
      $stopFlag = false;
      $forbiddenFlag = false;
      $warningFlag = false;
      $bugEta = bug_get_field ( $bugId, 'eta' );
      $useEta = ( $bugEta > 10 ) && config_get ( 'enable_eta' );
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
         echo '<img class="symbol" src="' . ROADMAPPRO_PLUGIN_URL . 'files/clock.png' . '" alt="clock" />&nbsp;';
      }
      if ( $forbiddenFlag )
      {
         echo '<a href="' . $href . '"><img class="symbol" src="' . ROADMAPPRO_PLUGIN_URL . 'files/sign_forbidden.png" alt="' . $forbiddenAltText . '" title="' . $forbiddenAltText . '" /></a>&nbsp;';
      }
      if ( $stopFlag )
      {
         echo '<a href="' . $href . '"><img class="symbol" src="' . ROADMAPPRO_PLUGIN_URL . 'files/sign_stop.png" alt="' . $stopAltText . '" title="' . $stopAltText . '" /></a>&nbsp;';
      }
      if ( $warningFlag )
      {
         echo '<a href="' . $href . '"><img class="symbol" src="' . ROADMAPPRO_PLUGIN_URL . 'files/sign_warning.png" alt="' . $warningAltText . '" title="' . $warningAltText . '" /></a>&nbsp;';
      }

      echo '&nbsp;';
   }

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
         "DROP TABLE mantis_plugin_RoadmapPro_eta_table";

      $mysqli->query ( $query );

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_unit_table";

      $mysqli->query ( $query );

      $query = /** @lang sql */
         "DELETE FROM mantis_config_table
            WHERE config_id LIKE 'plugin_RoadmapPro%'";

      $mysqli->query ( $query );

      $mysqli->close ();

      print_successful_redirect ( 'manage_plugin_page.php' );
   }
}
