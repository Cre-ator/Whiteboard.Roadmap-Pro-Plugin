<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/roadmap_db.php' );

/**
 * Created by PhpStorm.
 * User: stefan.schwarz
 * Date: 14.07.2016
 * Time: 15:19
 */
class roadmap_dbTest extends PHPUnit_Framework_TestCase
{
   public function testGetBugIdsByProjectAndVersion ()
   {
      $roadmap_db = new roadmap_db();
      /** valid variables */
      $project_id = 23;
      $version_name = 'Specification Management 1.1.x';
      $bug_ids = [ 0 => 30, 1 => 31 ];

      /** invalid variables */
      $invalid_version_name = 'specification management 1.1.';
      $invalid_bug_ids = [ 0 => 30, 1 => 32 ];

      /** valid */
      $this->assertInternalType ( 'array', $roadmap_db->dbGetBugIdsByProjectAndVersion ( $project_id, $version_name ) );
      $this->assertEquals ( $bug_ids, $roadmap_db->dbGetBugIdsByProjectAndVersion ( $project_id, $version_name ) );
      /** invalid */
      $this->assertNotEquals ( $invalid_bug_ids, $roadmap_db->dbGetBugIdsByProjectAndVersion ( $project_id, $version_name ) );
      $this->assertNotEquals ( $bug_ids, $roadmap_db->dbGetBugIdsByProjectAndVersion ( $project_id, $invalid_version_name ) );
      $this->assertEquals ( null, $roadmap_db->dbGetBugIdsByProjectAndVersion ( $project_id, '' ) );
      $this->assertEquals ( null, $roadmap_db->dbGetBugIdsByProjectAndVersion ( 0, $version_name ) );
      $this->assertEquals ( null, $roadmap_db->dbGetBugIdsByProjectAndVersion ( 250, '' ) );
      $this->assertEquals ( null, $roadmap_db->dbGetBugIdsByProjectAndVersion ( 0, '' ) );
      $this->assertEquals ( null, $roadmap_db->dbGetBugIdsByProjectAndVersion ( 'hallo', '' ) );
      $this->assertEquals ( null, $roadmap_db->dbGetBugIdsByProjectAndVersion ( 0, 0 ) );
      $this->assertEquals ( null, $roadmap_db->dbGetBugIdsByProjectAndVersion ( '', 0 ) );
      $this->assertEquals ( null, $roadmap_db->dbGetBugIdsByProjectAndVersion ( 'hallo', 0 ) );
   }

   public function testGetRoadmapProfiles ()
   {
      $roadmap_db = new roadmap_db();
      $profile_a = [ 0 => 3, 1 => 'Analyse fertig', 2 => 'FFC67A', 3 => '30;40;50;80;90', 4 => 2 ];
      $profile_b = [ 0 => 4, 1 => 'Bearbeitung fertig', 2 => 'BAFF61', 3 => '80;90', 4 => 3 ];
      $profile_c = [ 0 => 4, 1 => 'Bearbeitung fertig', 2 => 'BAFF61', 3 => '70;90', 4 => 3 ];
      $result_valid = [ 0 => $profile_a, 1 => $profile_b ];
      $result_invalid = [ 0 => $profile_a, 1 => $profile_c ];

      /** valid */
      $this->assertInternalType ( 'array', $roadmap_db->dbGetRoadmapProfiles () );
      $this->assertEquals ( $result_valid, $roadmap_db->dbGetRoadmapProfiles () );
      /** invalid */
      $this->assertNotEquals ( $result_invalid, $roadmap_db->dbGetRoadmapProfiles () );
   }

   public function testGetRoadmapProfile ()
   {
      $roadmap_db = new roadmap_db();
      $profile_id = 3;
      $result_valid = [ 0 => 3, 1 => 'Analyse fertig', 2 => 'FFC67A', 3 => '30;40;50;80;90', 4 => 2 ];
      $result_invalid_a = [ 0 => 2, 1 => 'Analyse fertig', 2 => 'E5FF63', 3 => '300;40;50;80;90', 4 => 2 ];
      $result_invalid_b = [ 0 => 1, 1 => 'Analüse fertig', 2 => 'E5FF63', 3 => '30;40;50;80;90', 4 => 2 ];
      $result_invalid_c = [ 0 => 1, 1 => 'Analyse fertig', 2 => '1E5FF63', 3 => '30;40;50;80;90', 4 => 2 ];
      $result_invalid_d = [ 0 => 1, 1 => 'Analyse fertig', 2 => 'E5FF63', 3 => '300;40;50;80;90', 4 => 2 ];
      $result_invalid_e = [ 0 => 1, 1 => 'Analyse fertig', 2 => 'E5FF63', 3 => '300;40;50;80;90', 4 => 3 ];

      $db_result = $roadmap_db->dbGetRoadmapProfile ( $profile_id );
      /** valid */
      $this->assertInternalType ( 'array', $db_result );
      $this->assertEquals ( $result_valid, $db_result );
      /** invalid */
      $this->assertNotEquals ( $result_invalid_a, $db_result );
      $this->assertNotEquals ( $result_invalid_b, $db_result );
      $this->assertNotEquals ( $result_invalid_c, $db_result );
      $this->assertNotEquals ( $result_invalid_d, $db_result );
      $this->assertNotEquals ( $result_invalid_e, $db_result );
      $this->assertEquals ( null, $roadmap_db->dbGetRoadmapProfile ( '1 AND profile_color=\'E5FF63\'' ) );
      $this->assertEquals ( null, $roadmap_db->dbGetRoadmapProfile ( '' ) );
      $this->assertEquals ( null, $roadmap_db->dbGetRoadmapProfile ( 'hallo' ) );
      $this->assertEquals ( null, $roadmap_db->dbGetRoadmapProfile ( 9999 ) );
   }

   public function testInsertDeleteProfile ()
   {
      $roadmap_db = new roadmap_db();
      /** variables */
      $profile_name = 'Testprofil';
      $profile_color = '000000';
      $profile_status = '10;20;50';
      $profile_priority = 10;

      $profile_id = $roadmap_db->dbInsertProfile ( $profile_name, $profile_color, $profile_status, $profile_priority );
      $profile = [ 0 => $profile_id, 1 => 'Testprofil', 2 => '000000', 3 => '10;20;50', 4 => 10 ];

      /** get profile from tested function @testGetRoadmapProfile */
      $db_result = $roadmap_db->dbGetRoadmapProfile ( $profile_id );
      /** valid */
      $this->assertEquals ( $profile, $db_result );

      /** delete profile */
      $roadmap_db->dbDeleteProfile ( $profile_id );
      /** get profile from tested function @testGetRoadmapProfile */
      $db_result = $roadmap_db->dbGetRoadmapProfile ( $profile_id );
      /** valid */
      $this->assertEquals ( null, $db_result );
   }
}
