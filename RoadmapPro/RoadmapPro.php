<?php

class RoadmapProPlugin extends MantisPlugin
{
   function register ()
   {
      $this->name = 'RoadmapPro';
      $this->description = 'Extended Roadmap with additional progress information';
      $this->page = 'config_page';

      $this->version = '1.0.14';
      $this->requires = array
      (
         'MantisCore' => '1.2.0, <= 1.3.99'
      );

      $this->author = 'Stefan Schwarz';
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

   function config ()
   {
      return array
      (
         'show_menu' => ON,
         'show_footer' => ON,
         'access_level' => ADMINISTRATOR
      );
   }

   function schema ()
   {
      return array
      (
         array
         (
            'CreateTableSQL', array ( plugin_table ( 'profile' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            profile_name    C(250)  DEFAULT '',
            profile_color   C(250)  DEFAULT '',
            profile_status  C(250)  DEFAULT '',
            profile_prio    I       DEFAULT 0
            " )
         ),
         array
         (
            'CreateTableSQL', array ( plugin_table ( 'eta' ), "
            id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            eta_config_value   C(250)  DEFAULT '',
            eta_user_value     C(250)  DEFAULT ''
            " )
         ),
         array
         (
            'CreateTableSQL', array ( plugin_table ( 'unit' ), "
            id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            eta_unit           C(250)  DEFAULT ''
            " )
         )
      );
   }

   function init ()
   {
      $tCorePath = config_get_global ( 'plugin_path' )
         . plugin_get_current ()
         . DIRECTORY_SEPARATOR
         . 'core'
         . DIRECTORY_SEPARATOR;

      require_once ( $tCorePath . 'roadmap_constant_api.php' );
   }

   function getUserHasLevel ()
   {
      $projectId = helper_get_current_project ();
      $userId = auth_get_current_user_id ();

      return user_get_access_level ( $userId, $projectId ) >= plugin_config_get ( 'access_level', ADMINISTRATOR );
   }

   function footer ()
   {
      if ( plugin_config_get ( 'show_footer' ) && $this->getUserHasLevel () )
      {
         return '<address>' . $this->name . '&nbsp;' . $this->version . '&nbsp;Copyright&nbsp;&copy;&nbsp;2016&nbsp;by&nbsp;' . $this->author . '</address>';
      }
      return null;
   }

   function menu ()
   {
      if ( ( !plugin_is_installed ( 'WhiteboardMenu' ) || !file_exists ( config_get_global ( 'plugin_path' ) . 'WhiteboardMenu' ) )
         && plugin_config_get ( 'show_menu' ) && $this->getUserHasLevel ()
      )
      {
         return '<a href="' . plugin_page ( 'roadmap_page' ) . '">' . plugin_lang_get ( 'menu_title' ) . '</a >';
      }
      return null;
   }
}
