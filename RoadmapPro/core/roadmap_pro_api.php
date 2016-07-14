<?php

/**
 * Class version_management_api
 *
 * Contains functions for the plugin specific content
 */
class roadmap_pro_api
{
   /**
    * returns true, if the used mantis version is release 1.2.x
    *
    * @return bool
    */
   public static function check_mantis_version_is_released ()
   {
      return substr ( MANTIS_VERSION, 0, 4 ) == '1.2.';
   }

   /**
    * returns true, if there is a duplicate entry.
    *
    * @param $array
    * @return bool
    */
   public static function check_array_for_duplicates ( $array )
   {
      return count ( $array ) !== count ( array_unique ( $array ) );
   }

   /**
    * create a mysqli object
    *
    * @return mysqli
    */
   private static function get_mysqli_object ()
   {
      $dbPath = config_get ( 'hostname' );
      $dbUser = config_get ( 'db_username' );
      $dbPass = config_get ( 'db_password' );
      $dbName = config_get ( 'database_name' );

      return new mysqli( $dbPath, $dbUser, $dbPass, $dbName );
   }

   /**
    * returns all assigned bug ids to a given target version
    *
    * @param $project_id
    * @param $version_name
    * @return array|null
    */
   public static function get_bug_ids_by_project_and_version ( $project_id, $version_name )
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "SELECT id FROM mantis_bug_table
            WHERE target_version = '" . $version_name . "'
            AND project_id = " . $project_id;

      $result = $mysqli->query ( $query );

