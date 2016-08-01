<?php

/**
 * Created by PhpStorm.
 * User: stefan.schwarz
 * Date: 01.08.2016
 * Time: 23:16
 */
class roadmap_data
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

   public function calcProjectVersionContent ()
   {
      /** no specific project or version */
      if ( ( $this->getProjectId == null ) && ( $this->getVersionId == null ) )
      {
         $this->prepareProjectIds ();
      }

      /** specific project selected */
      if ( $this->getProjectId != null )
      {
         array_push ( $this->projectIds, $this->getProjectId );
      }

      /** specific version selected */
      if ( $this->getVersionId != null )
      {
         $this->prepareVersionBasedData ();
      }
   }

   public function getProjectIds ()
   {
      return $this->projectIds;
   }

   public function getVersions ()
   {
      return $this->versions;
   }

   private function prepareProjectIds ()
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

   private function prepareVersionBasedData ()
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
}