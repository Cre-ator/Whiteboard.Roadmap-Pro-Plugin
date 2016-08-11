<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rHtmlApi.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/rProApi.php' );

/**
 * testclass for class rHtmlApi
 */
class rHtmlApiTest extends PHPUnit_Framework_TestCase
{
   /**
    * this test covers functionality of methods
    * - htmlPluginConfigOpenTable
    */
   public function testHtmlPluginConfigOpenTable ()
   {
      $this->expectOutputString ( '<table align="center" cellspacing="1" class="config-table">' );
      rHtmlApi::htmlPluginConfigOpenTable ();

      $this->expectOutputString (
         '<table align="center" cellspacing="1" class="config-table">' .
         '<table align="center" cellspacing="1" class="config-table" id="test">'
      );
      rHtmlApi::htmlPluginConfigOpenTable ( 'test' );
   }

   /**
    * this test covers functionality of methods
    * - printWrapperInHTML
    */
   public function testPrintWrapperInHTML ()
   {
      $this->expectOutputString ( '<div class="tr"><div class="td">content</div></div>' . PHP_EOL );
      rHtmlApi::printWrapperInHTML ( 'content' );
   }

   /**
    * this test covers functionality of methods
    * - htmlPluginBugCol
    */
   public function testHtmlPluginBugCol ()
   {
      $this->expectOutputString ( '<div class="td">' );
      rHtmlApi::htmlPluginBugCol ( false );

      $this->expectOutputString ( '<div class="td"><div class="td done">' );
      rHtmlApi::htmlPluginBugCol ( true );
   }

   /**
    * this test covers functionality of methods
    * - htmlPluginSpacer
    */
   public function testHtmlPluginSpacer ()
   {
      $this->expectOutputString ( '<div class="tr"><div class="td"><div class="spacer"></div></div></div>' );
      rHtmlApi::htmlPluginSpacer ();
   }

   /**
    * this test covers functionality of methods
    * - htmlPluginAddDirectoryProgressBar
    */
   public function testHtmlPluginAddDirectoryProgressBar ()
   {
      $this->expectOutputString ( '<script type="text/javascript">addProgressBarToDirectory (\'TestversionsId\',\'test\');</script>' );
      rHtmlApi::htmlPluginAddDirectoryProgressBar ( 'TestversionsId', 'test' );
   }
}
