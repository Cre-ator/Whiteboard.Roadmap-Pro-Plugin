<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rGroup.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rGroupManager.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProApi.php' );

/**
 * testclass for class rGroupManager
 *
 * @requires insert example data from file "mantis_plugin_roadmappro_profilegroup_table.csv" into the database
 */
class rGroupManagerTest extends PHPUnit_Framework_TestCase
{
   /**
    * this test covers functionality of methods
    * - getRGroupIds
    */
   public function testGetRGroupIds ()
   {
      $desiredGroupIds = [
         0 => 1,
         1 => 2
      ];
      $this->assertEquals ( $desiredGroupIds, rGroupManager::getRGroupIds () );
   }

   /**
    * this test covers functionality of methods
    * - getRGroups
    */
   public function testGetRGroups ()
   {
      $groupIdOne = 1;
      $groupIdTwo = 2;
      $groupIds = [
         0 => $groupIdOne,
         1 => $groupIdTwo
      ];

      $desiredGroupOne = new rGroup( $groupIdOne );
      $desiredGroupTwo = new rGroup( $groupIdTwo );
      $desiredGroups = [
         0 => $desiredGroupOne,
         1 => $desiredGroupTwo
      ];

      $this->assertEquals ( $desiredGroups, rGroupManager::getRGroups ( $groupIds ) );
   }
}
