<?php

/**
 * Created by PhpStorm.
 * User: stefan.schwarz
 * Date: 02.08.2016
 * Time: 00:13
 */
class roadmap
{
   private $versionId;
   private $bugIds;
   private $profileId;
   private $doneEta;
   private $progressPercent;
   private $profileHashArray;

   private $etaIsSet;
   private $singleEta;
   private $fullEta;
   private $doingBugIds;
   private $doneBugIds;
   private $issueIsDone;

   function __construct ( $bugIds, $profileId )
   {
      $this->bugIds = $bugIds;
      $this->profileId = $profileId;
      $this->doneBugIds = array ();
      $this->doingBugIds = array ();
      $this->profileHashArray = array ();
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

   public function getDoingBugIds ()
   {
      $this->calcDoingBugIds ();
      return $this->doingBugIds;
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

   public function getIssueIsDone ( $bugId )
   {
      $this->checkIssueIsDoneById ( $bugId );
      return $this->issueIsDone;
   }

   public function getProfileId ()
   {
      return $this->profileId;
   }

   public function getBugIds ()
   {
      return $this->bugIds;
   }

   public function setProfileId ( $profileId )
   {
      $this->profileId = $profileId;
   }

   private function resetDoneBugIds ()
   {
      $this->doneBugIds = array ();
   }

   public function setVersionId ( $versionId )
   {
      $this->versionId = $versionId;
   }

   public function getVersionId ()
   {
      return $this->versionId;
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
      $this->fullEta = 0;
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
         $this->getIssueIsDone ( $bugId );
         if ( $this->issueIsDone )
         {
            array_push ( $this->doneBugIds, $bugId );
            $this->doneBugIds = array_unique ( $this->doneBugIds );
         }
      }
   }

   private function calcDoingBugIds ()
   {
      foreach ( $this->bugIds as $bugId )
      {
         $this->getIssueIsDone ( $bugId );
         if ( $this->issueIsDone == false )
         {
            array_push ( $this->doingBugIds, $bugId );
            $this->doingBugIds = array_unique ( $this->doingBugIds );
         }
      }
   }

   /**
    * returns true if the issue is done like it is defined in the profile preference
    *
    * @param $bugId
    * @return bool
    */
   private function checkIssueIsDoneById ( $bugId )
   {
      $roadmapDb = new roadmap_db();
      $this->issueIsDone = false;

      $bugStatus = bug_get_field ( $bugId, 'status' );
      $roadmapProfile = $roadmapDb->dbGetRoadmapProfile ( $this->profileId );
      $dbRaodmapStatus = $roadmapProfile[ 3 ];
      $roadmapStatusArray = explode ( ';', $dbRaodmapStatus );

      foreach ( $roadmapStatusArray as $roadmapStatus )
      {
         if ( $bugStatus == $roadmapStatus )
         {
            $this->issueIsDone = true;
         }
      }
   }

   private function calcDoneEta ()
   {
      $this->doneEta = 0;
      $doneBugIds = $this->getDoneBugIds ();
      if ( $this->getEtaIsSet () )
      {
         foreach ( $doneBugIds as $doneBugId )
         {
            $this->doneEta += $this->getSingleEta ( $doneBugId );
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

   private function calcScaledData ()
   {
      # object initialization
      $roadmapDb = new roadmap_db();

      # variables
      $roadmapProfiles = $roadmapDb->dbGetRoadmapProfiles ();
      $useEta = $this->getEtaIsSet ();
      $allBugCount = count ( $this->bugIds );
      $profileCount = count ( $roadmapProfiles );
      $sumProfileEffort = $roadmapDb->dbGetSumProfileEffort ();

      $wholeProgress = 0;
      # iterate through profiles
      for ( $index = 0; $index < $profileCount; $index++ )
      {
         $roadmapProfile = $roadmapProfiles[ $index ];
         $tProfileId = $roadmapProfile[ 0 ];
         $this->setProfileId ( $tProfileId );
         # effort factor
         $tProfileEffort = $roadmapProfile[ 5 ];
         # uniform distribution when no effort is set
         if ( $sumProfileEffort == 0 )
         {
            $tProfileEffortFactor = ( 1 / $profileCount );
         }
         else
         {
            $tProfileEffortFactor = round ( ( $tProfileEffort / $sumProfileEffort ), 2 );
         }
         # bug data
         $doneBugIds = $this->getDoneBugIds ();
         $tDoneBugAmount = count ( $doneBugIds );
         if ( $useEta )
         {
            # calculate eta for profile
            $fullEta = ( $this->getFullEta () ) * $profileCount;
            $doneEta = 0;
            foreach ( $doneBugIds as $doneBugId )
            {
               $doneEta += $this->getSingleEta ( $doneBugId );
            }
            $doneEtaPercent = ( ( $doneEta / ( $fullEta ) ) * 100 );
            $doneEtaPercent = $doneEtaPercent * $profileCount * $tProfileEffortFactor;
            $wholeProgress += $doneEtaPercent;
            $profileHash = $tProfileId . ';' . $doneEtaPercent;
         }
         else
         {
            $tVersionProgress = ( $tDoneBugAmount / $allBugCount );
            $progessDonePercent = ( $tVersionProgress * 100 * $tProfileEffortFactor );
            $wholeProgress += $progessDonePercent;
            $profileHash = $tProfileId . ';' . $progessDonePercent;
         }

         array_push ( $this->profileHashArray, $profileHash );
         $this->resetDoneBugIds ();
         $this->setProfileId ( -1 );
      }

      $this->progressPercent = $wholeProgress;
   }

   public function getProfileHashArray ()
   {
      $this->calcScaledData ();
      return $this->profileHashArray;
   }

   public function getSclaedProgressPercent ()
   {
      return $this->progressPercent;
   }
}