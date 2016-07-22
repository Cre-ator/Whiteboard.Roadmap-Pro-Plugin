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

  The Code should look like

  # Roadmap Page
  if ( access_has_project_level( config_get( 'roadmap_view_threshold' ) ) 
       && !plugin_is_installed ( 'RoadmapPro' )  // new code snippet
     ) 
  {
     $t_menu_options[] = '' . helper_mantis_url( 'roadmap_page.php';
  }
 
  Then the standard roadmap menu field is disabled when the RoadmapPro-Plugin is installed. 