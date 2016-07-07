<?php

/**
 * Class version_management_api
 *
 * Contains functions for the plugin specific content
 */
class roadmap_pro_api
{
    /**
     * returns true, if the used mantis version is release 1.2.x
     *
     * @return bool
     */
    public static function check_mantis_version_is_released ()
    {
        return substr ( MANTIS_VERSION, 0, 4 ) == '1.2.';
    }

    /**
     * returns true, if there is a duplicate entry.
     *
     * @param $array
     * @return bool
     */
    public static function check_array_for_duplicates ( $array )
    {
        return count ( $array ) !== count ( array_unique ( $array ) );
    }

    /**
     * create a mysqli object
     *
     * @return mysqli
     */
    private static function get_mysqli_object ()
    {
        $dbPath = config_get ( 'hostname' );
        $dbUser = config_get ( 'db_username' );
        $dbPass = config_get ( 'db_password' );
        $dbName = config_get ( 'database_name' );

        return new mysqli( $dbPath, $dbUser, $dbPass, $dbName );
    }

    /**
     * returns all assigned bug ids to a given target version
     *
     * @param $project_id
     * @param $version_name
     * @return array|null
     */
    public static function get_bug_ids_by_project_and_version ( $project_id, $version_name )
    {
        $mysqli = self::get_mysqli_object ();

        $query = /** @lang sql */
            "SELECT id FROM mantis_bug_table
            WHERE target_version = '" . $version_name . "'
            AND project_id = " . $project_id;

        $result = $mysqli->query ( $query );

        $bug_ids = array ();
        if ( 0 != $result->num_rows )
        {
            while ( $row = $result->fetch_row () )
            {
                $bug_ids[] = $row[ 0 ];
            }
            return $bug_ids;
        }

        return null;
    }

    /**
     * inserts a new roadmap profile if there isnt a dupicate by name
     *
     * @param $profile_name
     * @param $profile_color
     * @param $profile_status
     */
    public static function insert_profile ( $profile_name, $profile_color, $profile_status )
    {
        $mysqli = self::get_mysqli_object ();

        $query = /** @lang sql */
            "INSERT INTO mantis_plugin_RoadmapPro_profile_table ( id, profile_name, profile_color, profile_status )
            SELECT null,'" . $profile_name . "','" . $profile_color . "','" . $profile_status . "'
            FROM DUAL WHERE NOT EXISTS (
            SELECT 1 FROM mantis_plugin_RoadmapPro_profile_table
            WHERE profile_name = '" . $profile_name . "')";

        $mysqli->query ( $query );
    }

    /**
     * get all roadmap profiles
     *
     * @return array|null
     */
    public static function get_roadmap_profiles ()
    {
        $mysqli = self::get_mysqli_object ();

        $query = /** @lang sql */
            "SELECT * FROM mantis_plugin_RoadmapPro_profile_table";

        $result = $mysqli->query ( $query );

        $profiles = array ();
        if ( 0 != $result->num_rows )
        {
            while ( $row = $result->fetch_row () )
            {
                $profiles[] = $row;
            }
            return $profiles;
        }

        return null;
    }

    /**
     * get a specific roadmap profile
     *
     * @param $profile_id
     * @return array|null
     */
    public static function get_roadmap_profile ( $profile_id )
    {
        $mysqli = self::get_mysqli_object ();

        $query = /** @lang sql */
            "SELECT * FROM mantis_plugin_RoadmapPro_profile_table
            WHERE id = " . $profile_id;

        $result = $mysqli->query ( $query );

        $profile = mysqli_fetch_row ( $result );

        return $profile;
    }

    /**
     * delete a roadmap profile by its primary id
     *
     * @param $profile_id
     */
    public static function delete_profile ( $profile_id )
    {
        $mysqli = self::get_mysqli_object ();

        $query = /** @lang sql */
            "DELETE FROM mantis_plugin_RoadmapPro_profile_table
            WHERE id = " . $profile_id;

        $mysqli->query ( $query );
    }

    /**
     * Reset all plugin-related data
     *
     * - config entries
     * - database entities
     */
    public static function reset_plugin ()
    {
        $mysqli = self::get_mysqli_object ();

        $query = /** @lang sql */
            "DROP TABLE mantis_plugin_RoadmapPro_profile_table";

        $mysqli->query ( $query );

        $query = /** @lang sql */
            "DELETE FROM mantis_config_table
            WHERE config_id LIKE 'plugin_RoadmapPro%'";

        $mysqli->query ( $query );

        print_successful_redirect ( 'manage_plugin_page.php' );
    }

