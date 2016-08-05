<?php

/**
 * Created by PhpStorm.
 * User: stefan.schwarz
 * Date: 14.07.2016
 * Time: 17:52
 */
class roadmap_db
{
   private $dbPath;
   private $dbUser;
   private $dbPass;
   private $dbName;

   private $mysqli;

   function __construct ()
   {
      $this->dbPath = config_get ( 'hostname' );
      $this->dbUser = config_get ( 'db_username' );
      $this->dbPass = config_get ( 'db_password' );
      $this->dbName = config_get ( 'database_name' );

      $this->mysqli = new mysqli( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );
   }

   /**
    * returns all assigned bug ids to a given target version
    *
    * @param $projectId
    * @param $versionName
    * @return array|null
    */
   public function dbGetBugIdsByProjectAndTargetVersion ( $projectId, $versionName )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $bugIds = null;
      if ( is_numeric ( $projectId ) )
      {
         $query = /** @lang sql */
            "SELECT id FROM mantis_bug_table
            WHERE target_version = '" . $versionName . "'
            AND project_id = " . $projectId;

         $result = $this->mysqli->query ( $query );

         if ( 0 != $result->num_rows )
         {
            while ( $row = $result->fetch_row () )
            {
               $bugIds[] = $row[ 0 ];
            }
         }
      }
      $this->mysqli->close ();

