<?php
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rProApi.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rThreshold.php' );

/**
 * the threshold manager gets data from multiple eta thresholds
 *
 * @author Stefan Schwarz
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
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT id FROM mantis_plugin_RoadmapPro_etathreshold_table';

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
}