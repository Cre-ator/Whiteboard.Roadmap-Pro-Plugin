<?php
require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );

auth_reauthenticate ();

$optionChange = gpc_get_bool ( 'config_change', false );
$optionReset = gpc_get_bool ( 'config_reset', false );

if ( $optionReset == true )
{
   print_successful_redirect ( plugin_page ( 'config_reset_ensure', true ) );
}

if ( $optionChange == true )
{
   roadmap_pro_api::configUpdateButton ( 'show_menu' );
   roadmap_pro_api::configUpdateButton ( 'show_footer' );
   # change eta values when eta management is active
   if ( config_get ( 'enable_eta' ) )
   {
      roadmap_pro_api::configProcessEta ();
   }
   # change profile values
   roadmap_pro_api::configProcessProfiles ();
   # change group values
   roadmap_pro_api::configProcessGroups ();

   form_security_purge ( 'plugin_RoadmapPro_config_update' );
   print_successful_redirect ( plugin_page ( 'config_page', true ) );
}