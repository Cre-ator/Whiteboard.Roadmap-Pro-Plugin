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
     * @param $profile_status
     */
    public static function insert_profile ( $profile_name, $profile_status )
    {
        $mysqli = self::get_mysqli_object ();

        $query = /** @lang sql */
            "INSERT INTO mantis_plugin_RoadmapPro_profile_table ( id, profile_name, profile_status )
            SELECT null,'" . $profile_name . "','" . $profile_status . "'
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
}
