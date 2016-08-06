<?php
require_once ( __DIR__ . '/roadmap_pro_api.php' );
require_once ( __DIR__ . '/rThreshold.php' );

/**
 * the threshold manager gets data from multiple eta thresholds
 */
class rThresholdManager
{
   /**
    * returns all threshold ids
    *
    * @return array
    */
   public static function getRThresholdIds ()
   {
      $mysqli = roadmap_pro_api::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT id FROM mantis_plugin_roadmappro_etathreshold_table';

      $result = $mysqli->query ( $query );

      $thresholdIds = array ();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row ()[ 0 ] )
         {
            $thresholdIds[] = $row;
         }
      }

      $mysqli->close ();

      return $thresholdIds;
   }

   /**
    * iterates the given threshold ids and returns the assigned threshold objects
    *
    * @param $thresholdIds
    * @return array
    */
   public static function getRGroups ( $thresholdIds )
   {
      $thresholds = array ();
      foreach ( $thresholdIds as $thresholdId )
      {
         $threshold = new rThreshold( $thresholdId );
         array_push ( $thresholds, $threshold );
      }

      return $thresholds;
   }
}