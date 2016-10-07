<?php
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rProApi.php' );

/**
 * threshold class that represents an eta threshold
 *
 * @author Stefan Schwarz
 */
class rThreshold
{
   /**
    * @var integer
    */
   private $thresholdId;
   /**
    * @var integer
    */
   private $thresholdTo;
   /**
    * @var string
    */
   private $thresholdUnit;
   /**
    * @var integer
    */
   private $thresholdFactor;

   /**
    * rThreshold constructor.
    * @param null $thresholdId
    */
   public function __construct ( $thresholdId = NULL )
   {
      if ( $thresholdId != NULL )
      {
         $this->thresholdId = $thresholdId;
         $this->dbInitThresholdById ();
      }
   }

   /**
    * rThreshold destructor.
    */
   public function __destruct ()
   {
      // TODO: Implement __destruct() method.
   }

   /**
    * @return int
    */
   public function getThresholdId ()
   {
      return $this->thresholdId;
   }

   /**
    * @return int
    */
   public function getThresholdTo ()
   {
      return $this->thresholdTo;
   }

   /**
    * @param int $thresholdTo
    */
   public function setThresholdTo ( $thresholdTo )
   {
      $this->thresholdTo = $thresholdTo;
   }

   /**
    * @return string
    */
   public function getThresholdUnit ()
   {
      return $this->thresholdUnit;
   }

   /**
    * @param string $thresholdUnit
    */
   public function setThresholdUnit ( $thresholdUnit )
   {
      $this->thresholdUnit = $thresholdUnit;
   }

   /**
    * @return int
    */
   public function getThresholdFactor ()
   {
      return $this->thresholdFactor;
   }

   /**
    * @param int $thresholdFactor
    */
   public function setThresholdFactor ( $thresholdFactor )
   {
      $this->thresholdFactor = $thresholdFactor;
   }

   /**
    * insert object data into new database row
    */
   public function triggerInsertIntoDb ()
   {
      if (
         ( $this->thresholdTo != NULL ) &&
         is_numeric ( $this->thresholdTo ) &&
         ( $this->thresholdUnit != NULL ) &&
         ( $this->thresholdFactor != NULL ) &&
         is_numeric ( $this->thresholdFactor )
      )
      {
         $this->dbInsertThreshold ();
      }
   }

   /**
    * update selected database row with object data
    */
   public function triggerUpdateInDb ()
   {
      if (
         ( $this->thresholdTo != NULL ) &&
         is_numeric ( $this->thresholdTo ) &&
         ( $this->thresholdUnit != NULL ) &&
         ( $this->thresholdFactor != NULL ) &&
         is_numeric ( $this->thresholdFactor ) &&
         ( $this->thresholdId != NULL ) &&
         is_numeric ( $this->thresholdId )
      )
      {
         $this->dbUpdateThreshold ();
      }
   }

   /**
    * remove selected database row
    */
   public function triggerDeleteFromDb ()
   {
      if (
         ( $this->thresholdId != NULL ) &&
         is_numeric ( $this->thresholdId )
      )
      {
         $this->dbDeleteThreshold ();
      }
   }

   /**
    * initializes a group object with database data
    */
   private function dbInitThresholdById ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT * FROM mantis_plugin_whiteboard_etathreshold_table WHERE id=' . $this->thresholdId;

      $result = $mysqli->query ( $query );
      $dbThresholdRow = mysqli_fetch_row ( $result );
      $mysqli->close ();

      $this->thresholdTo = $dbThresholdRow[ 1 ];
      $this->thresholdUnit = $dbThresholdRow[ 2 ];
      $this->thresholdFactor = $dbThresholdRow[ 3 ];
   }

   /**
    * insert new threshold row
    */
   private function dbInsertThreshold ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'INSERT INTO mantis_plugin_whiteboard_etathreshold_table ( id, eta_thr_to, eta_thr_unit, eta_thr_factor )
         SELECT null,' . (int)$this->thresholdTo . ',\'' . $this->thresholdUnit . '\',' . (int)$this->thresholdFactor . '
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM mantis_plugin_whiteboard_etathreshold_table
         WHERE eta_thr_unit=\'' . $this->thresholdUnit . '\')';

      $mysqli->query ( $query );
      $this->thresholdId = $mysqli->insert_id;
      $mysqli->close ();
   }

   /**
    * update threshold row
    */
   private function dbUpdateThreshold ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'UPDATE mantis_plugin_whiteboard_etathreshold_table
         SET eta_thr_to=' . (int)$this->thresholdTo . ',eta_thr_unit=\'' . $this->thresholdUnit . '\',eta_thr_factor=' . (int)$this->thresholdFactor . '
         WHERE id=' . $this->thresholdId;

      $mysqli->query ( $query );
      $mysqli->close ();
   }

   /**
    * delete threshold row
    */
   private function dbDeleteThreshold ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'DELETE FROM mantis_plugin_whiteboard_etathreshold_table WHERE id=' . $this->thresholdId;

      $mysqli->query ( $query );
      $mysqli->close ();
   }
}