# RoadmapPro

Plugin for MantisBT to manage raodmaps

Features

+ create and manage different roadmap profiles
+ display different roadmaps as specified in a profile
+ additional informations

Requirements

+ MantisBT 1.2.x | MantisBT 1.3.x

Installation

  Copy the 'RoadmapPro' plugin just in the plugins directory of Mantis. After installing a new menu entry "RoadmapPro" is added.

  If you want to disable the standard mantis roadmap, please add the following code into the html_api.php in
  mantisRoot/core/html_api.php:

  -> function print_menu -> go to # Roadmap Page

  Add the following condition to the if-statement: && !plugin_is_installed ( 'RoadmapPro' )

  Then the standard roadmap menu field is disabled when the RoadmapPro-Plugin is installed.
