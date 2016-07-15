<?php
require_once ( __DIR__ . '/roadmap_db.php' );

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
      $roadmap_db = new roadmap_db();

      $eta = 0;
      $bug_eta_value = bug_get_field ( $bug_id, 'eta' );

      $eta_enum_string = config_get ( 'eta_enum_string' );
      $eta_enum_values = MantisEnum::getValues ( $eta_enum_string );

      foreach ( $eta_enum_values as $enum_value )
      {
         if ( $enum_value == $bug_eta_value )
         {
            $eta_row = $roadmap_db->get_eta_row_by_key ( $enum_value );
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
      $roadmap_db = new roadmap_db();

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
               $eta_row = $roadmap_db->get_eta_row_by_key ( $enum_value );
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
   public static function check_issue_is_done_by_id ( $bug_id, $profile_id )
   {
      $roadmap_db = new roadmap_db();

      $done = false;

      $bug_status = bug_get_field ( $bug_id, 'status' );
      $roadmap_profile = $roadmap_db->get_roadmap_profile ( $profile_id );
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
         /** specific profile */
         if ( self::check_issue_is_done_by_id ( $bug_id, $profile_id ) )
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
         /** specific profile */
         if ( self::check_issue_is_done_by_id ( $bug_id, $profile_id ) )
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
      $roadmap_db = new roadmap_db();

      $bug_count = count ( $bug_ids );
      $bug_hash_array = array ();
      for ( $bug_index = 0; $bug_index < ( $bug_count ); $bug_index++ )
      {
         $bug_id = $bug_ids[ $bug_index ];
         $bug_target_version = bug_get_field ( $bug_id, 'target_version' );

         $bug_blocking_ids = array ();
         $bug_blocked_ids = array ();

         $blocking_relationship_rows = $roadmap_db->get_bug_relationship ( $bug_id, true );
         $blocked_relationship_rows = $roadmap_db->get_bug_relationship ( $bug_id, false );

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
}
