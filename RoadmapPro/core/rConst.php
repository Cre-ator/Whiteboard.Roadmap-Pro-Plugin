<?php
define ( 'PLUGINPATH', config_get_global ( 'plugin_path' ) . plugin_get_current () . DIRECTORY_SEPARATOR );
define ( 'COREPATH', PLUGINPATH . 'core' . DIRECTORY_SEPARATOR );

define ( 'HOURSPERDAY', 24 );
define ( 'WORKHOURSPERDAY', 8 );
define ( 'WORKDAYSPERWEEK', 5 );

define ( 'DAYINSEC', 86400 );
define ( 'HOURINSEC', 3600 );