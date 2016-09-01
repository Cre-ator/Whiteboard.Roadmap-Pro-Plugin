<?php
define ( 'PLUGINPATH', config_get_global ( 'plugin_path' ) . plugin_get_current () . DIRECTORY_SEPARATOR );
define ( 'COREPATH', PLUGINPATH . 'core' . DIRECTORY_SEPARATOR );

define ( 'HOURSPERDAY', 24 );
define ( 'WORKHOURSPERDAY', 8 );

define ( 'DAYINSEC', 86400 );
define ( 'HOURINSEC', 3600 );

define ( 'LOSSFACTOR', 1.3 );
define ( 'DAYSPERWEEK', 7 );
define ( 'MON', 0 );
define ( 'TUE', 1 );
define ( 'WED', 2 );
define ( 'THU', 3 );
define ( 'FRI', 4 );
define ( 'SAT', 5 );
define ( 'SUN', 6 );