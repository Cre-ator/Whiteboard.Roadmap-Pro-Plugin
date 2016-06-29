<?php

require_once ( __DIR__ . '/../core/roadmap_pro_api.php' );

process_page ();

function process_page ()
{

    html_page_top1 ( plugin_lang_get ( 'menu_title' ) );
    echo '<link rel="stylesheet" href="plugins/RoadmapPro/files/roadmap.css"/>';
    html_page_top2 ();

    echo '<div align="center">';
    echo '<hr size="1" width="100%" />';
    process_table ();
    echo '</div>';
    html_page_bottom ();
}

function process_table ()
{
    $current_project_id = helper_get_current_project ();
    $versions = version_get_all_rows_with_subs ( $current_project_id, null, false );

    echo '<tbody>';
    foreach ( $versions as $version )
    {
        $bug_ids = roadmap_pro_api::get_bug_ids_by_version ( $version[ 'version' ] );

        var_dump ( $bug_ids );
    }
    echo '</tbody>';
}
