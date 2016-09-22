<?php
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProApi.php' );

auth_reauthenticate ();

$optionChange = gpc_get_bool ( 'config_change', FALSE );
$optionReset = gpc_get_bool ( 'config_reset', FALSE );

if ( $optionReset == TRUE )
{
   print_successful_redirect ( plugin_page ( 'config_reset_ensure', TRUE ) );
}

if ( $optionChange == TRUE )
{
   rProApi::configUpdateButton ( 'show_menu' );
   rProApi::editPluginInWhiteboardMenu ( 'plugin_show_menu', gpc_get_int ( 'show_menu' ) );
   rProApi::configUpdateButton ( 'show_footer' );
   # change eta values when eta management is active
   if ( config_get ( 'enable_eta' ) )
   {
      rProApi::updateSingleValue ( 'defaulteta', 10 );
      if ( gpc_get_int ( 'calcthreshold' ) <= 100 && gpc_get_int ( 'calcthreshold' ) >= 0 )
      {
         rProApi::updateSingleValue ( 'calcthreshold', 0 );
      }
      rProApi::configProcessEta ();
      rProApi::configProcessTimeCalc ();
   }
   # change profile values
   rProApi::configProcessProfiles ();
   # change group values
   rProApi::configProcessGroups ();

   form_security_purge ( 'plugin_RoadmapPro_config_update' );
   print_successful_redirect ( plugin_page ( 'config_page', TRUE ) );
}