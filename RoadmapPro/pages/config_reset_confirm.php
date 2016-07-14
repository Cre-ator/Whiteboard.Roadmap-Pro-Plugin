<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_db.php' );

if ( isset( $_POST[ 'con_reset' ] ) )
{
   $roadmap_db = new roadmap_db();
   $roadmap_db->reset_plugin ();
}
else
{
   print_successful_redirect ( plugin_page ( 'config_page', true ) );
}

print_successful_redirect ( 'manage_plugin_page.php' );