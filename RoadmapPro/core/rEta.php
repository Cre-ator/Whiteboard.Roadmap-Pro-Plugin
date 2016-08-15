<?php
require_once ( __DIR__ . '/rProApi.php' );

/**
 * eta class that represents an eta element
 *
 * @author Stefan Schwarz
 */
class rEta
{
   /**
    * @var integer
    */
   private $etaId;
   /**
    * @var integer
    */
   private $etaConfig;
   /**
    * @var double
    */
   private $etaUser;
   /**
    * @var boolean
    */
   private $etaIsSet;

   /**
    * rEta constructor.
    * @param null $etaConfig
    */
   public function __construct ( $etaConfig = null )
   {
      if ( $etaConfig != null )
      {
         $this->etaConfig = $etaConfig;
         $this->dbInitEtaByConfigValue ();
      }
   }

   /**
    * rEta destructor.
    */
   public function __destruct ()
   {
      // TODO: Implement __destruct() method.
   }

   /**
    * @param int $etaConfig
    */
   public function setEtaConfig ( $etaConfig )
   {
      $this->etaConfig = $etaConfig;
   }

   /**
    * @return int
    */
   public function getEtaUser ()
   {
      return $this->etaUser;
   }

   /**
    * @param int $etaUser
    */
   public function setEtaUser ( $etaUser )
   {
      $this->etaUser = $etaUser;
   }

   /**
    * @return boolean
    */
   public function getEtaIsSet ()
   {
      $this->dbCheckEtaIsSet ();
      return $this->etaIsSet;
   }

   /**
    * insert object data into new database row
    */
   public function triggerInsertIntoDb ()
   {
      if (
         ( $this->etaUser != null ) &&
         is_numeric ( $this->etaUser )
      )
      {
         $this->dbInsertEta ();
      }
   }

   /**
    * update selected database row with object data
    */
   public function triggerUpdateInDb ()
   {
      if (
         ( $this->etaUser != null ) &&
         is_numeric ( $this->etaUser )
      )
      {
         $this->dbUpdateEta ();
      }
   }

   /**
    * initializes an eta object with database data
    */
   private function dbInitEtaByConfigValue ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT * FROM mantis_plugin_roadmappro_eta_table WHERE eta_config_value=' . $this->etaConfig;

      $result = $mysqli->query ( $query );
      $dbEtaRow = mysqli_fetch_row ( $result );
      $mysqli->close ();

      $this->etaId = $dbEtaRow[ 0 ];
      $this->etaUser = $dbEtaRow[ 2 ];
   }

   /**
    * insert new eta row
    */
   private function dbInsertEta ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'INSERT INTO mantis_plugin_RoadmapPro_eta_table ( id, eta_config_value, eta_user_value )
         SELECT null,' . $this->etaConfig . ',' . (double)$this->etaUser . '
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM mantis_plugin_RoadmapPro_eta_table
         WHERE eta_config_value=' . $this->etaConfig . ')';

      $mysqli->query ( $query );
      $this->etaId = $mysqli->insert_id;
      $mysqli->close ();
   }

   /**
    * update eta row
    */
   private function dbUpdateEta ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'UPDATE mantis_plugin_RoadmapPro_eta_table
         SET eta_user_value=' . (double)$this->etaUser . '
         WHERE eta_config_value=' . $this->etaConfig;

      $mysqli->query ( $query );
      $mysqli->close ();
   }

   /**
    * check if eta is set for selected config value
    */
   private function dbCheckEtaIsSet ()
   {
      $mysqli = rProApi::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT id FROM mantis_plugin_RoadmapPro_eta_table WHERE eta_config_value = ' . $this->etaConfig;

      $result = $mysqli->query ( $query );
      $mysqli->close ();

      if ( 0 != $result->num_rows )
      {
         $this->etaIsSet = true;
      }
      else
      {
         $this->etaIsSet = false;
      }
   }
}