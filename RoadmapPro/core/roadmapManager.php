<?php

/**
 * the roadmap manager prepares neccessary data for the roadmaps
 */
class roadmapManager
{
   private $projectIds;
   private $versions;
   private $getVersionId;
   private $getProjectId;

   function __construct ( $getVersionId, $getProjectId )
   {
      $this->projectIds = array ();
      $this->versions = array ();
      $this->getVersionId = $getVersionId;
      $this->getProjectId = $getProjectId;
   }

   public function getProjectIds ()
   {
      return $this->projectIds;
   }

   public function getVersions ()
   {
      return $this->versions;
   }

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

   private function projectDataCollector ()
   {
      array_push ( $this->projectIds, $this->getProjectId );
   }
}