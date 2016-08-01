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
   private static function getDoneBugIds ( $bugIds, $profileId )
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
    * calculate the data for summed process
    *
    * @param $bugIds
    * @param $useEta
    * @param $overallBugAmount
    * @return array
    */
   public static function calcScaledData ( $bugIds, $useEta, $overallBugAmount )
   {
      $roadmapDb = new roadmap_db();
      $profileProgressValueArray = array ();
      $roadmapProfiles = $roadmapDb->dbGetRoadmapProfiles ();
      $profileCount = count ( $roadmapProfiles );
      $sumProgressDoneBugAmount = 0;
      $sumProgressDoneBugPercent = 0;
      $sumProgressDoneEta = 0;
      $fullEta = ( self::getFullEta ( $bugIds ) ) * $profileCount;
      for ( $index = 0; $index < $profileCount; $index++ )
      {
         $roadmapProfile = $roadmapProfiles[ $index ];
         $tProfileId = $roadmapProfile[ 0 ];
         $tDoneBugAmount = self::getDoneBugAmount ( $bugIds, $tProfileId );
         $sumProgressDoneBugAmount += $tDoneBugAmount;
         if ( $useEta )
         {
            /** calculate eta for profile */
            $doneEta = 0;
            $doneBugIds = self::getDoneBugIds ( $bugIds, $tProfileId );
            foreach ( $doneBugIds as $doneBugId )
            {
               $doneEta += self::getSingleEta ( $doneBugId );
            }
            $doneEtaPercent = round ( ( ( $doneEta / $fullEta ) * 100 ), 1 );
            $sumProgressDoneEta += $doneEta;

            $profileHash = $tProfileId . ';' . $doneEtaPercent;
         }
         else
         {
            $tVersionProgress = ( $tDoneBugAmount / $overallBugAmount );
            $progessDonePercent = round ( ( $tVersionProgress * 100 / $profileCount ), 1 );
            if ( round ( ( $sumProgressDoneBugPercent + $progessDonePercent ), 1 ) == 99.9 )
            {
               $progessDonePercent = 100 - $sumProgressDoneBugPercent;
            }
            $sumProgressDoneBugPercent += $progessDonePercent;

            $profileHash = $tProfileId . ';' . $progessDonePercent;
         }

         array_push ( $profileProgressValueArray, $profileHash );
      }

      /** whole progress of the version */
      if ( $useEta )
      {
         $wholeProgress = ( $sumProgressDoneEta / $fullEta );
      }
      else
      {
         $wholeProgress = ( ( $sumProgressDoneBugAmount / $profileCount ) / $overallBugAmount );
      }
      $progressPercent = round ( ( $wholeProgress * 100 ), 1 );

      $result = [ 0 => $profileProgressValueArray, 1 => $progressPercent ];

      return $result;
   }

   /**
    * calculate the data for single process
    *
    * @param $bugIds
    * @param $profileId
    * @param $useEta
    * @param $overallBugAmount
    * @return array
    */
   public static function calcSingleData ( $bugIds, $profileId, $useEta, $overallBugAmount )
   {
      $fullEta = ( self::getFullEta ( $bugIds ) );
      $doneEta = 0;
      if ( $useEta )
      {
         $doneBugIds = self::getDoneBugIds ( $bugIds, $profileId );
         foreach ( $doneBugIds as $doneBugId )
         {
            $doneEta += self::getSingleEta ( $doneBugId );
         }

         $progressPercent = 0;
         if ( $fullEta > 0 )
         {
            $progressPercent = round ( ( ( $doneEta / $fullEta ) * 100 ), 1 );
         }
      }
      else
      {
         $doneBugAmount = self::getDoneBugAmount ( $bugIds, $profileId );
         $progress = ( $doneBugAmount / $overallBugAmount );
         $progressPercent = round ( ( $progress * 100 ), 1 );
      }

      $result = [ 0 => $doneEta, 1 => $progressPercent ];

      return $result;
   }
}
