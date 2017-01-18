<link rel="stylesheet" href="<?php echo plugin_file('roadmappro_config.css') ?>"/>
<script type="text/javascript" src="plugins/RoadmapPro/scripts/jscolor/jscolor.js"></script>
<script type="text/javascript" src="plugins/RoadmapPro/scripts/roadmappro.js"></script>


<?php
auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));
event_signal('EVENT_CORE_HEADERS');


if ('1.' == substr(MANTIS_VERSION, 0, 2)) {  // 1.2.x - 1.3.x
  html_page_top(plugin_lang_get('config_page_title'));
  print_manage_menu();

  require(__DIR__ . DIRECTORY_SEPARATOR . 'config.1.php');

  html_page_bottom();
} else {  // 2.x
  html_robots_noindex();
  layout_page_header(plugin_lang_get('config_page_title'));
  echo '<script type="text/javascript" src="plugins/RoadmapPro/scripts/roadmapprotwo.js"></script>';
  layout_page_header_end();


  layout_page_begin(__FILE__);
  print_manage_menu();

  require(__DIR__ . DIRECTORY_SEPARATOR . 'config.2.php');

  layout_page_end();
}
