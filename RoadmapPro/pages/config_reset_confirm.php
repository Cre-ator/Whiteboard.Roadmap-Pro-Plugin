<?php

require_once ( __DIR__ . '/../core/rProApi.php' );

if ( isset( $_POST[ 'con_reset' ] ) )
{
   rProApi::dbResetPlugin ();
}
else
{
   print_successful_redirect ( plugin_page ( 'config_page', true ) );
}

print_successful_redirect ( 'manage_plugin_page.php' );