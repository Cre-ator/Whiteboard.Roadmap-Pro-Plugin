<?php
define ( 'PLUGINPATH', config_get_global ( 'plugin_path' ) . plugin_get_current () . DIRECTORY_SEPARATOR );
define ( 'COREPATH', PLUGINPATH . 'core' . DIRECTORY_SEPARATOR );

define ( 'HOURSPERDAY', 24 );
define ( 'WORKHOURSPERDAYDEFAULT', 8 );
define ( 'WEEKWORKTIMEDEFAULT', 40 );
define ( 'WORKDAYSPERWEEKDEFAULT', 5 );

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

define ( 'MONOSPACECHARWIDTH', 8 );
define ( 'BARINNERWIDTH', 398 );