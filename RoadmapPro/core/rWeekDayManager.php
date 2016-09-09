<?php
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rProApi.php' );

/**
 * the weekday manager gets worktime data for each day in a week
 *
 * @author Stefan Schwarz
 */
class rWeekDayManager
{
   /**
    * returns workday config
    *
    * @return array
    */
   public static function getWorkDayConfig ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT workday_values FROM mantis_plugin_whiteboard_workday_table';

      $result = $mysqli->query ( $query );
      $mysqli->close ();
      $workDayConfig = '';
      if ( 0 != $result->num_rows )
      {
         $workDayConfig = mysqli_fetch_row ( $result )[ 0 ];
      }

      return $workDayConfig;
   }

   /**
    * set workday config
    *
    * @param $config
    */
   public static function setWorkDayConfig ( $config )
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'UPDATE mantis_plugin_whiteboard_workday_table
         SET workday_values=\'' . $config . '\'';

      $mysqli->query ( $query );
      $mysqli->close ();
   }
}