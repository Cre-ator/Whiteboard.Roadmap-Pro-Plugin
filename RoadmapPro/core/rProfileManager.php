<?php
require_once ( __DIR__ . '/roadmap_pro_api.php' );
require_once ( __DIR__ . '/rProfile.php' );

/**
 * the profile manager gets data from multiple roadmap profiles
 *
 * @author Stefan Schwarz
 */
class rProfileManager
{
   /**
    * returns all profile ids
    *
    * @return array
    */
   public static function getRProfileIds ()
   {
      $mysqli = roadmap_pro_api::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT id FROM mantis_plugin_RoadmapPro_profile_table ORDER BY profile_prio ASC';

      $result = $mysqli->query ( $query );

      $profileIds = array ();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row ()[ 0 ] )
         {
            $profileIds[] = $row;
         }
      }

      $mysqli->close ();

      return $profileIds;
   }

   /**
    * iterates the given profile ids and returns the assigned profile objects
    *
    * @param $profileIds
    * @return array
    */
   public static function getRProfiles ( $profileIds )
   {
      $profiles = array ();
      foreach ( $profileIds as $profileId )
      {
         $profile = new rProfile( $profileId );
         array_push ( $profiles, $profile );
      }

      return $profiles;
   }

   /**
    * returns the sum of alle profile efforts
    *
    * @return int
    */
   public static function getSumRProfileEffort ()
   {
      $mysqli = roadmap_pro_api::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT SUM(profile_effort) FROM mantis_plugin_roadmappro_profile_table';

      $result = $mysqli->query ( $query );

      $sumProfileEffort = 0;
      if ( 0 != $result->num_rows )
      {
         $sumProfileEffort = $result->fetch_row ()[ 0 ];
      }
      $mysqli->close ();

      return $sumProfileEffort;
   }
}