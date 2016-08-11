<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProfile.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProApi.php' );

/**
 * testclass for class rProfile
 */
class rProfileTest extends PHPUnit_Framework_TestCase
{
   /**
    * global profile id to identify test profile
    * @var integer
    */
   private static $testProfileId;

   /**
    * this test covers functionality of methods
    * - dbInitProfileById
    * - setProfileName, setProfileColor, setProfileStatus, setProfilePriority, setProfileEffort
    * - triggerInsertIntoDb
    *    - dbInsertProfile
    * - getProfileId, getProfileName, getProfileColor, getProfileStatus, getProfilePriority, getProfileEffort
    */
   public function testTriggerInsertIntoDb ()
   {
      $profileName = 'Testprofil';
      $profileColor = 'ABCDEF';
      $profileStatus = '10;20';
      $profilePriority = '99';
      $profileEffort = '100';

      # empty profile object.
      $profile = new rProfile();

      # set profile data.
      $profile->setProfileName ( $profileName );
      $profile->setProfileColor ( $profileColor );
      $profile->setProfileStatus ( $profileStatus );
      $profile->setProfilePriority ( $profilePriority );
      $profile->setProfileEffort ( $profileEffort );

      # insert into db.
      $profile->triggerInsertIntoDb ();
      self::$testProfileId = $profile->getProfileId ();

      # check inserted profile
      $testProfile = new rProfile( self::$testProfileId );
      $this->assertEquals ( $profileName, $testProfile->getProfileName () );
      $this->assertEquals ( $profileColor, $testProfile->getProfileColor () );
      $this->assertEquals ( $profileStatus, $testProfile->getProfileStatus () );
      $this->assertEquals ( $profilePriority, $testProfile->getProfilePriority () );
      $this->assertEquals ( $profileEffort, $testProfile->getProfileEffort () );
   }

   /**
    * this test covers functionality of methods
    * - dbInitProfileById
    * - setProfileName, setProfileColor, setProfileStatus, setProfilePriority, setProfileEffort
    * - triggerUpdateInDb
    *    - dbUpdateProfile
    * - getProfileName, getProfileColor, getProfileStatus, getProfilePriority, getProfileEffort
    */
   public function testTriggerUpdateInDb ()
   {
      $newProfileName = 'TestTestprofil';
      $newProfileColor = 'FEDCBA';
      $newProfileStatus = '10';
      $newProfilePriority = '90';
      $newProfileEffort = '90';

      # profile object.
      $profile = new rProfile( self::$testProfileId );

      # set new parameters.
      $profile->setProfileName ( $newProfileName );
      $profile->setProfileColor ( $newProfileColor );
      $profile->setProfileStatus ( $newProfileStatus );
      $profile->setProfilePriority ( $newProfilePriority );
      $profile->setProfileEffort ( $newProfileEffort );

      # update profile in database.
      $profile->triggerUpdateInDb ();

      # check updated profile.
      $testProfile = new rProfile( self::$testProfileId );
      $this->assertEquals ( $newProfileName, $testProfile->getProfileName () );
      $this->assertEquals ( $newProfileColor, $testProfile->getProfileColor () );
      $this->assertEquals ( $newProfileStatus, $testProfile->getProfileStatus () );
      $this->assertEquals ( $newProfilePriority, $testProfile->getProfilePriority () );
      $this->assertEquals ( $newProfileEffort, $testProfile->getProfileEffort () );
   }

   /**
    * this test covers functionality of methods
    * - dbInitProfileById
    * - triggerDeleteFromDb
    *    - dbDeleteProfile
    * - getProfileName, getProfileColor, getProfileStatus, getProfilePriority, getProfileEffort
    */
   public function testTriggerDeleteFromDb ()
   {
      # profile object.
      $profile = new rProfile( self::$testProfileId );
      $profile->triggerDeleteFromDb ();

      # check deleted
      $testProfile = new rProfile( self::$testProfileId );
      $this->assertEquals ( null, $testProfile->getProfileName () );
      $this->assertEquals ( null, $testProfile->getProfileColor () );
      $this->assertEquals ( null, $testProfile->getProfileStatus () );
      $this->assertEquals ( null, $testProfile->getProfilePriority () );
      $this->assertEquals ( null, $testProfile->getProfileEffort () );
   }
}
