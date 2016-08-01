<?php

/**
 * Created by PhpStorm.
 * User: stefan.schwarz
 * Date: 02.08.2016
 * Time: 00:13
 */
class roadmap_bugdata
{
   private $bugIds;
   private $profileId;
   private $doneEta;
   private $progressPercent;
   private $profileProgressValueArray;

   private $etaIsSet;
   private $singleEta;
   private $fullEta;
   private $doneBugIds;

   function __construct ( $bugIds, $profileId )
   {
      $this->bugIds = $bugIds;
      $this->profileId = $profileId;
      $this->doneBugIds = array ();
      $this->profileProgressValueArray = array ();
   }

   public function getEtaIsSet ()
   {
      $this->checkEtaIsSet ();
      return $this->etaIsSet;
   }

   public function getSingleEta ( $bugId )
   {
      $this->calcSingleEta ( $bugId );
      return $this->singleEta;
   }

   public function getFullEta ()
   {
      $this->calcFullEta ();
      return $this->fullEta;
   }

   public function getDoneBugIds ()
   {
      $this->calcDoneBugIds ();
      return $this->doneBugIds;
   }

   public function getDoneEta ()
   {
      $this->calcDoneEta ();
      return $this->doneEta;
   }

   public function getSingleProgressPercent ()
   {
      $this->calcSingleProgressPercent ();
      return $this->progressPercent;
   }

   public function setProfileId ( $profileId )
   {
      $this->profileId = $profileId;
   }

   public function resetDoneBugIds ()
   {
      $this->doneBugIds = array ();
   }

   /**
    * returns true if every item of bug id array has set eta value
    *
    * @return bool
    */
   private function checkEtaIsSet ()
   {
      $this->etaIsSet = true;
      foreach ( $this->bugIds as $bugId )
      {
         $bugEtaValue = bug_get_field ( $bugId, 'eta' );
         if ( ( $bugEtaValue == null ) || ( $bugEtaValue == 10 ) )
         {
            $this->etaIsSet = false;
         }
      }
   }

   /**
    * returns the eta value of a bunch of bugs
    *
    * @return float|int
    */
   private function calcFullEta ()
   {
      $roadmapDb = new roadmap_db();
      foreach ( $this->bugIds as $bugId )
      {
         $bugEtaValue = bug_get_field ( $bugId, 'eta' );

         $etaEnumString = config_get ( 'eta_enum_string' );
         $etaEnumValues = MantisEnum::getValues ( $etaEnumString );

         foreach ( $etaEnumValues as $enumValue )
         {
            if ( $enumValue == $bugEtaValue )
            {
               $etaRow = $roadmapDb->dbGetEtaRowByKey ( $enumValue );
               $this->fullEta += $etaRow[ 2 ];
            }
         }
      }
   }

   /**
    * returns the eta value of a single bug
    *
    * @param $bugId
    * @return float|int
    */
   private function calcSingleEta ( $bugId )
   {
      $roadmapDb = new roadmap_db();
      $bugEtaValue = bug_get_field ( $bugId, 'eta' );

      $etaEnumString = config_get ( 'eta_enum_string' );
      $etaEnumValues = MantisEnum::getValues ( $etaEnumString );

      foreach ( $etaEnumValues as $enumValue )
      {
         if ( $enumValue == $bugEtaValue )
         {
            $etaRow = $roadmapDb->dbGetEtaRowByKey ( $enumValue );
            $this->singleEta = $etaRow[ 2 ];
         }
      }
   }

   /**
    * returns the ids of done bugs in a bunch of bugs
    *
    * @return array
    */
   private function calcDoneBugIds ()
   {
      foreach ( $this->bugIds as $bugId )
      {
         /** specific profile */
         if ( $this->checkIssueIsDoneById ( $bugId ) == true )
         {
            array_push ( $this->doneBugIds, $bugId );
            $this->doneBugIds = array_unique ( $this->doneBugIds );
         }
      }
   }

