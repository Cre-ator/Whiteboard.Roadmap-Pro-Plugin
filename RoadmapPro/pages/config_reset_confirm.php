<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );

if ( isset( $_POST[ 'con_reset' ] ) )
{
   roadmap_pro_api::dbResetPlugin ();
}
else
{
   print_successful_redirect ( plugin_page ( 'config_page', true ) );
}

print_successful_redirect ( 'manage_plugin_page.php' );