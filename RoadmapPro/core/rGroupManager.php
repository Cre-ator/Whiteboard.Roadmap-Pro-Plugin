<?php
require_once ( __DIR__ . '/roadmap_pro_api.php' );
require_once ( __DIR__ . '/rGroup.php' );

/**
 * the group manager gets data from multiple group profiles
 */
class rGroupManager
{
   /**
    * returns all group ids
    *
    * @return array
    */
   public static function getRGroupIds ()
   {
      $mysqli = roadmap_pro_api::initializeDbConnection ();

      $query = /** @lang sql */
         "SELECT id FROM mantis_plugin_RoadmapPro_profilegroup_table";

      $result = $mysqli->query ( $query );

      $groupIds = array ();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row ()[ 0 ] )
         {
            $groupIds[] = $row;
         }
      }

      $mysqli->close ();

      return $groupIds;
   }

   /**
    * iterates the given group ids and returns the assigned group objects
    *
    * @param $groupIds
    * @return array
    */
   public static function getRGroups ( $groupIds )
   {
      $groups = array ();
      foreach ( $groupIds as $groupId )
      {
         $profile = new rGroup( $groupId );
         array_push ( $groups, $profile );
      }

      return $groups;
   }
}