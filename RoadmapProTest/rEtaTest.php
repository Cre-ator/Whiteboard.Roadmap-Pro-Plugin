<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rEta.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProApi.php' );

/**
 * testclass for class rEta
 */
class rEtaTest extends PHPUnit_Framework_TestCase
{
   /**
    * this test covers functionality of methods
    * - dbInitEtaByConfigValue
    * - setEtaUser
    * - getEtaIsSet
    *    - dbCheckEtaIsSet
    * - triggerInsertIntoDb
    *    - dbInsertEta
    * - setEtaConfig
    */
   public function testTriggerInsertIntoDb ()
   {
      $etaConfigValue = 990;
      $etaUserValue = 9.9;
      $eta = new rEta( $etaConfigValue );
      $eta->setEtaUser ( $etaUserValue );

      # ensure that eta isnt set now.
      $this->assertEquals ( false, $eta->getEtaIsSet () );
      $eta->triggerInsertIntoDb ();

      # check set after insert.
      $this->assertEquals ( true, $eta->getEtaIsSet () );

      # change eta config.
      $dummyEtaConfig = 980;
      $eta->setEtaConfig ( $dummyEtaConfig );
      $this->assertEquals ( false, $eta->getEtaIsSet () );
   }

   /**
    * this test covers functionality of methods
    * - dbInitEtaByConfigValue
    * - getEtaUser
    * - setEtaUser
    * - triggerUpdateInDb
    *    - dbUpdateEta
    */
   public function testTriggerUpdateInDb ()
   {
      $etaConfigValue = 990;
      $actualEtaUserValue = 9.9;
      $newEtaUserValue = 9;

      # create rEta object with actual user value, then update to new user value.
      $eta = new rEta( $etaConfigValue );
      $this->assertEquals ( $actualEtaUserValue, $eta->getEtaUser () );
      $eta->setEtaUser ( $newEtaUserValue );
      $eta->triggerUpdateInDb ();

      # create new rEta object to test updated db value.
      $newEta = new rEta( $etaConfigValue );
      $this->assertEquals ( $newEtaUserValue, $newEta->getEtaUser () );

      # remove test row.
      $mysqli = rProApi::initializeDbConnection ();
      $query = /** @lang sql */
         'DELETE FROM mantis_plugin_RoadmapPro_eta_table WHERE eta_config_value=' . $etaConfigValue;
      $mysqli->query ( $query );

   }
}
