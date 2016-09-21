<?php

class RoadmapProPlugin extends MantisPlugin
{
   private $shortName = null;

   function register ()
   {
      $this->shortName = 'RoadmapPro';
      $this->name = 'Whiteboard.' . $this->shortName;
      $this->description = 'Extended Roadmap with additional progress information';
      $this->page = 'config_page';

      $this->version = '1.2.13';
      $this->requires = array
      (
         'MantisCore' => '1.2.0, <= 1.3.99'
      );

      $this->author = 'cbb software GmbH (Rainer Dierck, Stefan Schwarz)';
      $this->contact = '';
      $this->url = 'https://github.com/Cre-ator';
   }

   function hooks ()
   {
      $hooks = array
      (
         'EVENT_LAYOUT_PAGE_FOOTER' => 'footer',
         'EVENT_MENU_MAIN' => 'menu'
      );
      return $hooks;
   }

   function init ()
   {
      require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rConst.php' );
   }

   function config ()
   {
      return array
      (
         'show_menu' => ON,
         'show_footer' => ON,
         'defaulteta' => 0,
         'calcthreshold' => 10
      );
   }

   function schema ()
   {
      require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProApi.php' );
      $tableArray = array ();

      $profileTable = array
      (
         'CreateTableSQL', array ( plugin_table ( 'profile' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            profile_name    C(250)  DEFAULT '',
            profile_color   C(250)  DEFAULT '',
            profile_status  C(250)  DEFAULT '',
            profile_prio    I       DEFAULT 0,
            profile_effort  I       DEFAULT 0
            " )
      );

      $profileGroupTable = array
      (
         'CreateTableSQL', array ( plugin_table ( 'profilegroup' ), "
            id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            group_name         C(250)  DEFAULT '',
            group_profiles     C(250)  DEFAULT ''
            " )
      );

      $etaThresholdTable = array
      (
         'CreateTableSQL', array ( plugin_table ( 'etathreshold', 'whiteboard' ), "
            id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            eta_thr_from       C(250)  DEFAULT '',
            eta_thr_to         C(250)  DEFAULT '',
            eta_thr_unit       C(250)  DEFAULT '',
            eta_thr_factor     C(250)  DEFAULT ''
            " )
      );

      $etaTable = array
      (
         'CreateTableSQL', array ( plugin_table ( 'eta', 'whiteboard' ), "
            id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            eta_config_value   C(250)  DEFAULT '',
            eta_user_value     C(250)  DEFAULT 0
            " )
      );

      $workDayTable = array
      (
         'CreateTableSQL', array ( plugin_table ( 'workday', 'whiteboard' ), "
            id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            workday_values     C(250)  DEFAULT ''
            " )
      );

      $whiteboardMenuTable = array
      (
         'CreateTableSQL', array ( plugin_table ( 'menu', 'whiteboard' ), "
            id                   I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            plugin_name          C(250)  DEFAULT '',
            plugin_access_level  I       UNSIGNED,
            plugin_show_menu     I       UNSIGNED,
            plugin_menu_path     C(250)  DEFAULT ''
            " )
      );

      array_push ( $tableArray, $profileTable );
      array_push ( $tableArray, $profileGroupTable );

      $boolArray = rProApi::checkWhiteboardTablesExist ();
      # add workday table if it does not exist
      if ( !$boolArray[ 3 ] )
      {
         array_push ( $tableArray, $workDayTable );
      }
      # add eta threshold table if it does not exist
      if ( !$boolArray[ 2 ] )
      {
         array_push ( $tableArray, $etaThresholdTable );
      }
      # add eta table if it does not exist
      if ( !$boolArray[ 1 ] )
      {
         array_push ( $tableArray, $etaTable );
      }
      # add whiteboardmenu table if it does not exist
      if ( !$boolArray[ 0 ] )
      {
         array_push ( $tableArray, $whiteboardMenuTable );
      }

      return $tableArray;
   }

   function footer ()
   {
      if ( plugin_config_get ( 'show_footer' ) )
      {
         return '<address>' . $this->shortName . ' ' . $this->version . ' Copyright &copy; 2016 by ' . $this->author . '</address>';
      }
      return null;
   }

   function menu ()
   {
      require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProApi.php' );
      if ( !rProApi::checkPluginIsRegisteredInWhiteboardMenu () )
      {
         rProApi::addPluginToWhiteboardMenu ();
         rProApi::setDefault ();
      }

      if ( ( !plugin_is_installed ( 'WhiteboardMenu' ) || !file_exists ( config_get_global ( 'plugin_path' ) . 'WhiteboardMenu' ) )
         && plugin_config_get ( 'show_menu' )
      )
      {
         return '<a href="' . plugin_page ( 'roadmap_page' ) . '">' . plugin_lang_get ( 'menu_title' ) . '</a >';
      }
      return null;
   }

   function uninstall ()
   {
      require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProApi.php' );
      rProApi::removePluginFromWhiteboardMenu ();
   }
}
