<?php

/**
 * the roadmap manager prepares neccessary data for the roadmaps
 *
 * @author Stefan Schwarz
 */
class roadmapManager
{
   /**
    * @var array
    */
   private $projectIds;
   /**
    * @var array
    */
   private $versions;
   /**
    * @var integer
    */
   private $getVersionId;
   /**
    * @var integer
    */
   private $getProjectId;

   /**
    * roadmapManager constructor.
    * @param $getVersionId
    * @param $getProjectId
    */
   function __construct ( $getVersionId, $getProjectId )
   {
      $this->projectIds = array ();
      $this->versions = array ();
      $this->getVersionId = $getVersionId;
      $this->getProjectId = $getProjectId;
   }

   /**
    * @return array
    */
   public function getProjectIds ()
   {
      return $this->projectIds;
   }

   /**
    * @return array
    */
   public function getVersions ()
   {
      return $this->versions;
   }

   /**
    * check s out the given project or version id and calculates the relevant data
    *
    * @author Stefan Schwarz
    */
   public function calcProjectVersionContent ()
   {
      # no specific project or version
      if ( ( $this->getProjectId == null ) && ( $this->getVersionId == null ) )
      {
         $this->automaticDataCollector ();
      }

      # specific project selected
      if ( $this->getProjectId != null )
      {
         $this->projectDataCollector ();
      }

      # specific version selected
      if ( $this->getVersionId != null )
      {
         $this->projectVersionDataCollector ();
      }
   }

   /**
    * collects data without specified project or version
    *
    * @author Stefan Schwarz
    */
   private function automaticDataCollector ()
   {
      $currentProjectId = helper_get_current_project ();
      $subProjectIds = project_hierarchy_get_all_subprojects ( $currentProjectId );

      if ( $currentProjectId > 0 )
      {
         array_push ( $this->projectIds, $currentProjectId );
      }

      foreach ( $subProjectIds as $sub_project_id )
      {
         array_push ( $this->projectIds, $sub_project_id );
      }
   }

   /**
    * collects data wih given version
    *
    * @author Stefan Schwarz
    */
   private function projectVersionDataCollector ()
   {
      $version = array ();
      $version[ 'id' ] = $this->getVersionId;
      $version[ 'version' ] = version_get_field ( $this->getVersionId, 'version' );
      $version[ 'date_order' ] = version_get_field ( $this->getVersionId, 'date_order' );
      $version[ 'released' ] = version_get_field ( $this->getVersionId, 'released' );
      $version[ 'description' ] = version_get_field ( $this->getVersionId, 'description' );

      array_push ( $this->versions, $version );

      $versionRelatedProjectId = version_get_field ( $this->getVersionId, 'project_id' );
      array_push ( $this->projectIds, $versionRelatedProjectId );
   }

   /**
    * collects data with given project id
    *
    * @author Stefan Schwarz
    */
   private function projectDataCollector ()
   {
      array_push ( $this->projectIds, $this->getProjectId );
   }
}