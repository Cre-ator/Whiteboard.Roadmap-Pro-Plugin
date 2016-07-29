<?php
require_once ( __DIR__ . '/roadmap_db.php' );

class roadmap_html_api
{

   /**
    * Prints a radio button element
    *
    * @param $colspan
    * @param $name
    */
   public static function htmlPluginConfigRadio ( $name, $colspan = 1 )
   {
      echo '<td width="100px" colspan="' . $colspan . '">';
      echo '<label>';
      echo '<input type="radio" name="' . $name . '" value="1"';
      echo ( ON == plugin_config_get ( $name ) ) ? 'checked="checked"' : '';
      echo '/>' . lang_get ( 'yes' );
      echo '</label>';
      echo '<label>';
      echo '<input type="radio" name="' . $name . '" value="0"';
      echo ( OFF == plugin_config_get ( $name ) ) ? 'checked="checked"' : '';
      echo '/>' . lang_get ( 'no' );
      echo '</label>';
      echo '</td>';
   }

   /**
    * Prints a category column in the plugin config area
    *
    * @param $class
    * @param $colspan
    * @param $lang_string
    * @return string
    */
   public static function htmlPluginConfigOutputCol ( $class, $lang_string, $colspan = 1 )
   {
      echo '<td class="' . $class . '" colspan="' . $colspan . '">' . plugin_lang_get ( $lang_string ) . '</td>';
   }

   /**
    * prints opening table tag
    *
    * @param $class
    * @param null $id
    */
   public static function htmlPluginConfigOpenTable ( $class, $id = null )
   {
      $htmlString = '<table align="center" cellspacing="1" class="' . $class . '"';
      if ( is_null ( $id ) == false )
      {
         $htmlString .= ' id="' . $id . '"';
      }
      $htmlString .= '>';
      echo $htmlString;
   }

   /**
    * prints closing table tag
    */
   public static function htmlPluginConfigCloseTable ()
   {
      echo '</table>';
   }
}
