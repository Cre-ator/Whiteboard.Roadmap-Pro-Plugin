<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );
require_once ( __DIR__ . '/../core/roadmap_constant_api.php' );

process_page ();

function process_page ()
{
    $roadmap_profile_id = $_GET[ 'profile_id' ];
    $profile_color = 'FFFFFF';
    if ( is_null ( $roadmap_profile_id ) == false )
    {
        $roadmap_profile = roadmap_pro_api::get_roadmap_profile ( $roadmap_profile_id );
        $profile_color = $roadmap_profile[ 2 ];
    }

    html_page_top1 ( plugin_lang_get ( 'menu_title' ) );
    echo '<link rel="stylesheet" href="' . EOADMAPPRO_PLUGIN_URL . 'files/progress.css.php?profile_color=' . $profile_color . '"/>' . "\n";
    html_page_top2 ();

    if ( plugin_is_installed ( 'WhiteboardMenu' ) &&
        file_exists ( config_get_global ( 'plugin_path' ) . 'WhiteboardMenu' )
    )
    {
        require_once __DIR__ . '/../../WhiteboardMenu/core/whiteboard_print_api.php';
        whiteboard_print_api::printWhiteboardMenu ();
    }

    /** print profile menu bar */
    print_profile_switcher ();

    if ( isset( $_GET[ 'profile_id' ] ) )
    {
        $profile_id = $_GET[ 'profile_id' ];
        echo '<div align="center">';
        echo '<hr size="1" width="100%" />';
        echo '<table>';
        process_table ( $profile_id );
        echo '</table>';
        echo '</div>';
    }

    html_page_bottom ();
}

function process_table ( $profile_id )
{
    $get_version_id = $_GET[ 'version_id' ];
    $get_project_id = $_GET[ 'project_id' ];

    $project_ids = roadmap_pro_api::prepare_project_ids ();

    /** specific project selected */
    if ( $get_project_id != null )
    {
        $project_ids = array ();
        array_push ( $project_ids, $get_project_id );
    }

    echo '<tbody>';
    /** iterate through projects */
    foreach ( $project_ids as $project_id )
    {
        $user_access_lecel = user_get_access_level ( auth_get_current_user_id (), $project_id );
        $has_project_level = access_has_project_level ( $user_access_lecel, $project_id );
        /** skip if user has no access to project */
        if ( $has_project_level == false )
        {
            continue;
        }

        $printed_project_title = false;
        $project_name = project_get_name ( $project_id );
        $versions = array_reverse ( version_get_all_rows ( $project_id ) );

        /** specific version selected */
        if ( $get_version_id != null )
        {
            $version = array ();
            $version[ 'id' ] = $get_version_id;
            $version[ 'version' ] = version_get_field ( $get_version_id, 'version' );
            $version[ 'date_order' ] = version_get_field ( $get_version_id, 'date_order' );
            $version[ 'released' ] = version_get_field ( $get_version_id, 'released' );
            $version[ 'description' ] = version_get_field ( $get_version_id, 'description' );

            $versions = array ();
            array_push ( $versions, $version );
        }

        /** iterate through versions */
        foreach ( $versions as $version )
        {
            $version_id = $version[ 'id' ];
            $version_name = $version[ 'version' ];
            $version_date = $version[ 'date_order' ];
            $version_released = $version[ 'released' ];
            $version_description = $version[ 'description' ];

            /** skip released versions */
            if ( $version_released == 1 )
            {
                continue;
            }

            $release_date = string_display_line ( date ( config_get ( 'short_date_format' ), $version_date ) );

            $bug_ids = roadmap_pro_api::get_bug_ids_by_project_and_version ( $project_id, $version_name );
            $overall_bug_amount = count ( $bug_ids );

            if ( $overall_bug_amount > 0 )
            {
                if ( $printed_project_title == false )
                {
                    echo '<tr><td><span class="pagetitle">' . string_display ( $project_name ), '&nbsp;-&nbsp;' . lang_get ( 'roadmap' ) . '</span></td></tr>';
                    $printed_project_title = true;
                }
                $release_title = '<a href="' . plugin_page ( 'roadmap_page' )
                    . '&amp;profile_id=' . $profile_id . '&amp;project_id=' . $project_id . '">'
                    . string_display_line ( $project_name ) . '</a>&nbsp;-'
                    . '&nbsp;<a href="' . plugin_page ( 'roadmap_page' )
                    . '&amp;profile_id=' . $profile_id . '&amp;version_id=' . $version_id . '">'
                    . string_display_line ( $version_name ) . '</a>';

                echo '<tr>';
                echo '<td>';
                echo $release_title . '&nbsp;(' . lang_get ( 'scheduled_release' ) . '&nbsp;' . $release_date . ')&nbsp;'
                    . lang_get ( 'word_separator' ),
                print_bracket_link ( 'view_all_set.php?type=1&temporary=y&' . FILTER_PROPERTY_PROJECT_ID . '=' . $project_id
                    . '&' . filter_encode_field_and_value ( FILTER_PROPERTY_TARGET_VERSION, $version_name ),
                    lang_get ( 'view_bugs_link' )
                );
                echo '</td>';
                echo '</tr>';

                echo '<tr><td>' . $version_description . '</td></tr>';

                $release_title_without_hyperlinks = $project_name . ' - ' . $version_name . $release_date;
                echo '<tr><td>' . utf8_str_pad ( '', utf8_strlen ( $release_title_without_hyperlinks ), '=' ) . '</td></tr>';

                $done_bug_amount = roadmap_pro_api::get_done_bug_amount ( $bug_ids, $profile_id );
                $version_progress = round ( ( $done_bug_amount / $overall_bug_amount ), 4 );
                $use_time_calculation = roadmap_pro_api::check_eta_is_set ( $bug_ids );
                print_version_progress ( $bug_ids, $profile_id, $version_progress, $use_time_calculation );
                print_bug_list ( $bug_ids, $profile_id );
                print_text_version_progress ( $overall_bug_amount, $done_bug_amount, $version_progress, $use_time_calculation );
            }
        }
    }
    echo '</tbody>';
}

