<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_db.php' );

auth_reauthenticate ();

$roadmapDb = new roadmap_db();
$optionChange = gpc_get_bool ( 'config_change', false );
$optionReset = gpc_get_bool ( 'config_reset', false );

if ( $optionReset == true )
{
   print_successful_redirect ( plugin_page ( 'config_reset_ensure', true ) );
}

if ( $optionChange == true )
{
   updateButton ( 'show_menu' );
   updateButton ( 'show_footer' );
   if ( config_get ( 'enable_eta' ) )
   {
      processEta ();
   }
   processProfiles ();

   form_security_purge ( 'plugin_RoadmapPro_config_update' );
   print_successful_redirect ( plugin_page ( 'config_page', true ) );
}

function processEta ()
{
   global $roadmapDb;

   $postEtaThresholdIds = $_POST[ 'threshold-id' ];
   $postEtaThresholdFrom = $_POST[ 'threshold-from' ];
   $postEtaThresholdTo = $_POST[ 'threshold-to' ];
   $postEtaThresholdUnit = $_POST[ 'threshold-unit' ];
   $postEtaThresholdFactor = $_POST[ 'threshold-factor' ];

   $postEtaValue = $_POST[ 'eta_value' ];
   $etaEnumString = config_get ( 'eta_enum_string' );
   $etaEnumValues = MantisEnum::getValues ( $etaEnumString );

   for ( $index = 0; $index < count ( $etaEnumValues ); $index++ )
   {
      $key = $etaEnumValues[ $index ];
      $value = $postEtaValue[ $index ];

      $roadmapDb->dbUpdateEtaUserValue ( $key, $value );
   }

   if ( is_null ( $postEtaThresholdFrom ) == false )
   {
      # process existing thresholds
      $thresholdIdCount = count ( $postEtaThresholdIds );
      for ( $index = 0; $index < $thresholdIdCount; $index++ )
      {
         $thresholdUnit = $postEtaThresholdUnit[ $index ];
         if ( strlen ( $thresholdUnit ) > 0 )
         {
            $thresholdId = $postEtaThresholdIds[ $index ];
            $thresholdFrom = $postEtaThresholdFrom[ $index ];
            $thresholdTo = $postEtaThresholdTo[ $index ];
            $thresholdFactor = $postEtaThresholdFactor[ $index ];

            $roadmapDb->dbUpdateEtaThresholdValue ( $thresholdId, $thresholdFrom, $thresholdTo, $thresholdUnit, $thresholdFactor );
         }
      }

      # process new thresholds
      $overallThresholdCount = count ( $postEtaThresholdFrom );
      $newThresholdIndex = 0;
      for ( $newIndex = $thresholdIdCount; $newIndex < $overallThresholdCount; $newIndex++ )
      {
         $newThresholdUnit = $_POST[ 'new-threshold-unit-' . $newThresholdIndex ];
         $newThresholdFrom = $postEtaThresholdFrom[ $newIndex ];
         $newThresholdTo = $postEtaThresholdTo[ $newIndex ];
         $thresholdFactor = $postEtaThresholdFactor[ $newIndex ];

         $roadmapDb->dbInsertEtaThresholdValue ( $newThresholdFrom, $newThresholdTo, $newThresholdUnit, $thresholdFactor );

         $newThresholdIndex++;
      }
   }
}

function processProfiles ()
{
   global $roadmapDb;

   $postProfileIds = $_POST[ 'profile-id' ];
   $postProfileNames = $_POST[ 'profile-name' ];
   $postProfileColor = $_POST[ 'profile-color' ];
   $postProfilePriority = $_POST[ 'profile-prio' ];
   $postProfileEffort = $_POST[ 'profile-effort' ];

   if ( is_null ( $postProfileNames ) == false )
   {
      if ( roadmap_pro_api::checkArrayForDuplicates ( $postProfileNames ) == true )
      {
         # error message
      }
      else
      {
         # process existing profiles
         $profileIdCount = count ( $postProfileIds );
         for ( $index = 0; $index < $profileIdCount; $index++ )
         {
            $thresholdUnit = $postProfileNames[ $index ];
            if ( strlen ( $thresholdUnit ) > 0 )
            {
               $postProfileStatus = $_POST[ 'profile-status-' . $index ];
               $profileStatus = roadmap_pro_api::generateDbStatusValueString ( $postProfileStatus );
               $thresholdId = $postProfileIds[ $index ];
               $thresholdFrom = $postProfileColor[ $index ];
               $thresholdTo = $postProfilePriority[ $index ];
               $profileEffort = $postProfileEffort[ $index ];

               $roadmapDb->dbUpdateProfile ( $thresholdId, $thresholdUnit, $thresholdFrom, $profileStatus, $thresholdTo, $profileEffort );
            }
         }

         # process new profiles
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
}


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
