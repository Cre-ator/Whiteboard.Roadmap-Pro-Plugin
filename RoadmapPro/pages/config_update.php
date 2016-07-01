<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );

auth_reauthenticate ();
access_ensure_global_level ( plugin_config_get ( 'roadmap_pro_access_level' ) );
form_security_validate ( 'plugin_RoadmapPro_config_update' );

$option_change = gpc_get_bool ( 'config_change', false );
$option_reset = gpc_get_bool ( 'config_reset', false );
$option_add_profile = gpc_get_bool ( 'add_profile', false );

if ( $option_add_profile )
{
    $profile_name = trim ( $_POST[ 'profile_name' ] );
    $profile_status = $_POST[ 'profile_status' ];

    roadmap_pro_api::insert_profile ( $profile_name, $profile_status );
}

if ( $option_reset )
{
    print_successful_redirect ( plugin_page ( 'config_reset_ensure', true ) );
}

if ( $option_change )
{
    update_single_value ( 'version_management_access_level', ADMINISTRATOR );
    update_button ( 'show_menu' );
    update_button ( 'show_footer' );
    update_color ( 'unused_version_row_color', '#908b2d' );
}

form_security_purge ( 'plugin_VersionManagement_config_update' );

print_successful_redirect ( plugin_page ( 'config_page', true ) );


/**
 * Adds the "#"-Tag if necessary
 *
 * @param $color
 * @return string
 */
function include_leading_color_identifier ( $color )
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
 * @param $field_name
 * @param $default_color
 */
function update_color ( $field_name, $default_color )
{
    $default_color = include_leading_color_identifier ( $default_color );
    $iA_background_color = include_leading_color_identifier ( gpc_get_string ( $field_name, $default_color ) );

    if ( plugin_config_get ( $field_name ) != $iA_background_color && plugin_config_get ( $field_name ) != '' )
    {
        plugin_config_set ( $field_name, $iA_background_color );
    }
    elseif ( plugin_config_get ( $field_name ) == '' )
    {
        plugin_config_set ( $field_name, $default_color );
    }
}

/**
 * Updates the value set by a button
 *
 * @param $config
 */
function update_button ( $config )
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
function update_single_value ( $value, $constant )
{
    $act_value = null;

    if ( is_int ( $value ) )
    {
        $act_value = gpc_get_int ( $value, $constant );
    }

    if ( is_string ( $value ) )
    {
        $act_value = gpc_get_string ( $value, $constant );
    }

    if ( plugin_config_get ( $value ) != $act_value )
    {
        plugin_config_set ( $value, $act_value );
    }
}

/**
 * Iterates through a specific amount and updates each value
 *
 * @param $value
 * @param $constant
 */
function update_multiple_values ( $value, $constant )
{
    $column_amount = plugin_config_get ( 'CAmount' );

    for ( $columnIndex = 1; $columnIndex <= $column_amount; $columnIndex++ )
    {
        $act_value = $value . $columnIndex;

        update_single_value ( $act_value, $constant );
    }
}