    /**
     * returns true if every item of bug id array has set eta value
     *
     * @param $bug_ids
     * @return bool
     */
    public static function check_eta_is_set ( $bug_ids )
    {
        $set = true;
        foreach ( $bug_ids as $bug_id )
        {
            $bug_eta_value = bug_get_field ( $bug_id, 'eta' );
            if ( ( is_null ( $bug_eta_value ) ) || ( $bug_eta_value == 10 ) )
            {
                $set = false;
            }
        }

        return $set;
    }

    /**
     * returns the eta value of a single bug
     *
     * @param $bug_id
     * @return float|int
     */
    public static function get_single_eta ( $bug_id )
    {
        $eta = 0;
        $bug_eta_value = bug_get_field ( $bug_id, 'eta' );
        switch ( $bug_eta_value )
        {
            case 20:
                $eta += ETA20;
                break;
            case 30:
                $eta += ETA30;
                break;
            case 40:
                $eta += ETA40;
                break;
            case 50:
                $eta += ETA50;
                break;
            default:
                $eta += 0;
                break;
        }

        return $eta;
    }

    /**
     * returns the eta value of a bunch of bugs
     *
     * @param $bug_ids
     * @return float|int
     */
    public static function get_full_eta ( $bug_ids )
    {
        $full_eta = 0;
        foreach ( $bug_ids as $bug_id )
        {
            $bug_eta_value = bug_get_field ( $bug_id, 'eta' );

            switch ( $bug_eta_value )
            {
                case 20:
                    $full_eta += ETA20;
                    break;
                case 30:
                    $full_eta += ETA30;
                    break;
                case 40:
                    $full_eta += ETA40;
                    break;
                case 50:
                    $full_eta += ETA50;
                    break;
                default:
                    break;
            }
        }

        return $full_eta;
    }

    /**
     * returns true if the issue is done like it is defined in the profile preference
     *
     * @param $bug_id
     * @param $profile_id
     * @return bool
     */
    public static function check_issue_is_done ( $bug_id, $profile_id )
    {
        $done = false;

        $bug_status = bug_get_field ( $bug_id, 'status' );
        $roadmap_profile = roadmap_pro_api::get_roadmap_profile ( $profile_id );
        $db_raodmap_status = $roadmap_profile[ 3 ];
        $roadmap_status_array = explode ( ';', $db_raodmap_status );

        foreach ( $roadmap_status_array as $roadmap_status )
        {
            if ( $bug_status == $roadmap_status )
            {
                $done = true;
            }
        }

        return $done;
    }

    /**
     * returns the amount of done bugs in a bunch of bugs
     *
     * @param $bug_ids
     * @param $profile_id
     * @return int
     */
    public static function get_done_bug_amount ( $bug_ids, $profile_id )
    {
        $done_bug_amount = 0;

        foreach ( $bug_ids as $bug_id )
        {
            if ( self::check_issue_is_done ( $bug_id, $profile_id ) )
            {
                $done_bug_amount++;
            }
        }

        return $done_bug_amount;
    }

    /**
     * returns the ids of done bugs in a bunch of bugs
     *
     * @param $bug_ids
     * @param $profile_id
     * @return array
     */
    public static function get_done_bug_ids ( $bug_ids, $profile_id )
    {
        $done_bug_ids = array ();
        foreach ( $bug_ids as $bug_id )
        {
            if ( self::check_issue_is_done ( $bug_id, $profile_id ) )
            {
                array_push ( $done_bug_ids, $bug_id );
            }
        }

        return $done_bug_ids;
    }

    /**
     * returns all subproject ids incl. the selected one except it is zero
     *
     * @return array
     */
    public static function prepare_project_ids ()
    {
        $current_project_id = helper_get_current_project ();
        $sub_project_ids = project_hierarchy_get_all_subprojects ( $current_project_id );

        $project_ids = array ();
        if ( $current_project_id > 0 )
        {
            array_push ( $project_ids, $current_project_id );
        }

        foreach ( $sub_project_ids as $sub_project_id )
        {
            array_push ( $project_ids, $sub_project_id );
        }

        return $project_ids;
    }

