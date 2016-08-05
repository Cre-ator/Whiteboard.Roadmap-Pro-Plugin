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

   $getGroupId = $_GET[ 'group_id' ];
   $roadmapDb = new roadmap_db();
   $roadmapDb->dbDeleteGroup ( $getGroupId );

   # redirect to view page
   print_successful_redirect ( plugin_page ( 'config_page', true ) );
}