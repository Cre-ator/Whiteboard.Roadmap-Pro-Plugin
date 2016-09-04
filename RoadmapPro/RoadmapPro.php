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

      $this->version = '1.1.50';
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
      require_once ( __DIR__ . '/core/rConst.php' );
      require_once ( __DIR__ . '/core/rProApi.php' );
      rProApi::setDefault ();
   }

   function config ()
   {
      return array
      (
         'show_menu' => ON,
         'show_footer' => ON,
         'weekDayConfig' => '0;0;0;0;0;0;0'
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
            profile_prio    I       DEFAULT 0,
            profile_effort  I       DEFAULT 0
            " )
         ),
         array
         (
            'CreateTableSQL', array ( plugin_table ( 'profilegroup' ), "
            id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            group_name         C(250)  DEFAULT '',
            group_profiles     C(250)  DEFAULT ''
            " )
         ),
         array
         (
            'CreateTableSQL', array ( plugin_table ( 'eta' ), "
            id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            eta_config_value   C(250)  DEFAULT '',
            eta_user_value     C(250)  DEFAULT 0
            " )
         ),
         array
         (
            'CreateTableSQL', array ( plugin_table ( 'etathreshold' ), "
            id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            eta_thr_from       C(250)  DEFAULT '',
            eta_thr_to         C(250)  DEFAULT '',
            eta_thr_unit       C(250)  DEFAULT '',
            eta_thr_factor     C(250)  DEFAULT ''
            " )
         )
      );
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
      if ( ( !plugin_is_installed ( 'WhiteboardMenu' ) || !file_exists ( config_get_global ( 'plugin_path' ) . 'WhiteboardMenu' ) )
         && plugin_config_get ( 'show_menu' )
      )
      {
         return '<a href="' . plugin_page ( 'roadmap_page' ) . '">' . plugin_lang_get ( 'menu_title' ) . '</a >';
      }
      return null;
   }
}
