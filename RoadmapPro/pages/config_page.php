<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );

auth_reauthenticate ();
access_ensure_global_level ( plugin_config_get ( 'access_level' ) );

html_page_top1 ( plugin_lang_get ( 'config_page_title' ) );
echo '<script type="text/javascript" src="plugins/RoadmapPro/files/jscolor/jscolor.js"></script>';
html_page_top2 ();
print_manage_menu ();


echo '<br/>';
echo '<form action="' . plugin_page ( 'config_update' ) . '" method="post">';
echo form_security_field ( 'plugin_RoadmapPro_config_update' );

if ( roadmap_pro_api::check_mantis_version_is_released () )
{
    echo '<table align="center" class="width75" cellspacing="1">';
}
else
{
    echo '<div class="form-container">';
    echo '<table>';
}

/** General configuration */
print_config_table_title_row ( 6, 'config_page_general' );
/** Access level */
print_config_table_row ();
echo '<td class="category">';
echo '<span class="required">*</span>' . plugin_lang_get ( 'config_page_access_level' );
echo '</td>';
echo '<td width="100px" colspan="6">';
echo '<select name="roadmap_pro_access_level">';
print_enum_string_option_list ( 'access_levels', plugin_config_get ( 'access_level', ADMINISTRATOR ) );
echo '</select>';
echo '</td>';
echo '</tr>';
/** Show menu */
print_config_table_row ();
print_config_table_category_col ( 1, 1, 'config_page_show_menu' );
print_config_table_radio_button_col ( 5, 'show_menu' );
echo '</tr>';
/** Show plugin information in footer */
print_config_table_row ();
print_config_table_category_col ( 1, 1, 'config_page_show_footer' );
print_config_table_radio_button_col ( 5, 'show_footer' );
echo '</tr>';

/** eta management */
print_config_table_title_row ( 6, 'config_page_eta_management' );
print_config_table_row ();
print_config_table_category_col ( 1, 1, 'config_page_profile_name' );




/** Profile Management */
print_config_table_title_row ( 6, 'config_page_roadmap_profile_management' );
/** Add new Profile */
print_config_table_row ();
print_config_table_category_col ( 1, 1, 'config_page_profile_name' );
/** profile name */
echo '<td width="100px">';
echo '<input type="text" id="profile_name" name="profile_name" size="15" maxlength="128" value="">';
echo '</td>';
/** select status, selected => new */
echo '<td>';
echo '<select name="profile_status[]" multiple="multiple">';
print_enum_string_option_list ( 'status' );
echo '</select>';
echo '</td>';
/** profile color picker */
print_config_table_color_picker_row ( 1, 'profile_color' );
/** submit new profile */
echo '<td colspan="2">';
echo '<input type="submit" name="add_profile" class="button" value="' . plugin_lang_get ( 'config_page_add_profile' ) . '">';
echo '</td>';
echo '</tr>';

/** show profiles */

$profiles = roadmap_pro_api::get_roadmap_profiles ();
if ( empty( $profiles ) == false )
{
    print_config_table_row ();
    print_config_table_title_row ( 5, 'config_page_profile_list' );
    foreach ( $profiles as $profile )
    {
        $profile_id = $profile[ 0 ];
        $profile_name = $profile[ 1 ];
        $db_profile_color = $profile[ 2 ];
        $db_profile_status = $profile[ 3 ];
        $profile_status_array = explode ( ';', $db_profile_status );

        print_config_table_row ();
        /** profile name */
        print_config_table_category_col ( 1, 1, 'config_page_profile_name' );
        echo '<td>' . string_display_line ( $profile_name ) . '</td>';
        /** profile status */
        print_config_table_category_col ( 1, 1, 'config_page_profile_status' );
        echo '<td>';
        $counter = count ( $profile_status_array );
        for ( $status_index = 0; $status_index < $counter; $status_index++ )
        {
            $profile_status = $profile_status_array[ $status_index ];
            echo string_display_line ( get_enum_element ( 'status', $profile_status ) );
            if ( $status_index < ( $counter - 1 ) )
            {
                echo ',&nbsp';
            }
        }
        echo '</td>';

        /** profile color */
        echo '<td style="background-color: #' . $db_profile_color . '"></td>';

        /** delete profile button */
        echo '<td>';
        echo '<a style="text-decoration: none;" href="' . plugin_page ( 'config_delete_profile' ) .
            '&amp;profile_id=' . $profile_id . '">';
        echo '<span class="input">';
        echo '<input type="button" value="' . plugin_lang_get ( 'config_page_delete_profile' ) . '" />';
        echo '</span>';
        echo '</a>';
        echo '</td>';

        echo '</tr>';
    }
    echo '</tr>';
}


