<?php
require_once ( __DIR__ . '/roadmap_db.php' );

/**
 * Class version_management_api
 *
 * Contains functions for the plugin specific content
 */
class roadmap_pro_api
{

   /**
    * returns the eta value of a bunch of bugs
    *
    * @param $bugIds
    * @return float|int
    */
   public static function getFullEta ( $bugIds )
   {
      $roadmapDb = new roadmap_db();

      $fullEta = 0;
      foreach ( $bugIds as $bugId )
      {
         $bugEtaValue = bug_get_field ( $bugId, 'eta' );

         $etaEnumString = config_get ( 'eta_enum_string' );
         $etaEnumValues = MantisEnum::getValues ( $etaEnumString );

         foreach ( $etaEnumValues as $enumValue )
         {
            if ( $enumValue == $bugEtaValue )
            {
               $etaRow = $roadmapDb->dbGetEtaRowByKey ( $enumValue );
               $fullEta += $etaRow[ 2 ];
            }
         }
      }

      return $fullEta;
   }

   /**
    * returns an array with bug ids and extened information about relations
    *
    * @param $bugIds
    * @return mixed
    */
   public static function calculateBugRelationships ( $bugIds )
   {
      $roadmapDb = new roadmap_db();

      $bugCount = count ( $bugIds );
      $bugHashArray = array ();
      for ( $index = 0; $index < ( $bugCount ); $index++ )
      {
         $bugId = $bugIds[ $index ];
         $bugTargetVersion = bug_get_field ( $bugId, 'target_version' );

         $bugBlockingIds = array ();
         $bugBlockedIds = array ();

         $blockingRelationshipRows = $roadmapDb->dbGetBugRelationship ( $bugId, true );
         $blockedRelationshipRows = $roadmapDb->dbGetBugRelationship ( $bugId, false );

         if ( $blockingRelationshipRows != null )
         {
            foreach ( $blockingRelationshipRows as $blockingRelationship )
            {
               $destBugId = $blockingRelationship[ 0 ];
               $destBugTargetVersion = bug_get_field ( $destBugId, 'target_version' );

               if ( $bugTargetVersion == $destBugTargetVersion )
               {
                  array_push ( $bugBlockingIds, $destBugId );
               }
            }
         }

         if ( $blockedRelationshipRows != null )
         {
            foreach ( $blockedRelationshipRows as $blocked_relationship )
            {
               $srcBugId = $blocked_relationship[ 0 ];
               $srcBugTargetVersion = bug_get_field ( $srcBugId, 'target_version' );

               if ( $bugTargetVersion == $srcBugTargetVersion )
               {
                  array_push ( $bugBlockedIds, $srcBugId );
               }
            }
         }

         $bugHash = array (
            'id' => $bugId,
            'blocking_ids' => $bugBlockingIds,
            'blocked_ids' => $bugBlockedIds
         );

         array_push ( $bugHashArray, $bugHash );
      }

      return $bugHashArray;
   }

   /**
    * generates string for blocked/blocking ids
    *
    * @param $bugIds
    * @param $blocked
    * @return string
    */
   public static function generateBlockIdString ( $bugIds, $blocked )
   {
      if ( $blocked == true )
      {
         $blockIdString = lang_get ( 'blocks' ) . '&nbsp;';
      }
      else
      {
         $blockIdString = lang_get ( 'dependant_on' ) . '&nbsp;';
      }
      $blockedIdCount = count ( $bugIds );
      for ( $index = 0; $index < $blockedIdCount; $index++ )
      {
         $blockIdString .= bug_format_id ( $bugIds[ $index ] );
         if ( $index < ( $blockedIdCount - 1 ) )
         {
            $blockIdString .= ',&nbsp;';
         }
      }

      return $blockIdString;
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
   public static function generateDbStatusValueString ( $statusValues )
   {
      $profileStatus = '';
      $limit = count ( $statusValues );
      for ( $index = 0; $index < $limit; $index++ )
      {
         $profileStatus .= $statusValues[ $index ];
         if ( $index < ( $limit - 1 ) )
         {
            $profileStatus .= ';';
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
      $roadmapDb = new roadmap_db();

      $backupString = array ();
      $backupString[ 0 ] = $eta;
      $backupString[ 1 ] = plugin_lang_get ( 'config_page_eta_unit' );
      $etaString = array ();
      $thresholds = $roadmapDb->dbGetEtaThresholds ();
      $thresholdCount = count ( $thresholds );
      if ( $thresholdCount < 1 )
      {
         $etaString = $backupString;
      }
      else
      {
         for ( $index = 0; $index < $thresholdCount; $index++ )
         {
            $thresholdRow = $thresholds[ $index ];
            $thresholdFrom = $thresholdRow[ 1 ];
            $thresholdTo = $thresholdRow[ 2 ];

            if ( ( $eta > $thresholdFrom ) && ( $eta < $thresholdTo ) )
            {
               $thresholdUnit = $thresholdRow[ 3 ];
               $thresholdFactor = $thresholdRow[ 4 ];

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
         . '&amp;profile_id=' . $profileId . '&amp;project_id=' . $projectId . '">'
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
}
