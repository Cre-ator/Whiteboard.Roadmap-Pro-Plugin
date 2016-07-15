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
    * @param $project_id
    * @param $version_name
    * @return array|null
    */
   public function get_bug_ids_by_project_and_version ( $project_id, $version_name )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $bug_ids = null;
      if ( is_numeric ( $project_id ) )
      {
         $query = /** @lang sql */
            "SELECT id FROM mantis_bug_table
            WHERE target_version = '" . $version_name . "'
            AND project_id = " . $project_id;

         $result = $this->mysqli->query ( $query );

         if ( 0 != $result->num_rows )
         {
            while ( $row = $result->fetch_row () )
            {
               $bug_ids[] = $row[ 0 ];
            }
         }
      }
      $this->mysqli->close ();

      return $bug_ids;
   }

   /**
    * inserts a new roadmap profile if there isnt a dupicate by name
    *
    * @param $profile_name
    * @param $profile_color
    * @param $profile_status
    * @param $profile_priority
    * @return mixed
    */
   public function insert_profile ( $profile_name, $profile_color, $profile_status, $profile_priority )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "INSERT INTO mantis_plugin_RoadmapPro_profile_table ( id, profile_name, profile_color, profile_status, profile_prio )
            SELECT null,'" . $profile_name . "','" . $profile_color . "','" . $profile_status . "'," . (int)$profile_priority . "
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_profile_table
            WHERE profile_name = '" . $profile_name . "')";

      $this->mysqli->query ( $query );
      $profile_id = $this->mysqli->insert_id;
      $this->mysqli->close ();

      return $profile_id;
   }

   /**
    * get all roadmap profiles
    *
    * @return array|null
    */
   public function get_roadmap_profiles ()
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
    * @param $profile_id
    * @return array|null
    */
   public function get_roadmap_profile ( $profile_id )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $profile = null;
      if ( is_numeric ( $profile_id ) )
      {
         $query = /** @lang sql */
            "SELECT * FROM mantis_plugin_RoadmapPro_profile_table
            WHERE id = " . $profile_id;

         $result = $this->mysqli->query ( $query );

         $profile = mysqli_fetch_row ( $result );
      }

      $this->mysqli->close ();

      return $profile;
   }

   /**
    * delete a roadmap profile by its primary id
    *
    * @param $profile_id
    */
   public function delete_profile ( $profile_id )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      if ( is_numeric ( $profile_id ) )
      {
         $query = /** @lang sql */
            "DELETE FROM mantis_plugin_RoadmapPro_profile_table
            WHERE id = " . $profile_id;

         $this->mysqli->query ( $query );
      }

      $this->mysqli->close ();
   }

   /**
    * Reset all plugin-related data
    *
    * - config entries
    * - database entities
    */
   public function reset_plugin ()
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
    * returns true if there is a relationship for two given bug ids
    *
    * @param $bug_id_src
    * @param $bug_id_dest
    * @return bool
    */

   public function check_relationship ( $bug_id_src, $bug_id_dest )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "SELECT * FROM mantis_bug_relationship_table
            WHERE source_bug_id = " . $bug_id_src . "
            AND destination_bug_id = " . $bug_id_dest . "
            AND relationship_type = 2";

      $result = $this->mysqli->query ( $query );
      $this->mysqli->close ();

      if ( $result->num_rows > 0 )
      {
         return true;
      }
      else
      {
         return false;
      }
   }

   /**
    * get the relationship rows for two given bug ids
    *
    * @param $bug_id
    * @param $blocking
    * @return array|null
    */
   public function get_bug_relationship ( $bug_id, $blocking )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      /** get blocking bug ids */
      if ( $blocking == true )
      {
         $query = /** @lang sql */
            "SELECT destination_bug_id FROM mantis_bug_relationship_table
            WHERE source_bug_id = " . $bug_id . "
            AND relationship_type = 2";
      }
      /** get blocked bug ids */
      else
      {
         $query = /** @lang sql */
            "SELECT source_bug_id FROM mantis_bug_relationship_table
            WHERE destination_bug_id = " . $bug_id . "
            AND relationship_type = 2";
      }

      $result = $this->mysqli->query ( $query );

      $relationships = null;
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row () )
         {
            $relationships[] = $row;
         }
      }
      $this->mysqli->close ();

      return $relationships;
   }

   /**
    * insert a new eta-key-value-pair
    *
    * @param $key
    * @param $value
    * @return mixed
    */
   public function insert_eta_key_value ( $key, $value )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "INSERT INTO mantis_plugin_RoadmapPro_eta_table ( id, eta_config_value, eta_user_value )
            SELECT null,'" . $key . "','" . $value . "'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_eta_table
            WHERE eta_config_value = '" . $key . "')";

      $this->mysqli->query ( $query );
      $eta_id = $this->mysqli->insert_id;
      $this->mysqli->close ();

      return $eta_id;
   }

   /**
    * update a eta-key-value-pair
    *
    * @param $key
    * @param $value
    */
   public function update_eta_key_value ( $key, $value )
   {

      if ( $this->check_eta_key_is_set ( $key ) )
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
         $this->insert_eta_key_value ( $key, $value );
      }
   }

   /**
    * checks of a key is already set in the database
    *
    * @param $key
    * @return bool
    */
   public function check_eta_key_is_set ( $key )
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

   public function get_eta_row_by_key ( $key )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "SELECT * FROM mantis_plugin_RoadmapPro_eta_table
            WHERE eta_config_value = '" . $key . "'";

      $result = $this->mysqli->query ( $query );

      $eta_row = null;
      if ( 0 != $result->num_rows )
      {
         $eta_row = $result->fetch_row ();
      }
      $this->mysqli->close ();

      return $eta_row;
   }

   public function get_eta_unit ()
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "SELECT eta_unit FROM mantis_plugin_RoadmapPro_unit_table
            WHERE eta_unit != ''";

      $result = $this->mysqli->query ( $query );

      $eta_unit = null;
      if ( 0 != $result->num_rows )
      {
         $eta_unit = $result->fetch_row ()[ 0 ];
      }
      $this->mysqli->close ();

      return $eta_unit;
   }

   /**
    * insert a new eta-unit
    *
    * @param $unit
    * @return mixed
    */
   public function insert_eta_unit ( $unit )
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "INSERT INTO mantis_plugin_RoadmapPro_unit_table ( id, eta_unit )
            SELECT null,'" . $unit . "'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_unit_table
            WHERE id = 1)";

      $this->mysqli->query ( $query );
      $unit_id = $this->mysqli->insert_id;
      $this->mysqli->close ();

      return $unit_id;
   }

   /**
    * update a eta-unit
    *
    * @param $unit
    */
   public function update_eta_unit ( $unit )
   {

      if ( $this->check_eta_unit_is_set () )
      {
         $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );
         $query = /** @lang sql */
            "UPDATE mantis_plugin_RoadmapPro_unit_table
            SET eta_unit='" . $unit . "'
            WHERE id = 1";

         $this->mysqli->query ( $query );
         $this->mysqli->close ();
      }
      else
      {
         $this->insert_eta_unit ( $unit );
      }
   }

   /**
    * checks of a eta-unit is already set in the database
    *
    * @return bool
    */
   public function check_eta_unit_is_set ()
   {
      $this->mysqli->connect ( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );

      $query = /** @lang sql */
         "SELECT id FROM mantis_plugin_RoadmapPro_unit_table
            WHERE id = 1";

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
}