      return $bugIds;
   }

   # ++++++++++++++++++++++++++++ groups +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

   /**
    * get alle roadmap groups
    *
    * @return array|null
    */
   public function dbGetGroups ()
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "SELECT * FROM mantis_plugin_RoadmapPro_profilegroup_table";

      $result = $this->mysqli->query ( $query );

      $groups = null;
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row () )
         {
            $groups[] = $row;
         }
      }

      $this->mysqli->close ();

      return $groups;
   }

   public function dbGetGroup ( $groupId )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $group = null;
      if ( is_numeric ( $groupId ) )
      {
         $query = /** @lang sql */
            "SELECT DISTINCT * FROM mantis_plugin_RoadmapPro_profilegroup_table
            WHERE id = " . $groupId;

         $result = $this->mysqli->query ( $query );

         $group = mysqli_fetch_row ( $result );
      }

      $this->mysqli->close ();

      return $group;
   }

   public function dbInsertGroup ( $groupName, $groupProfiles )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "INSERT INTO mantis_plugin_RoadmapPro_profilegroup_table ( id, group_name, group_profiles )
            SELECT null,'" . $groupName . "','" . $groupProfiles . "'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_profilegroup_table
            WHERE group_name = '" . $groupName . "')";

      $this->mysqli->query ( $query );
      $groupId = $this->mysqli->insert_id;
      $this->mysqli->close ();

      return $groupId;
   }

   public function dbUpdateGroup ( $groupId, $groupName, $groupProfiles )
   {
      if ( is_numeric ( $groupId ) )
      {
         $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

         $query = /** @lang sql */
            "UPDATE mantis_plugin_RoadmapPro_profilegroup_table
               SET group_name = '" . $groupName . "', group_profiles= '" . $groupProfiles . "'
               WHERE id=" . $groupId;

         $this->mysqli->query ( $query );
         $this->mysqli->close ();
      }
   }

   public function dbDeleteGroup ( $groupId )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      if ( is_numeric ( $groupId ) )
      {
         $query = /** @lang sql */
            "DELETE FROM mantis_plugin_RoadmapPro_profilegroup_table
            WHERE id = " . $groupId;

         $this->mysqli->query ( $query );
      }

      $this->mysqli->close ();
   }

   # +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

   # ++++++++++++++++++++++++++++ profiles +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   /**
    * inserts a new roadmap profile if there isnt a dupicate by name
    *
    * @param $profileName
    * @param $profileColor
    * @param $profileStatus
    * @param $profilePriority
    * @param $profileEffort
    * @return mixed
    */
   public function dbInsertProfile ( $profileName, $profileColor, $profileStatus, $profilePriority, $profileEffort )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "INSERT INTO mantis_plugin_RoadmapPro_profile_table ( id, profile_name, profile_color, profile_status, profile_prio, profile_effort )
            SELECT null,'" . $profileName . "','" . $profileColor . "','" . $profileStatus . "'," . (int)$profilePriority . "," . (int)$profileEffort . "
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_profile_table
            WHERE profile_name = '" . $profileName . "')";

      $this->mysqli->query ( $query );
      $profileId = $this->mysqli->insert_id;
      $this->mysqli->close ();

      return $profileId;
   }

   public function dbUpdateProfile ( $profileId, $profileName, $profileColor, $profileStatus, $profilePriority, $profileEffort )
   {
      if ( is_numeric ( $profileId ) )
      {
         $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

         $query = /** @lang sql */
            "UPDATE mantis_plugin_RoadmapPro_profile_table
               SET profile_name = '" . $profileName . "', profile_color= '" . $profileColor . "', 
                  profile_status= '" . $profileStatus . "', profile_prio= '" . $profilePriority . "', 
                  profile_effort= '" . $profileEffort . "'
               WHERE id=" . $profileId;

         $this->mysqli->query ( $query );
         $this->mysqli->close ();
      }
   }

   /**
    * get all roadmap profiles
    *
    * @return array|null
    */
   public function dbGetProfiles ()
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "SELECT * FROM mantis_plugin_RoadmapPro_profile_table ORDER BY profile_prio ASC";

      $result = $this->mysqli->query ( $query );

      $profiles = null;
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row () )
         {
            $profiles[] = $row;
         }
      }

      $this->mysqli->close ();

      return $profiles;
   }

   /**
    * get a specific roadmap profile
    *
    * @param $profileId
    * @return array|null
    */
   public function dbGetProfile ( $profileId )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $profile = null;
      if ( is_numeric ( $profileId ) )
      {
         $query = /** @lang sql */
            "SELECT DISTINCT * FROM mantis_plugin_RoadmapPro_profile_table
            WHERE id = " . $profileId;

         $result = $this->mysqli->query ( $query );

         $profile = mysqli_fetch_row ( $result );
      }

      $this->mysqli->close ();

      return $profile;
   }

   /**
    * delete a roadmap profile by its primary id
    *
    * @param $profileId
    */
   public function dbDeleteProfile ( $profileId )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      if ( is_numeric ( $profileId ) )
      {
         $query = /** @lang sql */
            "DELETE FROM mantis_plugin_RoadmapPro_profile_table
            WHERE id = " . $profileId;

         $this->mysqli->query ( $query );
      }

      $this->mysqli->close ();
   }

   /**
    * returns the sum of all profile efforts
    *
    * @return int
    */
   public function dbGetSumProfileEffort ()
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );
      $query = /** @lang sql */
         "SELECT SUM(profile_effort) FROM mantis_plugin_roadmappro_profile_table";

      $result = $this->mysqli->query ( $query );

      $sumProfileEffort = 0;
      if ( 0 != $result->num_rows )
      {
         $sumProfileEffort = $result->fetch_row ()[ 0 ];
      }
      $this->mysqli->close ();

      return $sumProfileEffort;
   }
   # +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

   /**
    * Reset all plugin-related data
    *
    * - config entries
    * - database entities
    */
   public function dbResetPlugin ()
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_profile_table";

      $this->mysqli->query ( $query );

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_eta_table";

      $this->mysqli->query ( $query );

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_unit_table";

      $this->mysqli->query ( $query );

      $query = /** @lang sql */
         "DELETE FROM mantis_config_table
            WHERE config_id LIKE 'plugin_RoadmapPro%'";

      $this->mysqli->query ( $query );

      $this->mysqli->close ();

      print_successful_redirect ( 'manage_plugin_page.php' );
   }

   /**
    * insert a new eta-key-value-pair
    *
    * @param $key
    * @param $value
    * @return mixed
    */
   private function dbInsertEtaKeyValue ( $key, $value )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "INSERT INTO mantis_plugin_RoadmapPro_eta_table ( id, eta_config_value, eta_user_value )
            SELECT null,'" . $key . "','" . $value . "'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_eta_table
            WHERE eta_config_value = '" . $key . "')";

      $this->mysqli->query ( $query );
      $etaId = $this->mysqli->insert_id;
      $this->mysqli->close ();

      return $etaId;
   }

   /**
    * update a eta-key-value-pair
    *
    * @param $key
    * @param $value
    */
   public function dbUpdateEtaUserValue ( $key, $value )
   {
      if ( is_numeric ( $key ) )
      {
         if ( $this->dbCheckEtaKeyIsSet ( $key ) )
         {
            $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );
            $query = /** @lang sql */
               "UPDATE mantis_plugin_RoadmapPro_eta_table
            SET eta_user_value='" . $value . "'
            WHERE eta_config_value = '" . $key . "'";

            $this->mysqli->query ( $query );
            $this->mysqli->close ();
         }
         else
         {
            $this->dbInsertEtaKeyValue ( $key, $value );
         }
      }
   }

   /**
    * checks of a key is already set in the database
    *
    * @param $key
    * @return bool
    */
   private function dbCheckEtaKeyIsSet ( $key )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "SELECT id FROM mantis_plugin_RoadmapPro_eta_table
            WHERE eta_config_value = '" . $key . "'";

      $result = $this->mysqli->query ( $query );
      $this->mysqli->close ();

      if ( 0 != $result->num_rows )
      {
         return true;
      }
      else
      {
         return false;
      }
   }

   public function dbGetEtaRowByKey ( $key )
   {
      if ( !is_numeric ( $key ) )
      {
         return null;
      }

      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "SELECT * FROM mantis_plugin_RoadmapPro_eta_table
            WHERE eta_config_value = '" . $key . "'";

      $result = $this->mysqli->query ( $query );

      $etaRow = null;
      if ( 0 != $result->num_rows )
      {
         $etaRow = $result->fetch_row ();
      }
      $this->mysqli->close ();

      return $etaRow;
   }

   public function dbGetEtaThresholds ()
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );
      $query = /** @lang sql */
         "SELECT * FROM mantis_plugin_RoadmapPro_etathreshold_table";

      $result = $this->mysqli->query ( $query );

      $etaThresholdRows = null;
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row () )
         {
            $etaThresholdRows[] = $row;
         }
      }
      $this->mysqli->close ();

      return $etaThresholdRows;
   }

   public function dbInsertEtaThresholdValue ( $from, $to, $unit, $factor )
   {
      if ( is_numeric ( $from ) && is_numeric ( $to ) && is_numeric ( $factor ) )
      {
         $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

         $query = /** @lang sql */
            "INSERT INTO mantis_plugin_RoadmapPro_etathreshold_table ( id, eta_thr_from, eta_thr_to, eta_thr_unit, eta_thr_factor )
            SELECT null,'" . $from . "','" . $to . "','" . $unit . "','" . $factor . "'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_etathreshold_table
            WHERE eta_thr_unit = '" . $unit . "')";

         $this->mysqli->query ( $query );
         $etaId = $this->mysqli->insert_id;
         $this->mysqli->close ();

         return $etaId;
      }

      return null;
   }

   public function dbUpdateEtaThresholdValue ( $id, $from, $to, $unit, $factor )
   {
      if ( is_numeric ( $id ) && is_numeric ( $from ) && is_numeric ( $to ) && is_numeric ( $factor ) )
      {
         $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );
         $query = /** @lang sql */
            "UPDATE mantis_plugin_RoadmapPro_etathreshold_table
               SET eta_thr_from='" . $from . "',eta_thr_to='" . $to . "',eta_thr_unit='" . $unit . "',eta_thr_factor='" . $factor . "'
               WHERE id = '" . $id . "'";

         $this->mysqli->query ( $query );
         $this->mysqli->close ();
      }
   }

   public function dbDeleteEtaThreshold ( $id )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      if ( is_numeric ( $id ) )
      {
         $query = /** @lang sql */
            "DELETE FROM mantis_plugin_RoadmapPro_etathreshold_table
            WHERE id = " . $id;

         $this->mysqli->query ( $query );
      }

      $this->mysqli->close ();
   }
}