   /**
    * returns true if the issue is done like it is defined in the profile preference
    *
    * @param $bugId
    * @return bool
    */
   public function checkIssueIsDoneById ( $bugId )
   {
      $roadmapDb = new roadmap_db();
      $done = false;

      $bugStatus = bug_get_field ( $bugId, 'status' );
      $roadmapProfile = $roadmapDb->dbGetRoadmapProfile ( $this->profileId );
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

   private function calcDoneEta ()
   {
      $roadmapBugData = new roadmap_bugdata( $this->bugIds, $this->profileId );
      $this->doneEta = 0;
      $doneBugIds = $roadmapBugData->getDoneBugIds ();
      if ( $roadmapBugData->getEtaIsSet () )
      {
         foreach ( $doneBugIds as $doneBugId )
         {
            $this->doneEta += $roadmapBugData->getSingleEta ( $doneBugId );
         }
      }
   }

   private function calcSingleProgressPercent ()
   {
      $this->doneEta = 0;
      $doneBugIds = $this->getDoneBugIds ();
      if ( $this->getEtaIsSet () )
      {
         $fullEta = $this->getFullEta ();
         foreach ( $doneBugIds as $doneBugId )
         {
            $this->doneEta += $this->getSingleEta ( $doneBugId );
         }

         if ( $fullEta > 0 )
         {
            $this->progressPercent = round ( ( ( $this->doneEta / $fullEta ) * 100 ), 1 );
         }
      }
      else
      {
         $doneBugAmount = count ( $doneBugIds );
         $allBugCount = count ( $this->bugIds );
         $progress = ( $doneBugAmount / $allBugCount );
         $this->progressPercent = round ( ( $progress * 100 ), 1 );
      }
   }





   public function calcData ()
   {
      /** object initialization */
      $roadmapDb = new roadmap_db();
      $roadmapBugData = new roadmap_bugdata( $this->bugIds, $this->profileId );

      /** variables */
      $roadmapProfiles = $roadmapDb->dbGetRoadmapProfiles ();
      $useEta = $roadmapBugData->getEtaIsSet ();
      $allBugCount = count ( $this->bugIds );
      $profileCount = count ( $roadmapProfiles );
      $sumProgressDoneBugAmount = 0;
      $sumProgressDoneBugPercent = 0;
      $sumProgressDoneEta = 0;
      $fullEta = ( $roadmapBugData->getFullEta () ) * $profileCount;

      /** iterate through profiles */
      for ( $index = 0; $index < $profileCount; $index++ )
      {
         $roadmapProfile = $roadmapProfiles[ $index ];
         $tProfileId = $roadmapProfile[ 0 ];
         $roadmapBugData->setProfileId ( $tProfileId );

         $doneBugIds = $roadmapBugData->getDoneBugIds ();
         $tDoneBugAmount = count ( $doneBugIds );
         $sumProgressDoneBugAmount += $tDoneBugAmount;
         if ( $useEta )
         {
            /** calculate eta for profile */
            $doneEta = 0;
            foreach ( $doneBugIds as $doneBugId )
            {
               $doneEta += $roadmapBugData->getSingleEta ( $doneBugId );
            }
            $doneEtaPercent = round ( ( ( $doneEta / $fullEta ) * 100 ), 1 );
            $sumProgressDoneEta += $doneEta;

            $profileHash = $tProfileId . ';' . $doneEtaPercent;
         }
         else
         {
            $tVersionProgress = ( $tDoneBugAmount / $allBugCount );
            $progessDonePercent = round ( ( $tVersionProgress * 100 / $profileCount ), 1 );
            if ( round ( ( $sumProgressDoneBugPercent + $progessDonePercent ), 1 ) == 99.9 )
            {
               $progessDonePercent = 100 - $sumProgressDoneBugPercent;
            }
            $sumProgressDoneBugPercent += $progessDonePercent;

            $profileHash = $tProfileId . ';' . $progessDonePercent;
         }

         array_push ( $this->profileProgressValueArray, $profileHash );
         $roadmapBugData->resetDoneBugIds ();
         $roadmapBugData->setProfileId ( $this->profileId );
      }

      /** whole progress of the version */
      if ( $useEta )
      {
         $wholeProgress = ( $sumProgressDoneEta / $fullEta );
      }
      else
      {
         $wholeProgress = ( ( $sumProgressDoneBugAmount / $profileCount ) / $allBugCount );
      }
      $this->progressPercent = round ( ( $wholeProgress * 100 ), 1 );
   }

   public function getProfileProgressValueArray ()
   {
      return $this->profileProgressValueArray;
   }

   public function getProgressPercent ()
   {
      return $this->progressPercent;
   }
}