    /**
     * returns an array with bug ids and extened information about relations
     *
     * @param $bug_ids
     * @return mixed
     */
    public static function calculate_bug_array_with_relationships ( $bug_ids )
    {
        $bug_count = count ( $bug_ids );
        $bug_hash_array = array ();
        for ( $bug_index = 0; $bug_index < $bug_count; $bug_index++ )
        {
            $bug_id = $bug_ids[ $bug_index ];
            $blocking_ids = '';
            $blocked_ids = '';
            if ( ( $bug_index >= 0 ) && ( $bug_index < ( $bug_count - 1 ) ) )
            {
                $next_bug_id = $bug_ids[ $bug_index + 1 ];

                $this_next_relationship = self::check_relationship ( $bug_id, $next_bug_id );
                $next_this_relationship = self::check_relationship ( $next_bug_id, $bug_id );

                /** this blocked from next -> this
                 *                            - next
                 */
                if ( $this_next_relationship )
                {
                    $blocking_ids = '';
                    $blocking_relationship_rows = self::get_relationship ( $bug_id, $next_bug_id );
                    foreach ( $blocking_relationship_rows as $row )
                    {
                        $blocking_id = $row[ 2 ];
                        $blocking_ids .= $blocking_id . ';';
                    }
                }
                /** next blocked from this -> next
                 *                            - this
                 */
                elseif ( $next_this_relationship )
                {
                    $blocked_ids = '';
                    $blocked_relationship_rows = self::get_relationship ( $next_bug_id, $bug_id );
                    foreach ( $blocked_relationship_rows as $row )
                    {
                        $blocked_id = $row[ 2 ];
                        $blocked_ids .= $blocked_id . ';';
                    }
                }
            }

            $bug_hash = array (
                'id' => $bug_id,
                'next_ids' => $blocking_ids,
                'previous_ids' => $blocked_ids,
            );

            array_push ( $bug_hash_array, $bug_hash );
        }

        return $bug_hash_array;
    }

    public static function sort_bug_ids_by_relationships ( $bug_hash_array )
    {
        $sorted_bug_ids = array ();
        $counter = 0;
        foreach ( $bug_hash_array as $bug_hash )
        {
            $is_blocker = false;
            $bug_id = $bug_hash[ 'id' ];
            /** if bug is blocker, move --> */
            if ( strlen ( $bug_hash[ 'previous_ids' ] ) > 0 )
            {
                $is_blocker = true;
            }
            /** if bug is blocked, hich prio */
            if ( strlen ( $bug_hash[ 'next_ids' ] ) > 0 )
            {
                $bug_data = array (
                    'counter' => $counter,
                    'id' => $bug_id,
                    'is_blocker' => $is_blocker
                );

                $counter++;
            }
            else
            {
                $bug_data = array (
                    'counter' => '',
                    'id' => $bug_id,
                    'is_blocker' => $is_blocker
                );
            }

            array_push ( $sorted_bug_ids, $bug_data );
        }

        return $sorted_bug_ids;
    }

    /**
     * returns true if there is a relationship for two given bug ids
     *
     * @param $bug_id_src
     * @param $bug_id_dest
     * @return bool
     */
    public static function check_relationship ( $bug_id_src, $bug_id_dest )
    {
        /** src_id - blocked */
        /** dest_id - blocking */

        /** - blocked
         *    - blocking
         *  - others...
         */

        $mysqli = self::get_mysqli_object ();

        $query = /** @lang sql */
            "SELECT * FROM mantis_bug_relationship_table
            WHERE source_bug_id = " . $bug_id_src . "
            AND destination_bug_id = " . $bug_id_dest . "
            AND relationship_type = 2";

        $result = $mysqli->query ( $query );

        if ( $result->num_rows > 0 )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * get the relationship rows for two given bug ids
     *
     * @param $bug_id_src
     * @param $bug_id_dest
     * @return array|null
     */
    public static function get_relationship ( $bug_id_src, $bug_id_dest )
    {
        /** src_id - blocked */
        /** dest_id - blocking */

        /** - blocked
         *    - blocking
         *  - others...
         */

        $mysqli = self::get_mysqli_object ();

        $query = /** @lang sql */
            "SELECT * FROM mantis_bug_relationship_table
            WHERE source_bug_id = " . $bug_id_src . "
            AND destination_bug_id = " . $bug_id_dest . "
            AND relationship_type = 2";

        $result = $mysqli->query ( $query );

        $relationships = array ();
        if ( 0 != $result->num_rows )
        {
            while ( $row = $result->fetch_row () )
            {
                $relationships[] = $row;
            }
            return $relationships;
        }

        return null;
    }
}
