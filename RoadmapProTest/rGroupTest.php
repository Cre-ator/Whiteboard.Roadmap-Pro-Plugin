<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rGroup.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProApi.php' );

/**
 * testclass for class rGroup
 */
class rGroupTest extends PHPUnit_Framework_TestCase
{
   /**
    * global group id to identify test group
    * @var integer
    */
   private static $testGroupId;

   /**
    * this test covers functionality of methods
    * - dbInitGroupById
    * - setGroupName
    * - setGroupProfiles
    * - triggerInsertIntoDb
    *    - dbInsertGroup
    * - getGroupId
    * - getGroupName
    * - getGroupProfiles
    */
   public function testTriggerInsertIntoDb ()
   {
      $groupName = 'Testgruppe';
      $groupProfiles = '1;2;3';

      # empty group object.
      $group = new rGroup();

      # set group data.
      $group->setGroupName ( $groupName );
      $group->setGroupProfiles ( $groupProfiles );

      # insert into db.
      $group->triggerInsertIntoDb ();
      self::$testGroupId = $group->getGroupId ();

      # check inserted group.
      $testGroup = new rGroup( self::$testGroupId );
      $this->assertEquals ( $groupName, $testGroup->getGroupName () );
      $this->assertEquals ( $groupProfiles, $testGroup->getGroupProfiles () );
   }

   /**
    * this test covers functionality of methods
    * - dbInitGroupById
    * - setGroupName
    * - setGroupProfiles
    * - triggerUpdateInDb
    *    - dbUpdateGroup
    * - getGroupId
    * - getGroupName
    * - getGroupProfiles
    */
   public function testTriggerUpdateInDb ()
   {
      $newGroupName = 'TestTestgruppe';
      $newGroupProfiles = '1;2';

      # group object.
      $group = new rGroup( self::$testGroupId );

      # set new parameters.
      $group->setGroupName ( $newGroupName );
      $group->setGroupProfiles ( $newGroupProfiles );

      # update group in database.
      $group->triggerUpdateInDb ();

      # check updated group.
      $testGroup = new rGroup( self::$testGroupId );
      $this->assertEquals ( $newGroupName, $testGroup->getGroupName () );
      $this->assertEquals ( $newGroupProfiles, $testGroup->getGroupProfiles () );
   }

   /**
    * this test covers functionality of methods
    * - dbInitGroupById
    * - triggerDeleteFromDb
    *    - dbDeleteGroup
    * - getGroupName
    * - getGroupProfiles
    */
   public function testTriggerDeleteFromDb ()
   {
      # group object.
      $group = new rGroup( self::$testGroupId );
      $group->triggerDeleteFromDb ();

      # check deleted
      $testGroup = new rGroup( self::$testGroupId );
      $this->assertEquals ( null, $testGroup->getGroupName () );
      $this->assertEquals ( null, $testGroup->getGroupProfiles () );
   }
}
