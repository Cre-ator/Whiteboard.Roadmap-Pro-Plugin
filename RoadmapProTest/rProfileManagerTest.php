<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProfile.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProfileManager.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProApi.php' );

/**
 * testclass for class rGroupManager
 *
 * @requires insert example data from file "mantis_plugin_roadmappro_profile_table.csv" into the database
 */
class rProfileManagerTest extends PHPUnit_Framework_TestCase
{
   /**
    * this test covers functionality of methods
    * - getRProfileIds
    */
   public function testGetRProfileIds ()
   {
      $desiredProfileIds = [
         0 => 1,
         1 => 2,
         2 => 3,
         3 => 4,
         4 => 5
      ];
      $this->assertEquals ( $desiredProfileIds, rProfileManager::getRProfileIds () );
   }

   /**
    * this test covers functionality of methods
    * - getRProfiles
    */
   public function testGetRProfiles ()
   {
      $profileIdOne = 1;
      $profileIdTwo = 2;
      $profileIdThree = 3;
      $profileIdFour = 4;
      $profileIdFive = 5;
      $profileIds = [
         0 => $profileIdOne,
         1 => $profileIdTwo,
         2 => $profileIdThree,
         3 => $profileIdFour,
         4 => $profileIdFive
      ];

      $desiredProfileOne = new rProfile( $profileIdOne );
      $desiredProfileTwo = new rProfile( $profileIdTwo );
      $desiredProfileThree = new rProfile( $profileIdThree );
      $desiredProfileFour = new rProfile( $profileIdFour );
      $desiredProfileFive = new rProfile( $profileIdFive );
      $desiredProfiles = [
         0 => $desiredProfileOne,
         1 => $desiredProfileTwo,
         2 => $desiredProfileThree,
         3 => $desiredProfileFour,
         4 => $desiredProfileFive
      ];

      $this->assertEquals ( $desiredProfiles, rProfileManager::getRProfiles ( $profileIds ) );
   }

   /**
    * this test covers functionality of methods
    * - getSumRProfileEffort
    */
   public function testGetSumRProfileEffort ()
   {
      $desiredSumEffort = 180;
      $this->assertEquals ( $desiredSumEffort, rProfileManager::getSumRProfileEffort () );
   }
}
