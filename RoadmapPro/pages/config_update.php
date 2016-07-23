<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_db.php' );

auth_reauthenticate ();

$roadmapDb = new roadmap_db();
$optionChange = gpc_get_bool ( 'config_change', false );
$optionReset = gpc_get_bool ( 'config_reset', false );

if ( $optionReset )
{
   print_successful_redirect ( plugin_page ( 'config_reset_ensure', true ) );
}

if ( $optionChange )
{
   $postProfileIds = $_POST[ 'profile-id' ];
   $postProfileNames = $_POST[ 'profile-name' ];
   $postProfileColor = $_POST[ 'profile-color' ];
   $postProfilePriority = $_POST[ 'profile-prio' ];
   $postProfileEffort = $_POST[ 'profile-effort' ];

   if ( roadmap_pro_api::checkArrayForDuplicates ( $postProfileNames ) == true )
   {
      /** error message */
   }
   else
   {
      updateButton ( 'show_menu' );
      updateButton ( 'show_footer' );

      if ( config_get ( 'enable_eta' ) )
      {
         $postEtaValue = $_POST[ 'eta_value' ];
         $postEtaUnit = $_POST[ 'eta_unit' ];
         $etaEnumString = config_get ( 'eta_enum_string' );
         $etaEnumValues = MantisEnum::getValues ( $etaEnumString );

         $roadmapDb->dbUpdateEtaUnit ( $postEtaUnit );
         for ( $index = 0; $index < count ( $etaEnumValues ); $index++ )
         {
            $key = $etaEnumValues[ $index ];
            $value = $postEtaValue[ $index ];

            $roadmapDb->dbUpdateEtaKeyValue ( $key, $value );
         }
      }

      /** process existing profiles */
      $profileIdCount = count ( $postProfileIds );
      for ( $index = 0; $index < $profileIdCount; $index++ )
      {
         $profileName = $postProfileNames[ $index ];
         if ( strlen ( $profileName ) > 0 )
         {
            $postProfileStatus = $_POST[ 'profile-status-' . $index ];
            $profileStatus = roadmap_pro_api::generateDbStatusValueString ( $postProfileStatus );
            $profileId = $postProfileIds[ $index ];
            $profileColor = $postProfileColor[ $index ];
            $profilePriority = $postProfilePriority[ $index ];
            $profileEffort = $postProfileEffort[ $index ];

            $roadmapDb->dbUpdateProfile ( $profileId, $profileName, $profileColor, $profileStatus, $profilePriority, $profileEffort );
         }
      }

      /** process new profiles */
      $overallProfileCount = count ( $postProfileNames );
      $newStatusIndex = 0;
      for ( $newIndex = $profileIdCount; $newIndex < $overallProfileCount; $newIndex++ )
      {
         $newProfileName = $postProfileNames[ $newIndex ];
         if ( strlen ( $newProfileName ) > 0 )
         {
            $postNewProfileStatus = $_POST[ 'new-status-' . $newStatusIndex ];
            $newProfileStatus = roadmap_pro_api::generateDbStatusValueString ( $postNewProfileStatus );
            $newProfileColor = $postProfileColor[ $newIndex ];
            $newProfilePriority = $postProfilePriority[ $newIndex ];
            $newProfileEffort = $postProfileEffort[ $newIndex ];

            $roadmapDb->dbInsertProfile ( $newProfileName, $newProfileColor, $newProfileStatus, $newProfilePriority, $newProfileEffort );
         }

         $newStatusIndex++;
      }
   }
}

form_security_purge ( 'plugin_RoadmapPro_config_update' );

print_successful_redirect ( plugin_page ( 'config_page', true ) );

/**
 * Adds the "#"-Tag if necessary
 *
 * @param $color
 * @return string
 */
function includeLeadingColorIdentifier ( $color )
{
   if ( "#" == $color[ 0 ] )
   {
      return $color;
   }
   else
   {
      return "#" . $color;
   }
}

/**
 * Updates a specific color value in the plugin
 *
 * @param $fieldName
 * @param $defaultColor
 */
function updateColor ( $fieldName, $defaultColor )
{
   $defaultColor = includeLeadingColorIdentifier ( $defaultColor );
   $currentColor = includeLeadingColorIdentifier ( gpc_get_string ( $fieldName, $defaultColor ) );

   if ( plugin_config_get ( $fieldName ) != $currentColor && plugin_config_get ( $fieldName ) != '' )
   {
      plugin_config_set ( $fieldName, $currentColor );
   }
   elseif ( plugin_config_get ( $fieldName ) == '' )
   {
      plugin_config_set ( $fieldName, $defaultColor );
   }
}

/**
 * Updates the value set by a button
 *
 * @param $config
 */
function updateButton ( $config )
{
   $button = gpc_get_int ( $config );

   if ( plugin_config_get ( $config ) != $button )
   {
      plugin_config_set ( $config, $button );
   }
}

/**
 * Updates the value set by an input text field
 *
 * @param $value
 * @param $constant
 */
function updateSingleValue ( $value, $constant )
{
   $currentValue = null;

   if ( is_int ( $value ) )
   {
      $currentValue = gpc_get_int ( $value, $constant );
   }

   if ( is_string ( $value ) )
   {
      $currentValue = gpc_get_string ( $value, $constant );
   }

   if ( plugin_config_get ( $value ) != $currentValue )
   {
      plugin_config_set ( $value, $currentValue );
   }
}