function print_profile_switcher ()
{
    $get_version_id = $_GET[ 'version_id' ];
    $get_project_id = $_GET[ 'project_id' ];

    $roadmap_profiles = roadmap_pro_api::get_roadmap_profiles ();

    echo '<table align="center">';
    echo '<tbody>';
    echo '<tr>';
    foreach ( $roadmap_profiles as $roadmap_profile )
    {
        $profile_id = $roadmap_profile[ 0 ];
        $profile_name = $roadmap_profile[ 1 ];

        echo '<td>';
        echo '[ <a href="' . plugin_page ( 'roadmap_page' ) . '&amp;profile_id=' . $profile_id;
        if ( $get_version_id != null )
        {
            echo '&amp;version_id=' . $get_version_id;
        }
        if ( $get_project_id != null )
        {
            echo '&amp;project_id=' . $get_project_id;
        }
        echo '">';
        echo string_display ( $profile_name );
        echo '</a> ]';
        echo '</td>';
    }
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
}

function print_version_progress ( $bug_ids, $profile_id, $version_progress, $use_time_calculation )
{
    echo '<tr><td>';
    if ( $use_time_calculation )
    {
        $full_eta = roadmap_pro_api::get_full_eta ( $bug_ids );
        $done_eta = 0;
        $done_bug_ids = roadmap_pro_api::get_done_bug_ids ( $bug_ids, $profile_id );
        foreach ( $done_bug_ids as $done_bug_id )
        {
            $done_eta += roadmap_pro_api::get_single_eta ( $done_bug_id );
        }

        $progress_time = round ( ( ( $done_eta / $full_eta ) * 100 ), 2 );

        echo '<div class="progress9000">';
        echo '<span class="bar" style="width: ' . $progress_time . '%;">' . $done_eta . '&nbsp;' . lang_get ( 'from' ) . '&nbsp;' . $full_eta . '&nbsp;(' . plugin_lang_get ( 'roadmap_page_bar_time' ) . ')</span>';
        echo '</div>';
    }
    else
    {
        $progress_percent = round ( ( $version_progress * 100 ), 2 );
        echo '<div class="progress9000">';
        echo '<span class="bar" style="width: ' . $progress_percent . '%;">' . $progress_percent . '%&nbsp;(' . plugin_lang_get ( 'roadmap_page_bar_amount' ) . ')</span>';
        echo '</div>';
    }
    echo '</td></tr>';
}

function print_bug_list ( $bug_ids, $profile_id )
{
    $bug_hash_array = roadmap_pro_api::calculate_bug_array_with_relationships ( $bug_ids );
    var_dump ( $bug_hash_array );
    $sorted_bugs = roadmap_pro_api::sort_bug_ids_by_relationships ( $bug_hash_array );
    var_dump ( $sorted_bugs );
    foreach ( $bug_ids as $bug_id )
    {
        $user_id = bug_get_field ( $bug_id, 'handler_id' );
        $bug_eta = bug_get_field ( $bug_id, 'eta' );
        echo '<tr><td>';
        /** line through, if bug is done */
        if ( roadmap_pro_api::check_issue_is_done ( $bug_id, $profile_id ) )
        {
            echo '<span style="text-decoration: line-through;">';
        }
        echo print_bug_link ( $bug_id, bug_format_id ( $bug_id ) ) . ':&nbsp;';
        if ( ( $bug_eta > 10 ) && config_get ( 'enable_eta' ) )
        {
            echo '<img src="' . EOADMAPPRO_PLUGIN_URL . 'files/clock.png' . '" alt="clock" height="12" width="12" />&nbsp;';
        }
        echo string_display ( bug_get_field ( $bug_id, 'summary' ) )
            . '&nbsp;(';
        echo '<a href="' . config_get ( 'path' ) . '/view_user_page.php?id=' . $user_id . '">';
        echo user_get_name ( $user_id );
        echo '</a>';

        echo ')&nbsp;-&nbsp;'
            . string_display_line ( get_enum_element ( 'status', bug_get_field ( $bug_id, 'status' ) ) ) . '.';
        if ( roadmap_pro_api::check_issue_is_done ( $bug_id, $profile_id ) )
        {
            echo '</span>';
        }
        echo '</td></tr>';
    }
}

function print_text_version_progress ( $overall_bug_amount, $done_bug_amount, $version_progress, $use_time_calculation )
{
    $progress_percent = round ( ( $version_progress * 100 ), 2 );

    echo '<tr><td>';
    if ( $use_time_calculation )
    {
        echo sprintf ( plugin_lang_get ( 'roadmap_page_resolved_time' ), $done_bug_amount, $overall_bug_amount );
    }
    else
    {
        echo sprintf ( lang_get ( 'resolved_progress' ), $done_bug_amount, $overall_bug_amount, $progress_percent );
    }
    echo '</td></tr>';
    /** spacer */
    echo '<tr><td height="20px"></td></tr>';
}