echo '<tr>';
echo '<td class="center" colspan="6">';
echo '<input type="submit" name="config_change" class="button" value="' . lang_get ( 'update_prefs_button' ) . '"/>&nbsp';
echo '<input type="submit" name="config_reset" class="button" value="' . lang_get ( 'reset_prefs_button' ) . '"/>';
echo '</td>';
echo '</tr>';

echo '</table>';

if ( roadmap_pro_api::check_mantis_version_is_released () == false )
{
    echo '</div>';
}

echo '</form>';
html_page_bottom1 ();


/**
 * Prints a table row in the plugin config area
 */
function print_config_table_row ()
{
    if ( roadmap_pro_api::check_mantis_version_is_released () )
    {
        echo '<tr ' . helper_alternate_class () . '>';
    }
    else
    {
        echo '<tr>';
    }
}

/**
 * Prints a category column in the plugin config area
 *
 * @param $colspan
 * @param $rowspan
 * @param $lang_string
 */
function print_config_table_category_col ( $colspan, $rowspan, $lang_string )
{
    echo '<td class="category" colspan="' . $colspan . '" rowspan="' . $rowspan . '">';
    echo plugin_lang_get ( $lang_string );
    echo '</td>';
}

/**
 * Prints a title row in the plugin config area
 *
 * @param $colspan
 * @param $lang_string
 */
function print_config_table_title_row ( $colspan, $lang_string )
{
    echo '<tr>';
    echo '<td class="form-title" colspan="' . $colspan . '">';
    echo plugin_lang_get ( $lang_string );
    echo '</td>';
    echo '</tr>';
}

/**
 * Prints a radio button element in the plugin config area
 *
 * @param $colspan
 * @param $name
 */
function print_config_table_radio_button_col ( $colspan, $name )
{
    echo '<td width="100px" colspan="' . $colspan . '">';
    echo '<label>';
    echo '<input type="radio" name="' . $name . '" value="1"';
    echo ( ON == plugin_config_get ( $name ) ) ? 'checked="checked"' : '';
    echo '/>' . lang_get ( 'yes' );
    echo '</label>';
    echo '<label>';
    echo '<input type="radio" name="' . $name . '" value="0"';
    echo ( OFF == plugin_config_get ( $name ) ) ? 'checked="checked"' : '';
    echo '/>' . lang_get ( 'no' );
    echo '</label>';
    echo '</td>';
}

/**
 * Prints a color picker element in the plugin config area
 *
 * @param $colspan
 * @param $name
 */
function print_config_table_color_picker_row ( $colspan, $name )
{
    echo '<td width="100px" colspan="' . $colspan . '">';
    echo '<label>';
    echo '<input class="color {pickerFace:4,pickerClosable:true}" type="text" name="' . $name . '" value="" />';
    echo '</label>';
    echo '</td>';
}

/**
 * @param $colspan
 * @param $name
 */
function print_config_table_text_input_field ( $colspan, $name )
{
    $profile = gpc_get_string ( 'profile', '' );
    echo '<td width="100px" colspan="' . $colspan . '">';
    echo '<input type="text" id="type" name="' . $name . '" size="15" maxlength="128" value="' . $profile . '">&nbsp';
    echo '<input type="submit" name="add_profile" class="button" value="' . plugin_lang_get ( 'config_page_add_profile' ) . '">';
    echo '</td>';
}