<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProApi.php' );

/**
 * testclass for class rProfile
 *
 * @requires insert example data from file "mantis_plugin_roadmappro_profile_table.csv" into the database
 */
class rProApiTest extends PHPUnit_Framework_TestCase
{
   /**
    * this test covers functionality of methods
    * - getProfileEnumIds
    */
   public function testGetProfileEnumIds ()
   {
      $profileIds = [
         0 => 1,
         1 => 2,
         2 => 3,
         3 => 4,
         4 => 5
      ];

      $this->assertEquals ( $profileIds, rProApi::getProfileEnumIds () );
   }

   /**
    * this test covers functionality of methods
    * - getProfileEnumNames
    */
   public function testGetProfileEnumNames ()
   {
      $profileNames = [
         0 => 'Analyse fertig',
         1 => 'Verifikation fertig',
         2 => 'Bearbeitung fertig',
         3 => 'Analyse',
         4 => 'In Bearbeitung'
      ];

      $this->assertEquals ( $profileNames, rProApi::getProfileEnumNames () );
   }
}
