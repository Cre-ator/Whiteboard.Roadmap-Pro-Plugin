<?php
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rGroup.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProfile.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rThreshold.php' );

auth_reauthenticate ();

# delete group if group id is send
if ( isset( $_GET[ 'group_id' ] ) )
{
   $getGroupId = $_GET[ 'group_id' ];
   $group = new rGroup( $getGroupId );
   $group->triggerDeleteFromDb ();
}

# delete profile if profile id is send
if ( isset( $_GET[ 'profile_id' ] ) )
{
   $getProfileId = $_GET[ 'profile_id' ];
   $profile = new rProfile( $getProfileId );
   $profile->triggerDeleteFromDb ();
}

# delete threshold if threshold id is send
if ( isset( $_GET[ 'threshold_id' ] ) )
{
   $getThresholdId = $_GET[ 'threshold_id' ];
   $threshold = new rThreshold( $getThresholdId );
   $threshold->triggerDeleteFromDb ();
}

# redirect to view page
print_successful_redirect ( plugin_page ( 'config_page', TRUE ) );