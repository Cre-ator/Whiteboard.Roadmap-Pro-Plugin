<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_db.php' );

process_page ();

/**
 * authenticates a user and removes a version if user has level to do
 */
function process_page ()
{
   auth_reauthenticate ();

   $profile_id = $_GET[ 'profile_id' ];
   $oradmap_db = new roadmap_db();
   $oradmap_db->delete_profile ( $profile_id );

   /** redirect to view page */
   print_successful_redirect ( plugin_page ( 'config_page', true ) );
}