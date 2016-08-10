<?php
require_once ( __DIR__ . '/../core/rProApi.php' );

auth_reauthenticate ();

$optionChange = gpc_get_bool ( 'config_change', false );
$optionReset = gpc_get_bool ( 'config_reset', false );

if ( $optionReset == true )
{
   print_successful_redirect ( plugin_page ( 'config_reset_ensure', true ) );
}

if ( $optionChange == true )
{
   rProApi::configUpdateButton ( 'show_menu' );
   rProApi::configUpdateButton ( 'show_footer' );
   # change eta values when eta management is active
   if ( config_get ( 'enable_eta' ) )
   {
      rProApi::configProcessEta ();
   }
   # change profile values
   rProApi::configProcessProfiles ();
   # change group values
   rProApi::configProcessGroups ();

   form_security_purge ( 'plugin_RoadmapPro_config_update' );
   print_successful_redirect ( plugin_page ( 'config_page', true ) );
}