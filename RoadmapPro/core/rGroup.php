<?php
require_once ( __DIR__ . '/roadmap_pro_api.php' );

/**
 * group class that represents a group
 *
 * @author Stefan Schwarz
 */
class rGroup
{
   /**
    * @var mysqli
    */
   private $mysqli;
   /**
    * @var integer
    */
   private $groupId;
   /**
    * @var string
    */
   private $groupName;
   /**
    * @var string
    */
   private $groupProfiles;

   /**
    * rGroup constructor.
    * @param null $groupId
    */
   public function __construct ( $groupId = null )
   {
      if ( $groupId != null )
      {
         $this->groupId = $groupId;
         $this->dbInitGroupById ();
      }
   }

   /**
    * rGroup destructor.
    */
   public function __destruct ()
   {
      // TODO: Implement __destruct() method.
   }

   /**
    * @return string
    */
   public function getGroupName ()
   {
      return $this->groupName;
   }

   /**
    * @param string $groupName
    */
   public function setGroupName ( $groupName )
   {
      $this->groupName = $groupName;
   }

   /**
    * @return string
    */
   public function getGroupProfiles ()
   {
      return $this->groupProfiles;
   }

   /**
    * @param string $groupProfiles
    */
   public function setGroupProfiles ( $groupProfiles )
   {
      $this->groupProfiles = $groupProfiles;
   }

   /**
    * insert object data into new database row
    */
   public function triggerInsertIntoDb ()
   {
      if (
         $this->groupName != null &&
         $this->groupProfiles != null
      )
      {
         $this->dbInsertGroup ();
      }
   }

   /**
    * update selected database row with object data
    */
   public function triggerUpdateInDb ()
   {
      if (
         $this->groupId != null &&
         is_numeric ( $this->groupId ) &&
         $this->groupName != null &&
         $this->groupProfiles != null
      )
      {
         $this->dbUpdateGroup ();
      }
   }

   /**
    * remove selected database row
    */
   public function triggerDeleteFromDb ()
   {
      if (
         $this->groupId != null &&
         is_numeric ( $this->groupId )
      )
      {
         $this->dbDeleteGroup ();
      }
   }

   /**
    * initializes a group object with database data
    */
   private function dbInitGroupById ()
   {
      $this->mysqli = roadmap_pro_api::initializeDbConnection ();

      $query = /** @lang sql */
         'SELECT DISTINCT * FROM mantis_plugin_RoadmapPro_profilegroup_table WHERE id=' . $this->groupId;

      $result = $this->mysqli->query ( $query );
      $dbGroupRow = mysqli_fetch_row ( $result );
      $this->mysqli->close ();

      $this->groupName = $dbGroupRow[ 1 ];
      $this->groupProfiles = $dbGroupRow[ 2 ];
   }

   /**
    * insert new group row
    */
   private function dbInsertGroup ()
   {
      $this->mysqli = roadmap_pro_api::initializeDbConnection ();

      $query = /** @lang sql */
         'INSERT INTO mantis_plugin_RoadmapPro_profilegroup_table ( id, group_name, group_profiles )
         SELECT null,\'' . $this->groupName . '\',\'' . $this->groupProfiles . '\'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM mantis_plugin_RoadmapPro_profilegroup_table
         WHERE group_name=\'' . $this->groupName . '\')';

      $this->mysqli->query ( $query );
      $this->groupId = $this->mysqli->insert_id;
      $this->mysqli->close ();
   }

   /**
    * update group row
    */
   private function dbUpdateGroup ()
   {
      $this->mysqli = roadmap_pro_api::initializeDbConnection ();

      $query = /** @lang sql */
         'UPDATE mantis_plugin_RoadmapPro_profilegroup_table
         SET group_name=\'' . $this->groupName . '\',group_profiles=\'' . $this->groupProfiles . '\'
         WHERE id=' . $this->groupId;

      $this->mysqli->query ( $query );
      $this->mysqli->close ();
   }

   /**
    * delete group row
    */
   private function dbDeleteGroup ()
   {
      $this->mysqli = roadmap_pro_api::initializeDbConnection ();

      $query = /** @lang sql */
         'DELETE FROM mantis_plugin_RoadmapPro_profilegroup_table WHERE id=' . $this->groupId;

      $this->mysqli->query ( $query );
      $this->mysqli->close ();
   }
}