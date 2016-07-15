<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_db.php' );

processPage ();

/**
 * authenticates a user and removes a version if user has level to do
 */
function processPage ()
{
   auth_reauthenticate ();

   $getProfileId = $_GET[ 'profile_id' ];
   $roadmapDb = new roadmap_db();
   $roadmapDb->dbDeleteProfile ( $getProfileId );

   /** redirect to view page */
   print_successful_redirect ( plugin_page ( 'config_page', true ) );
}