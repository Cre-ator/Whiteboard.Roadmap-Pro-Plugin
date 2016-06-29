<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );

auth_reauthenticate ();
access_ensure_global_level ( plugin_config_get ( 'roadmap_pro_access_level' ) );

html_page_top1 ( plugin_lang_get ( 'config_page_title' ) );
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
print_config_table_title_row ( 2, 'config_page_general' );
/** Access level */
print_config_table_row ();
echo '<td class="category">';
echo '<span class="required">*</span>' . plugin_lang_get ( 'config_page_access_level' );
echo '</td>';
echo '<td width="100px" colspan="2">';
echo '<select name="access_level">';
print_enum_string_option_list ( 'access_levels', plugin_config_get ( 'version_management_access_level', ADMINISTRATOR ) );
echo '</select>';
echo '</td>';
echo '</tr>';
/** Show menu */
print_config_table_row ();
print_config_table_category_col ( 1, 1, 'config_page_show_menu' );
print_config_table_radio_button_col ( 1, 'show_menu' );
echo '</tr>';
/** Show plugin information in footer */
print_config_table_row ();
print_config_table_category_col ( 1, 1, 'config_page_show_footer' );
print_config_table_radio_button_col ( 1, 'show_footer' );
echo '</tr>';

/** Profile Management */
print_config_table_title_row ( 2, 'config_page_roadmap_profile_management' );
/** Add new Profile */
print_config_table_row ();
print_config_table_category_col ( 1, 1, 'config_page_new_profile' );
echo '</tr>';

echo '<tr>';
echo '<td class="center" colspan="5">';
echo '<input type="submit" name="config_change" class="button" value="' . lang_get ( 'update_prefs_button' ) . '"/>' . ' ';
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
 * @param $default
 */
function print_config_table_color_picker_row ( $colspan, $name, $default )
{
    echo '<td width="100px" colspan="' . $colspan . '">';
    echo '<label>';
    echo '<input class="color {pickerFace:4,pickerClosable:true}" type="text" name="' . $name . '" value="' . plugin_config_get ( $name, $default ) . '" />';
    echo '</label>';
    echo '</td>';
}