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

Screenshot of the 'Roadmap Pro' with single progress.
![RoadmapPro](/Images/roadmap_pro_sp.png?raw=true "")

Screenshot of the 'Roadmap Pro' with overall progress.
![RoadmapPro](/Images/roadmap_pro_op.png?raw=true "")

Screenshot of the 'Roadmap Pro' config dialog.
![RoadmapPro Config](/Images/roadmap_pro_conf.png?raw=true "")

Screenshot of the 'Roadmap Pro' ETA config dialog.
![RoadmapPro Config Eta](/Images/roadmap_pro_conf_eta.png?raw=true "")

Libraries
---------

- Jan Odvarko - jscolor, JavaScript Color Picker (v1.4.5)
  GNU Lesser General Public License, http://www.gnu.org/copyleft/lesser.html
  http://jscolor.com