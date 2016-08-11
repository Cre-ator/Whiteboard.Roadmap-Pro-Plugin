<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rThreshold.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rThresholdManager.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProApi.php' );

/**
 * testclass for class rGroupManager
 *
 * @requires insert example data from file "mantis_plugin_roadmappro_etathreshold_table.csv" into the database
 */
class rThresholdManagerTest extends PHPUnit_Framework_TestCase
{
   /**
    * this test covers functionality of methods
    * - getRThresholdIds
    */
   public function testGetRProfileIds ()
   {
      $desiredThresholdIds = [
         0 => 1,
         1 => 2,
         2 => 3
      ];
      $this->assertEquals ( $desiredThresholdIds, rThresholdManager::getRThresholdIds () );
   }
}
