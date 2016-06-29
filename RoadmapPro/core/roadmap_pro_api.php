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

    public static function get_bug_ids_by_version ( $version_name )
    {
        $dbPath = config_get ( 'hostname' );
        $dbUser = config_get ( 'db_username' );
        $dbPass = config_get ( 'db_password' );
        $dbName = config_get ( 'database_name' );

        $mysqli = new mysqli( $dbPath, $dbUser, $dbPass, $dbName );

        $query = /** @lang sql */
            "SELECT id FROM mantis_bug_table
            WHERE target_version = '" . $version_name . "'";

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
}