      $bug_ids = array ();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row () )
         {
            $bug_ids[] = $row[ 0 ];
         }
         return $bug_ids;
      }

      return null;
   }

   /**
    * inserts a new roadmap profile if there isnt a dupicate by name
    *
    * @param $profile_name
    * @param $profile_color
    * @param $profile_status
    */
   public static function insert_profile ( $profile_name, $profile_color, $profile_status )
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "INSERT INTO mantis_plugin_RoadmapPro_profile_table ( id, profile_name, profile_color, profile_status )
            SELECT null,'" . $profile_name . "','" . $profile_color . "','" . $profile_status . "'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_profile_table
            WHERE profile_name = '" . $profile_name . "')";

      $mysqli->query ( $query );
   }

   /**
    * get all roadmap profiles
    *
    * @return array|null
    */
   public static function get_roadmap_profiles ()
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "SELECT * FROM mantis_plugin_RoadmapPro_profile_table";

      $result = $mysqli->query ( $query );

      $profiles = array ();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row () )
         {
            $profiles[] = $row;
         }
         return $profiles;
      }

      return null;
   }

   /**
    * get a specific roadmap profile
    *
    * @param $profile_id
    * @return array|null
    */
   public static function get_roadmap_profile ( $profile_id )
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "SELECT * FROM mantis_plugin_RoadmapPro_profile_table
            WHERE id = " . $profile_id;

      $result = $mysqli->query ( $query );

      $profile = mysqli_fetch_row ( $result );

      return $profile;
   }

   /**
    * delete a roadmap profile by its primary id
    *
    * @param $profile_id
    */
   public static function delete_profile ( $profile_id )
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "DELETE FROM mantis_plugin_RoadmapPro_profile_table
            WHERE id = " . $profile_id;

      $mysqli->query ( $query );
   }

   /**
    * Reset all plugin-related data
    *
    * - config entries
    * - database entities
    */
   public static function reset_plugin ()
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_profile_table";

      $mysqli->query ( $query );

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_eta_table";

      $mysqli->query ( $query );

      $query = /** @lang sql */
         "DROP TABLE mantis_plugin_RoadmapPro_unit_table";

      $mysqli->query ( $query );

      $query = /** @lang sql */
         "DELETE FROM mantis_config_table
            WHERE config_id LIKE 'plugin_RoadmapPro%'";

      $mysqli->query ( $query );

      print_successful_redirect ( 'manage_plugin_page.php' );
   }

   /**
    * returns true if every item of bug id array has set eta value
    *
    * @param $bug_ids
    * @return bool
    */
   public static function check_eta_is_set ( $bug_ids )
   {
      $set = true;
      foreach ( $bug_ids as $bug_id )
      {
         $bug_eta_value = bug_get_field ( $bug_id, 'eta' );
         if ( ( is_null ( $bug_eta_value ) ) || ( $bug_eta_value == 10 ) )
         {
            $set = false;
         }
      }

      return $set;
   }

   /**
    * returns the eta value of a single bug
    *
    * @param $bug_id
    * @return float|int
    */
   public static function get_single_eta ( $bug_id )
   {
      $eta = 0;
      $bug_eta_value = bug_get_field ( $bug_id, 'eta' );

      $eta_enum_string = config_get ( 'eta_enum_string' );
      $eta_enum_values = MantisEnum::getValues ( $eta_enum_string );

      foreach ( $eta_enum_values as $enum_value )
      {
         if ( $enum_value == $bug_eta_value )
         {
            $eta_row = self::get_eta_row_by_key ( $enum_value );
            $eta = $eta_row[ 2 ];
         }
      }

      return $eta;
   }

   /**
    * returns the eta value of a bunch of bugs
    *
    * @param $bug_ids
    * @return float|int
    */
   public static function get_full_eta ( $bug_ids )
   {
      $full_eta = 0;
      foreach ( $bug_ids as $bug_id )
      {
         $bug_eta_value = bug_get_field ( $bug_id, 'eta' );

         $eta_enum_string = config_get ( 'eta_enum_string' );
         $eta_enum_values = MantisEnum::getValues ( $eta_enum_string );

         foreach ( $eta_enum_values as $enum_value )
         {
            if ( $enum_value == $bug_eta_value )
            {
               $eta_row = self::get_eta_row_by_key ( $enum_value );
               $full_eta += $eta_row[ 2 ];
            }
         }
      }

      return $full_eta;
   }

   /**
    * returns true if the issue is done like it is defined in the profile preference
    *
    * @param $bug_id
    * @param $profile_id
    * @return bool
    */
   public static function check_issue_is_done ( $bug_id, $profile_id )
   {
      $done = false;

      $bug_status = bug_get_field ( $bug_id, 'status' );
      $roadmap_profile = roadmap_pro_api::get_roadmap_profile ( $profile_id );
      $db_raodmap_status = $roadmap_profile[ 3 ];
      $roadmap_status_array = explode ( ';', $db_raodmap_status );

      foreach ( $roadmap_status_array as $roadmap_status )
      {
         if ( $bug_status == $roadmap_status )
         {
            $done = true;
         }
      }

      return $done;
   }

   /**
    * returns the amount of done bugs in a bunch of bugs
    *
    * @param $bug_ids
    * @param $profile_id
    * @return int
    */
   public static function get_done_bug_amount ( $bug_ids, $profile_id )
   {
      $done_bug_amount = 0;

      foreach ( $bug_ids as $bug_id )
      {
         if ( self::check_issue_is_done ( $bug_id, $profile_id ) )
         {
            $done_bug_amount++;
         }
      }

      return $done_bug_amount;
   }

   /**
    * returns the ids of done bugs in a bunch of bugs
    *
    * @param $bug_ids
    * @param $profile_id
    * @return array
    */
   public static function get_done_bug_ids ( $bug_ids, $profile_id )
   {
      $done_bug_ids = array ();
      foreach ( $bug_ids as $bug_id )
      {
         if ( self::check_issue_is_done ( $bug_id, $profile_id ) )
         {
            array_push ( $done_bug_ids, $bug_id );
         }
      }

      return $done_bug_ids;
   }

   /**
    * returns all subproject ids incl. the selected one except it is zero
    *
    * @return array
    */
   public static function prepare_project_ids ()
   {
      $current_project_id = helper_get_current_project ();
      $sub_project_ids = project_hierarchy_get_all_subprojects ( $current_project_id );

      $project_ids = array ();
      if ( $current_project_id > 0 )
      {
         array_push ( $project_ids, $current_project_id );
      }

      foreach ( $sub_project_ids as $sub_project_id )
      {
         array_push ( $project_ids, $sub_project_id );
      }

      return $project_ids;
   }

   /**
    * returns an array with bug ids and extened information about relations
    *
    * @param $bug_ids
    * @return mixed
    */
   public static function calculate_bug_relationships ( $bug_ids )
   {
      $bug_count = count ( $bug_ids );
      $bug_hash_array = array ();
      for ( $bug_index = 0; $bug_index < ( $bug_count ); $bug_index++ )
      {
         $bug_id = $bug_ids[ $bug_index ];
         $bug_target_version = bug_get_field ( $bug_id, 'target_version' );

         $bug_blocking_ids = array ();
         $bug_blocked_ids = array ();

         $blocking_relationship_rows = self::get_bug_relationship ( $bug_id, true );
         $blocked_relationship_rows = self::get_bug_relationship ( $bug_id, false );

         if ( is_null ( $blocking_relationship_rows ) == false )
         {
            foreach ( $blocking_relationship_rows as $blocking_relationship )
            {
               $dest_bug_id = $blocking_relationship[ 0 ];
               $dest_bug_target_version = bug_get_field ( $dest_bug_id, 'target_version' );

               if ( $bug_target_version == $dest_bug_target_version )
               {
                  array_push ( $bug_blocking_ids, $dest_bug_id );
               }
            }
         }

         if ( is_null ( $blocked_relationship_rows ) == false )
         {
            foreach ( $blocked_relationship_rows as $blocked_relationship )
            {
               $src_bug_id = $blocked_relationship[ 0 ];
               $src_bug_target_version = bug_get_field ( $src_bug_id, 'target_version' );

               if ( $bug_target_version == $src_bug_target_version )
               {
                  array_push ( $bug_blocked_ids, $src_bug_id );
               }
            }
         }

         $bug_hash = array (
            'id' => $bug_id,
            'blocking_ids' => $bug_blocking_ids,
            'blocked_ids' => $bug_blocked_ids
         );

         array_push ( $bug_hash_array, $bug_hash );
      }

      return $bug_hash_array;
   }

   /**
    * returns true if there is a relationship for two given bug ids
    *
    * @param $bug_id_src
    * @param $bug_id_dest
    * @return bool
    */
   public static function check_relationship ( $bug_id_src, $bug_id_dest )
   {
      /** src_id - blocked */
      /** dest_id - blocking */

      /** - blocked
       *    - blocking
       *  - others...
       */

      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "SELECT * FROM mantis_bug_relationship_table
            WHERE source_bug_id = " . $bug_id_src . "
            AND destination_bug_id = " . $bug_id_dest . "
            AND relationship_type = 2";

      $result = $mysqli->query ( $query );

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
   public static function get_bug_relationship ( $bug_id, $blocking )
   {
      $mysqli = self::get_mysqli_object ();

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

      $result = $mysqli->query ( $query );

      $relationships = array ();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row () )
         {
            $relationships[] = $row;
         }
         return $relationships;
      }

      return null;
   }

   /**
    * insert a new eta-key-value-pair
    *
    * @param $key
    * @param $value
    */
   public static function insert_eta_key_value ( $key, $value )
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "INSERT INTO mantis_plugin_RoadmapPro_eta_table ( id, eta_config_value, eta_user_value )
            SELECT null,'" . $key . "','" . $value . "'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_eta_table
            WHERE eta_config_value = '" . $key . "')";

      $mysqli->query ( $query );
   }

   /**
    * update a eta-key-value-pair
    *
    * @param $key
    * @param $value
    */
   public static function update_eta_key_value ( $key, $value )
   {
      $mysqli = self::get_mysqli_object ();

      if ( self::check_eta_key_is_set ( $key ) )
      {
         $query = /** @lang sql */
            "UPDATE mantis_plugin_RoadmapPro_eta_table
            SET eta_user_value='" . $value . "'
            WHERE eta_config_value = '" . $key . "'";

         $mysqli->query ( $query );
      }
      else
      {
         self::insert_eta_key_value ( $key, $value );
      }
   }

   /**
    * checks of a key is already set in the database
    *
    * @param $key
    * @return bool
    */
   public static function check_eta_key_is_set ( $key )
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "SELECT id FROM mantis_plugin_RoadmapPro_eta_table
            WHERE eta_config_value = '" . $key . "'";

      $result = $mysqli->query ( $query );
      if ( 0 != $result->num_rows )
      {
         return true;
      }
      else
      {
         return false;
      }
   }

   public static function get_eta_row_by_key ( $key )
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "SELECT * FROM mantis_plugin_RoadmapPro_eta_table
            WHERE eta_config_value = '" . $key . "'";

      $result = $mysqli->query ( $query );

      $eta_row = null;
      if ( 0 != $result->num_rows )
      {
         $eta_row = $result->fetch_row ();
      }

      return $eta_row;
   }

   public static function get_eta_unit ()
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "SELECT eta_unit FROM mantis_plugin_RoadmapPro_unit_table
            WHERE eta_unit != ''";

      $result = $mysqli->query ( $query );

      $eta_unit = null;
      if ( 0 != $result->num_rows )
      {
         $eta_unit = $result->fetch_row ()[ 0 ];
      }

      return $eta_unit;
   }

   /**
    * insert a new eta-unit
    *
    * @param $unit
    */
   public static function insert_eta_unit ( $unit )
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "INSERT INTO mantis_plugin_RoadmapPro_unit_table ( id, eta_unit )
            SELECT null,'" . $unit . "'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_eta_table
            WHERE id = 1)";

      $mysqli->query ( $query );
   }

   /**
    * update a eta-unit
    *
    * @param $unit
    */
   public static function update_eta_unit ( $unit )
   {
      $mysqli = self::get_mysqli_object ();

      if ( self::check_eta_unit_is_set () )
      {
         $query = /** @lang sql */
            "UPDATE mantis_plugin_RoadmapPro_unit_table
            SET eta_unit='" . $unit . "'
            WHERE id = 1";

         $mysqli->query ( $query );
      }
      else
      {
         self::insert_eta_unit ( $unit );
      }
   }

   /**
    * checks of a eta-unit is already set in the database
    *
    * @return bool
    */
   public static function check_eta_unit_is_set ()
   {
      $mysqli = self::get_mysqli_object ();

      $query = /** @lang sql */
         "SELECT id FROM mantis_plugin_RoadmapPro_unit_table
            WHERE id = 1";

      $result = $mysqli->query ( $query );
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
