RoadmapPro
==========

Plugin for MantisBT to manage roadmaps with additional features
+ create and manage different roadmap profiles
+ display different roadmaps as specified in a profile
+ group profiles for different progress calculation
+ additional informations
+ overview for simple navigation (requires javascript enabled)

Requirements
------------

+ MantisBT 1.2

Installation
------------

Copy the 'RoadmapPro' plugin in the plugins directory of Mantis. After installing a new menu entry "Roadmap Pro" is added.

If you want to disable the standard MantisBT roadmap, please add the following code into the html_api.php in  
  mantisRoot/core/html_api.php:  
 
  -> function print_menu -> go to # Roadmap Page  
 
  Add the following condition to the if-statement: && !plugin_is_installed ( 'RoadmapPro' )  

  The Code should look like

  # Roadmap Page
```
  if ( access_has_project_level( config_get( 'roadmap_view_threshold' ) ) 
       && !plugin_is_installed ( 'RoadmapPro' )  // new code snippet
     ) 
  {
     $t_menu_options[] = '' . helper_mantis_url( 'roadmap_page.php';
  }
```
  Then the standard roadmap menu field is disabled when the RoadmapPro-Plugin is installed. 

Available Translations
----------------------

- [x] english
- [x] german

You are welcome to translate this plugin, in a different language with Crowdin.

(https://crowdin.com/project/roadmap-pro)

Screenshots
-----------

Screenshot of the 'Roadmap Pro' overview with single progress.
![RoadmapPro Overview](/Images/roadmap_pro_spov.png?raw=true "")

Screenshot of the 'Roadmap Pro' detailed roadmap information with single progress.
![RoadmapPro Detailed View](/Images/roadmap_pro_spdv.png?raw=true "")

Screenshot of the 'Roadmap Pro' overview with overall progress.
![RoadmapPro Overview](/Images/roadmap_pro_opov.png?raw=true "")

Screenshot of the 'Roadmap Pro' detailed roadmap information with overall progress.
![RoadmapPro Detailed View](/Images/roadmap_pro_opdv.png?raw=true "")