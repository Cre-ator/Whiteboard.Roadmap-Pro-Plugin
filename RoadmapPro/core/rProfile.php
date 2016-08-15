<?php
require_once ( __DIR__ . '/rProApi.php' );

/**
 * profile class that represents a roadmap profile
 *
 * @author Stefan Schwarz
 */
class rProfile
{
   /**
    * @var integer
    */
   private $profileId;
   /**
    * @var string
    */
   private $profileName;
   /**
    * @var string
    */
   private $profileColor;
   /**
    * @var string
    */
   private $profileStatus;
   /**
    * @var integer
    */
   private $profilePriority;
   /**
    * @var integer
    */
   private $profileEffort;

   /**
    * rProfile constructor.
    * @param null $profileId
    */
   public function __construct ( $profileId = null )
   {
      if ( $profileId != null )
      {
         $this->profileId = $profileId;
         $this->dbInitProfileById ();
      }
   }

   /**
    * rProfile destructor.
    */
   public function __destruct ()
   {
      // TODO: Implement __destruct() method.
   }

   /**
    * @return int
    */
   public function getProfileId ()
   {
      return $this->profileId;
   }

   /**
    * @return string
    */
   public function getProfileName ()
   {
      return $this->profileName;
   }

   /**
    * @param string $profileName
    */
   public function setProfileName ( $profileName )
   {
      $this->profileName = $profileName;
   }

   /**
    * @return string
    */
   public function getProfileColor ()
   {
      return $this->profileColor;
   }

   /**
    * @param string $profileColor
    */
   public function setProfileColor ( $profileColor )
   {
      $this->profileColor = $profileColor;
   }

   /**
    * @return string
    */
   public function getProfileStatus ()
   {
      return $this->profileStatus;
   }

   /**
    * @param string $profileStatus
    */
   public function setProfileStatus ( $profileStatus )
   {
      $this->profileStatus = $profileStatus;
   }

   /**
    * @return int
    */
   public function getProfilePriority ()
   {
      return $this->profilePriority;
   }

   /**
    * @param int $profilePriority
    */
   public function setProfilePriority ( $profilePriority )
   {
      $this->profilePriority = $profilePriority;
   }

   /**
    * @return int
    */
   public function getProfileEffort ()
   {
      return $this->profileEffort;
   }

   /**
    * @param int $profileEffort
    */
   public function setProfileEffort ( $profileEffort )
   {
      $this->profileEffort = $profileEffort;
   }

   /**
    * insert object data into new database row
    */
   public function triggerInsertIntoDb ()
   {
      if (
         ( $this->profileName != null ) &&
         ( $this->profileColor != null ) &&
         ( $this->profileStatus != null ) &&
         ( $this->profilePriority != null ) &&
         ( $this->profileEffort != null )
      )
      {
         $this->dbInsertProfile ();
      }
   }

   /**
    * update selected database row with object data
    */
   public function triggerUpdateInDb ()
   {
      if (
         ( $this->profileId != null ) &&
         is_numeric ( $this->profileId ) &&
         ( $this->profileName != null ) &&
         ( $this->profileColor != null ) &&
         ( $this->profileStatus != null ) &&
         ( $this->profilePriority != null ) &&
         ( $this->profileEffort != null )
      )
      {
         $this->dbUpdateProfile ();
      }
   }

   /**
    * remove selected database row
    */
   public function triggerDeleteFromDb ()
   {
      if (
         ( $this->profileId != null ) &&
         is_numeric ( $this->profileId )
      )
      {
         $this->dbDeleteProfile ();
      }
   }

   /**
    * initializes a profile object with database data
    */
   private function dbInitProfileById ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT DISTINCT * FROM mantis_plugin_RoadmapPro_profile_table WHERE id=' . $this->profileId;

      $result = $mysqli->query ( $query );
      $dbProfileRow = mysqli_fetch_row ( $result );
      $mysqli->close ();

      $this->profileName = $dbProfileRow[ 1 ];
      $this->profileColor = $dbProfileRow[ 2 ];
      $this->profileStatus = $dbProfileRow[ 3 ];
      $this->profilePriority = $dbProfileRow[ 4 ];
      $this->profileEffort = $dbProfileRow[ 5 ];
   }

   /**
    * insert new profile row
    */
   private function dbInsertProfile ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'INSERT INTO mantis_plugin_RoadmapPro_profile_table ( id, profile_name, profile_color, profile_status, profile_prio, profile_effort )
         SELECT null,\'' . $this->profileName . '\',\'' . $this->profileColor . '\',\'' . $this->profileStatus . '\',' . (int)$this->profilePriority . ',' . (int)$this->profileEffort . '
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM mantis_plugin_RoadmapPro_profile_table
         WHERE profile_name=\'' . $this->profileName . '\')';

      $mysqli->query ( $query );
      $this->profileId = $mysqli->insert_id;
      $mysqli->close ();
   }

   /**
    * update profile row
    */
   private function dbUpdateProfile ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'UPDATE mantis_plugin_RoadmapPro_profile_table
         SET profile_name=\'' . $this->profileName . '\',profile_color=\'' . $this->profileColor . '\', 
         profile_status=\'' . $this->profileStatus . '\',profile_prio=' . (int)$this->profilePriority . ', 
         profile_effort=' . (int)$this->profileEffort . '
         WHERE id=' . $this->profileId;

      $mysqli->query ( $query );
      $mysqli->close ();
   }

   /**
    * delete profile row
    */
   private function dbDeleteProfile ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'DELETE FROM mantis_plugin_RoadmapPro_profile_table WHERE id=' . $this->profileId;

      $mysqli->query ( $query );
      $mysqli->close ();
   }
}