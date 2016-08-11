<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rThreshold.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProApi.php' );

/**
 * testclass for class rProfile
 */
class rThresholdTest extends PHPUnit_Framework_TestCase
{
   /**
    * global profile id to identify test threshold
    * @var integer
    */
   private static $testThresholdId;

   /**
    * this test covers functionality of methods
    * - dbInitThresholdById
    * - setThresholdFrom, setThresholdTo, setThresholdUnit, setThresholdFactor
    * - triggerInsertIntoDb
    *    - dbInsertThreshold
    * - getThresholdId, getThresholdFrom, getThresholdTo, getThresholdUnit, getThresholdFactor
    */
   public function testTriggerInsertIntoDb ()
   {
      $thresholdFrom = '0';
      $thresholdTo = '99';
      $thresholdUnit = 'Testeinheit';
      $thresholdFactor = '99';

      # empty threshold object.
      $threshold = new rThreshold();

      # set threshold data.
      $threshold->setThresholdFrom ( $thresholdFrom );
      $threshold->setThresholdTo ( $thresholdTo );
      $threshold->setThresholdUnit ( $thresholdUnit );
      $threshold->setThresholdFactor ( $thresholdFactor );

      # insert into db.
      $threshold->triggerInsertIntoDb ();
      self::$testThresholdId = $threshold->getThresholdId ();

      # check inserted threshold
      $testThreshold = new rThreshold( self::$testThresholdId );
      $this->assertEquals ( $thresholdFrom, $testThreshold->getThresholdFrom () );
      $this->assertEquals ( $thresholdTo, $testThreshold->getThresholdTo () );
      $this->assertEquals ( $thresholdUnit, $testThreshold->getThresholdUnit () );
      $this->assertEquals ( $thresholdFactor, $testThreshold->getThresholdFactor () );
   }

   /**
    * this test covers functionality of methods
    * - dbInitThresholdById
    * - setThresholdFrom, setThresholdTo, setThresholdUnit, setThresholdFactor
    * - triggerUpdateInDb
    *    - dbUpdateThreshold
    * - getThresholdId, getThresholdFrom, getThresholdTo, getThresholdUnit, getThresholdFactor
    */
   public function testTriggerUpdateInDb ()
   {
      $newThresholdFrom = '99';
      $newThresholdTo = '100';
      $newThresholdUnit = 'TestTesteinheit';
      $newThresholdFactor = '100';

      # threshold object.
      $threshold = new rThreshold( self::$testThresholdId );

      # set new parameters.
      $threshold->setThresholdFrom ( $newThresholdFrom );
      $threshold->setThresholdTo ( $newThresholdTo );
      $threshold->setThresholdUnit ( $newThresholdUnit );
      $threshold->setThresholdFactor ( $newThresholdFactor );

      # update threshold in database.
      $threshold->triggerUpdateInDb ();

      # check updated threshold.
      $testThreshold = new rThreshold( self::$testThresholdId );
      $this->assertEquals ( $newThresholdFrom, $testThreshold->getThresholdFrom () );
      $this->assertEquals ( $newThresholdTo, $testThreshold->getThresholdTo () );
      $this->assertEquals ( $newThresholdUnit, $testThreshold->getThresholdUnit () );
      $this->assertEquals ( $newThresholdFactor, $testThreshold->getThresholdFactor () );
   }

   /**
    * this test covers functionality of methods
    * - dbInitThresholdById
    * - triggerDeleteFromDb
    *    - dbDeleteThreshold
    * - getThresholdFrom, getThresholdTo, getThresholdUnit, getThresholdFactor
    */
   public function testTriggerDeleteFromDb ()
   {
      # threshold object.
      $threshold = new rThreshold( self::$testThresholdId );
      $threshold->triggerDeleteFromDb ();

      # check deleted
      $testThreshold = new rThreshold( self::$testThresholdId );
      $this->assertEquals ( null, $testThreshold->getThresholdFrom () );
      $this->assertEquals ( null, $testThreshold->getThresholdTo () );
      $this->assertEquals ( null, $testThreshold->getThresholdUnit () );
      $this->assertEquals ( null, $testThreshold->getThresholdFactor () );
   }
}
