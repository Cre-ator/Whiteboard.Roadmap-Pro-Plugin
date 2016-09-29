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
   private $versionId;
   /**
    * @var integer
    */
   private $projectId;

   /**
    * roadmapManager constructor.
    * @param $getVersionId
    * @param $getProjectId
    */
   function __construct ( $getVersionId, $getProjectId )
   {
      $this->projectIds = array ();
      $this->versions = array ();
      $this->versionId = $getVersionId;
      $this->projectId = $getProjectId;

      $this->calcProjectVersionContent ();
   }

   /**
    * roadmapManager destructor.
    */
   function __destruct ()
   {
      // TODO: Implement __destruct() method.
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
   private function calcProjectVersionContent ()
   {
      # no specific project or version
      if ( ( $this->projectId == NULL ) && ( $this->versionId == NULL ) )
      {
         $this->automaticDataCollector ();
      }

      # specific project selected
      if ( $this->projectId != NULL )
      {
         $this->projectDataCollector ();
      }

      # specific version selected
      if ( $this->versionId != NULL )
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
      $version[ 'id' ] = $this->versionId;
      $version[ 'version' ] = version_get_field ( $this->versionId, 'version' );
      $version[ 'date_order' ] = version_get_field ( $this->versionId, 'date_order' );
      $version[ 'released' ] = version_get_field ( $this->versionId, 'released' );
      $version[ 'description' ] = version_get_field ( $this->versionId, 'description' );

      array_push ( $this->versions, $version );

      $versionRelatedProjectId = version_get_field ( $this->versionId, 'project_id' );
      array_push ( $this->projectIds, $versionRelatedProjectId );
   }

   /**
    * collects data with given project id
    *
    * @author Stefan Schwarz
    */
   private function projectDataCollector ()
   {
      array_push ( $this->projectIds, $this->projectId );
   }
}