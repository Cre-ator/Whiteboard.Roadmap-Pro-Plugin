<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/roadmap_db.php' );

/**
 * Created by PhpStorm.
 *
 * User: stefan.schwarz
 * Date: 14.07.2016
 * Time: 15:19
 *
 * IMPORTANT: #1 Add the following lines to your mantis config file first:
 *
 *                $g_eta_enum_string = '10:none,20:< 0,5 day,30:< 1 day,40:< 2-3 days,50:< 5 days';
 *                $g_status_enum_string = '10:new,20:feedback,30:acknowledged,50:assigned,80:resolved,85:confirmed,90:closed';
 *
 *
 *            #2 Then import the data sheets in the folder RoadmapProTest to your database!
 *               (Make sure, that there is no data in roadmappro plugin tables!)
 *
 *                mantis_plugin_roadmappro_eta_table.csv
 *                mantis_plugin_roadmappro_etathreshold_table.cs
 *                mantis_plugin_roadmappro_profile_table.cs
 */
class roadmap_dbTest extends PHPUnit_Framework_TestCase
{
   # profile table
   public function testDbGetProfiles ()
   {
      $roadmapDb = new roadmap_db();
      $prf1 = [ 0 => 1, 1 => 'Analyse fertig', 2 => 'FFCD85', 3 => '30;50;80;90', 4 => 1, 5 => 20 ];
      $prf2 = [ 0 => 2, 1 => 'Verifikation fertig', 2 => 'FFF494', 3 => '85;90', 4 => 3, 5 => 30 ];
      $prf3 = [ 0 => 3, 1 => 'Bearbeitung fertig', 2 => 'D2F5B0', 3 => '80;90', 4 => 2, 5 => 50 ];
      # sorted by [4] -> priority parameter
      $validArray = [ 0 => $prf1, 1 => $prf3, 2 => $prf2 ];

      $this->assertEquals ( $validArray, $roadmapDb->dbGetProfiles () );
   }

   public function testDbGetProfile ()
   {
      $roadmapDb = new roadmap_db();
      $profileId = 1;
      $prf1 = [ 0 => 1, 1 => 'Analyse fertig', 2 => 'FFCD85', 3 => '30;50;80;90', 4 => 1, 5 => 20 ];
      $prf2 = [ 0 => 2, 1 => 'Verifikation fertig', 2 => 'FFF494', 3 => '85;90', 4 => 3, 5 => 30 ];
      $prf3 = [ 0 => 3, 1 => 'Bearbeitung fertig', 2 => 'D2F5B0', 3 => '80;90', 4 => 2, 5 => 50 ];

      $this->assertEquals ( $prf1, $roadmapDb->dbGetProfile ( $profileId ) );
      $this->assertNotEquals ( $prf2, $roadmapDb->dbGetProfile ( $profileId ) );
      $this->assertNotEquals ( $prf3, $roadmapDb->dbGetProfile ( $profileId ) );
   }

   public function testDbUpdateProfile ()
   {
      $roadmapDb = new roadmap_db();
      $profileId = 1;

      $startName = 'Analyse fertig';
      $startColor = 'FFCD85';
      $startStatus = '30;50;80;90';
      $startPrio = 1;
      $startEffort = 20;

      $targetName = 'test';
      $targetColor = 'FFFAAA';
      $targetStatus = '30;50';
      $targetPrio = 9;
      $targetEffort = 100;

      $startPrf = [ 0 => 1, 1 => $startName, 2 => $startColor, 3 => $startStatus, 4 => $startPrio, 5 => $startEffort ];
      $targetPrf = [ 0 => 1, 1 => $targetName, 2 => $targetColor, 3 => $targetStatus, 4 => $targetPrio, 5 => $targetEffort ];

      $roadmapDb->dbUpdateProfile ( $profileId, $targetName, $targetColor, $targetStatus, $targetPrio, $targetEffort );
      $updatedPrf = $roadmapDb->dbGetProfile ( $profileId );
      $this->assertEquals ( $targetPrf, $updatedPrf );
      $roadmapDb->dbUpdateProfile ( $profileId, $startName, $startColor, $startStatus, $startPrio, $startEffort );
      $updatedPrf = $roadmapDb->dbGetProfile ( $profileId );
      $this->assertEquals ( $startPrf, $updatedPrf );
   }

   public function testDbInsertProfile ()
   {
      $roadmapDb = new roadmap_db();

      $targetName = 'test';
      $targetColor = 'FFFAAA';
      $targetStatus = '30;50';
      $targetPrio = 9;
      $targetEffort = 100;


      $prfIdAfterInsert = $roadmapDb->dbInsertProfile ( $targetName, $targetColor, $targetStatus, $targetPrio, $targetEffort );
      $targetPrf = [ 0 => $prfIdAfterInsert, 1 => $targetName, 2 => $targetColor, 3 => $targetStatus, 4 => $targetPrio, 5 => $targetEffort ];

      $prfAfterInsert = $roadmapDb->dbGetProfile ( $prfIdAfterInsert );
      $this->assertEquals ( $targetPrf, $prfAfterInsert );
   }

   public function testDbDeleteProfile ()
   {
      $roadmapDb = new roadmap_db();

      $roadmaps = $roadmapDb->dbGetProfiles ();
      $roadmapCount = count ( $roadmaps );
      $lastRoadmapId = $roadmaps[ ( $roadmapCount - 1 ) ][ 0 ];
      $roadmapDb->dbDeleteProfile ( $lastRoadmapId );

      $prf1 = [ 0 => 1, 1 => 'Analyse fertig', 2 => 'FFCD85', 3 => '30;50;80;90', 4 => 1, 5 => 20 ];
      $prf2 = [ 0 => 2, 1 => 'Verifikation fertig', 2 => 'FFF494', 3 => '85;90', 4 => 3, 5 => 30 ];
      $prf3 = [ 0 => 3, 1 => 'Bearbeitung fertig', 2 => 'D2F5B0', 3 => '80;90', 4 => 2, 5 => 50 ];
      # sorted by [4] -> priority parameter
      $validArray = [ 0 => $prf1, 1 => $prf3, 2 => $prf2 ];

      $this->assertEquals ( $validArray, $roadmapDb->dbGetProfiles () );
   }

   public function TestDbGetSumProfileEffort ()
   {
      $roadmapDb = new roadmap_db();

      $desiredEffort = 100;
      $actualEffort = $roadmapDb->dbGetSumProfileEffort ();

      $this->assertEquals ( $desiredEffort, $actualEffort );
   }
}
