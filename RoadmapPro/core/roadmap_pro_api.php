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
    * returns true if every item of bug id array has set eta value
    *
    * @param $bugIds
    * @return bool
    */
   public static function checkEtaIsSet ( $bugIds )
   {
      $set = true;
      foreach ( $bugIds as $bugId )
      {
         $bugEtaValue = bug_get_field ( $bugId, 'eta' );
         if ( ( is_null ( $bugEtaValue ) ) || ( $bugEtaValue == 10 ) )
         {
            $set = false;
         }
      }

      return $set;
   }

   /**
    * returns the eta value of a single bug
    *
    * @param $bugId
    * @return float|int
    */
   public static function getSingleEta ( $bugId )
   {
      $roadmapDb = new roadmap_db();

      $eta = 0;
      $bugEtaValue = bug_get_field ( $bugId, 'eta' );

      $etaEnumString = config_get ( 'eta_enum_string' );
      $etaEnumValues = MantisEnum::getValues ( $etaEnumString );

      foreach ( $etaEnumValues as $enumValue )
      {
         if ( $enumValue == $bugEtaValue )
         {
            $etaRow = $roadmapDb->dbGetEtaRowByKey ( $enumValue );
            $eta = $etaRow[ 2 ];
         }
      }

      return $eta;
   }

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
    * returns true if the issue is done like it is defined in the profile preference
    *
    * @param $bugId
    * @param $profileId
    * @return bool
    */
   public static function checkIssueIsDoneById ( $bugId, $profileId )
   {
      $roadmapDb = new roadmap_db();
      $done = false;

      $bugStatus = bug_get_field ( $bugId, 'status' );
      $roadmapProfile = $roadmapDb->dbGetRoadmapProfile ( $profileId );
      $dbRaodmapStatus = $roadmapProfile[ 3 ];
      $roadmapStatusArray = explode ( ';', $dbRaodmapStatus );

      foreach ( $roadmapStatusArray as $roadmapStatus )
      {
         if ( $bugStatus == $roadmapStatus )
         {
            $done = true;
         }
      }

      return $done;
   }

   /**
    * returns the amount of done bugs in a bunch of bugs
    *
    * @param $bugIds
    * @param $profileId
    * @return int
    */
   public static function getDoneBugAmount ( $bugIds, $profileId )
   {
      $doneBugAmount = 0;
      foreach ( $bugIds as $bugId )
      {
         /** specific profile */
         if ( self::checkIssueIsDoneById ( $bugId, $profileId ) )
         {
            $doneBugAmount++;
         }
      }

      return $doneBugAmount;
   }

   /**
    * returns the ids of done bugs in a bunch of bugs
    *
    * @param $bugIds
    * @param $profileId
    * @return array
    */
   public static function getDoneBugIds ( $bugIds, $profileId )
   {
      $doneBugIds = array ();
      foreach ( $bugIds as $bugId )
      {
         /** specific profile */
         if ( self::checkIssueIsDoneById ( $bugId, $profileId ) )
         {
            array_push ( $doneBugIds, $bugId );
         }
      }

      return $doneBugIds;
   }

   /**
    * returns all subproject ids incl. the selected one except it is zero
    *
    * @return array
    */
   public static function prepareProjectIds ()
   {
      $currentProjectId = helper_get_current_project ();
      $subProjectIds = project_hierarchy_get_all_subprojects ( $currentProjectId );

      $projectIds = array ();
      if ( $currentProjectId > 0 )
      {
         array_push ( $projectIds, $currentProjectId );
      }

      foreach ( $subProjectIds as $sub_project_id )
      {
         array_push ( $projectIds, $sub_project_id );
      }

      return $projectIds;
   }

   /**
    * returns true, if the used mantis version is release 1.2.x
    *
    * @return bool
    */
   public static function check_mantis_version_is_released ()
   {
      return substr ( MANTIS_VERSION, 0, 4 ) == '1.2.';
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

         if ( is_null ( $blockingRelationshipRows ) == false )
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

         if ( is_null ( $blockedRelationshipRows ) == false )
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
}
