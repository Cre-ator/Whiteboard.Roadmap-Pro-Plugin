<?php
require_once ( __DIR__ . '/../../vendor/autoload.php' );
require_once ( __DIR__ . '/../RoadmapPro/core/roadmap_pro_api.php' );

/**
 * Created by PhpStorm.
 * User: stefan.schwarz
 * Date: 05.08.2016
 * Time: 11:44
 */
class roadmap_pro_apiTest extends PHPUnit_Framework_TestCase
{
   public function testGenerateDbStatusValueString ()
   {
      # valid data
      $statusValues = [ 0 => 10, 1 => 30, 2 => 50, 3 => 80, 4 => 90 ];
      $desiredStatusValueString = '10;30;50;80;90';
      $actualStatusValuString = roadmap_pro_api::generateDbStatusValueString ( $statusValues );
      $this->assertEquals ( $desiredStatusValueString, $actualStatusValuString );

      # invalid data
      $statusValues = [ 0 => 10, 1 => 'abc', 2 => 50, 3 => 80, 4 => 90 ];
      $desiredStatusValueString = '10;50;80;90';
      $actualStatusValuString = roadmap_pro_api::generateDbStatusValueString ( $statusValues );
      $this->assertEquals ( $desiredStatusValueString, $actualStatusValuString );

      $statusValues = [ 0 => 10, 1 => 30, 2 => 50, 3 => 80, 4 => 'abc' ];
      $desiredStatusValueString = '10;30;50;80;';
      $actualStatusValuString = roadmap_pro_api::generateDbStatusValueString ( $statusValues );
      $this->assertEquals ( $desiredStatusValueString, $actualStatusValuString );
   }
}
