<?php
require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/rProfileManager.php' );
require_once ( __DIR__ . '/../core/rProfile.php' );
require_once ( __DIR__ . '/../core/rGroupManager.php' );
require_once ( __DIR__ . '/../core/rGroup.php' );
require_once ( __DIR__ . '/../core/rEta.php' );

auth_reauthenticate ();

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
   processGroups ();

   form_security_purge ( 'plugin_RoadmapPro_config_update' );
   print_successful_redirect ( plugin_page ( 'config_page', true ) );
}

function processEta ()
{
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
      $etaConfig = $etaEnumValues[ $index ];
      $etaUser = $postEtaValue[ $index ];
      $eta = new rEta( $etaConfig );
      $etaIsSet = $eta->getEtaIsSet ();
      if ( $etaIsSet )
      {
         $eta->setEtaConfig ( $etaConfig );
         $eta->setEtaUser ( $etaUser );
         $eta->triggerUpdateInDb ();
      }
      else
      {
         $eta->setEtaConfig ( $etaConfig );
         $eta->setEtaUser ( $etaUser );
         $eta->triggerInsertIntoDb ();
      }
   }

   if ( $postEtaThresholdFrom != null )
   {
      # process existing thresholds
      $thresholdIdCount = count ( $postEtaThresholdIds );
      for ( $index = 0; $index < $thresholdIdCount; $index++ )
      {
         $thresholdUnit = $postEtaThresholdUnit[ $index ];
         if ( strlen ( $thresholdUnit ) > 0 )
         {
            $thresholdId = $postEtaThresholdIds[ $index ];
            $threshold = new rThreshold( $thresholdId );
            $threshold->setThresholdFrom ( $postEtaThresholdFrom[ $index ] );
            $threshold->setThresholdTo ( $postEtaThresholdTo[ $index ] );
            $threshold->setThresholdUnit ( $thresholdUnit );
            $threshold->setThresholdFactor ( $postEtaThresholdFactor[ $index ] );
            $threshold->triggerUpdateInDb ();
         }
      }

      # process new thresholds
      $overallThresholdCount = count ( $postEtaThresholdFrom );
      $newThresholdIndex = 0;
      for ( $newIndex = $thresholdIdCount; $newIndex < $overallThresholdCount; $newIndex++ )
      {
         $newThreshold = new rThreshold();
         $newThreshold->setThresholdFrom ( $postEtaThresholdFrom[ $newIndex ] );
         $newThreshold->setThresholdTo ( $postEtaThresholdTo[ $newIndex ] );
         $newThresholdUnit = $_POST[ 'new-threshold-unit-' . $newThresholdIndex ];
         $newThreshold->setThresholdUnit ( $newThresholdUnit );
         $newThreshold->setThresholdFactor ( $postEtaThresholdFactor[ $newIndex ] );
         $newThreshold->triggerInsertIntoDb ();
         $newThresholdIndex++;
      }
   }
}

function processProfiles ()
{
   $postProfileIds = $_POST[ 'profile-id' ];
   $postProfileNames = $_POST[ 'profile-name' ];
   $postProfileColor = $_POST[ 'profile-color' ];
   $postProfilePriority = $_POST[ 'profile-prio' ];
   $postProfileEffort = $_POST[ 'profile-effort' ];

   if ( $postProfileNames != null )
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
            $profileName = $postProfileNames[ $index ];
            if ( strlen ( $profileName ) > 0 )
            {
               $profileId = $postProfileIds[ $index ];
               $profile = new rProfile( $profileId );
               $profile->setProfileName ( $profileName );
               $postProfileStatus = $_POST[ 'profile-status-' . $index ];
               $profile->setProfileStatus ( roadmap_pro_api::generateDbValueString ( $postProfileStatus ) );
               $profile->setProfileColor ( $postProfileColor[ $index ] );
               $profile->setProfilePriority ( $postProfilePriority[ $index ] );
               $profile->setProfileEffort ( $postProfileEffort[ $index ] );
               $profile->triggerUpdateInDb ();
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
               $newProfile = new rProfile();
               $newProfile->setProfileName ( $newProfileName );
               $postNewProfileStatus = $_POST[ 'new-status-' . $newStatusIndex ];
               $newProfile->setProfileStatus ( roadmap_pro_api::generateDbValueString ( $postNewProfileStatus ) );
               $newProfile->setProfileColor ( $postProfileColor[ $newIndex ] );
               $newProfile->setProfilePriority ( $postProfilePriority[ $newIndex ] );
               $newProfile->setProfileEffort ( $postProfileEffort[ $newIndex ] );
               $newProfile->triggerInsertIntoDb ();
            }

            $newStatusIndex++;
         }
      }
   }
}

function processGroups ()
{
   $postGroupIds = $_POST[ 'group-id' ];
   $postGroupNames = $_POST[ 'group-name' ];

   if ( $postGroupNames != null )
   {
      if ( roadmap_pro_api::checkArrayForDuplicates ( $postGroupNames ) == true )
      {
         # error message
      }
      else
      {
         # process existing groups
         $groupIdCount = count ( $postGroupIds );
         for ( $index = 0; $index < $groupIdCount; $index++ )
         {
            $groupName = $postGroupNames[ $index ];
            if ( strlen ( $groupName ) > 0 )
            {
               $groupId = $postGroupIds[ $index ];
               $group = new rGroup( $groupId );
               $group->setGroupName ( $groupName );
               $postGroupProfiles = $_POST[ 'group-profile-' . $index ];
               $group->setGroupProfiles ( roadmap_pro_api::generateDbValueString ( $postGroupProfiles ) );
               $group->triggerUpdateInDb ();
            }
         }

         # process new groups
         $overallGroupCount = count ( $postGroupNames );
         $newGroupProfileIndex = 0;
         for ( $newIndex = $groupIdCount; $newIndex < $overallGroupCount; $newIndex++ )
         {
            $newGroupName = $postGroupNames[ $newIndex ];
            if ( strlen ( $newGroupName ) > 0 )
            {
               $newGroup = new rGroup();
               $newGroup->setGroupName ( $newGroupName );
               $postNewGroupProfiles = $_POST[ 'new-group-profile-' . $newGroupProfileIndex ];
               $newGroup->setGroupProfiles ( roadmap_pro_api::generateDbValueString ( $postNewGroupProfiles ) );
               $newGroup->triggerInsertIntoDb ();
            }

            $newGroupProfileIndex++;
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
