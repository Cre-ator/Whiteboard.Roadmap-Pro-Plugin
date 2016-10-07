<?php
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rProApi.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rProfileManager.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rProfile.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rGroupManager.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rGroup.php' );
require_once ( __DIR__ . DIRECTORY_SEPARATOR . 'rThreshold.php' );

/**
 * Class roadmap_html_api
 *
 * provides functions to print html content
 *
 * @author Stefan Schwarz
 */
class rHtmlApi
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
    */
   public static function htmlPluginConfigOutputCol ( $class, $lang_string, $colspan = 1 )
   {
      echo '<td class="' . $class . '" colspan="' . $colspan . '">' . plugin_lang_get ( $lang_string ) . '</td>';
   }

   /**
    * prints opening table tag
    *
    * @param null $id
    */
   public static function htmlPluginConfigOpenTable ( $id = NULL )
   {
      $htmlString = '<table align="center" cellspacing="1" class="width75';
      if ( $id != NULL )
      {
         $htmlString .= ' top" id="' . $id . '">';
      }
      else
      {
         $htmlString .= '">';
      }
      echo $htmlString;
   }

   /**
    * print progress bar for single roadmap
    *
    * @param $progress
    * @param $progressString
    * @param $versionId
    * @param $projectId
    */
   private static function printSingleProgressbar ( $progress, $progressString, $versionId, $projectId )
   {
      $versionDesiredDate = version_get_field ( $versionId, 'date_order' );
      $versionReleaseDate = string_display_line ( date ( config_get ( 'short_date_format' ), $versionDesiredDate ) );
      $versionReleaseString = plugin_lang_get ( 'roadmap_page_release_date_planned' ) . ':&nbsp;' . $versionReleaseDate;
      $progressHtmlString = '<span class="bar single" style="width: ' . $progress . '%; white-space: nowrap;"><div>' . $progressString . '</div></span>';

      echo '<div class="progress9001">' . $progressHtmlString . '</div>';
      echo '<div class="td h25">&nbsp;' . plugin_lang_get ( 'roadmap_page_release_date_planned' ) . ':&nbsp;' . $versionReleaseDate . '</div>';

      echo '<script type="text/javascript">';
      echo 'addProgressBarToDirectory (\'' . $versionId . '\',\'' . $projectId . '\',\'' . $progressHtmlString . '\',\'' . $versionReleaseString . '\',\'' . '' . '\');';
      echo '</script>';
   }

   /**
    * print progress bar for a progress group
    *
    * @param roadmap $roadmap
    */
   private static function printScaledProgressbar ( roadmap $roadmap )
   {
      $useEta = $roadmap->getEtaIsSet ();
      $profileHashMap = $roadmap->getProfileHashArray ();
      $fullEta = $roadmap->getFullEta ();
      $doneEta = 0;
      $sumPercentDone = 0;
      $sumProgressHtmlString = '';

      $maxThresholdUnit = rProApi::calculateEtaUnit ( $fullEta );
      /** @var rThreshold $maxThreshold */
      $maxThreshold = $maxThresholdUnit[ 2 ];

      echo '<div class="td"><div class="progress9001">';
      if ( !empty( $profileHashMap ) )
      {
         $profileHashCount = count ( $profileHashMap );
         for ( $index = 0; $index < $profileHashCount; $index++ )
         {
            # extract profile data
            $profileHash = explode ( ';', $profileHashMap[ $index ] );
            $hashProfileId = $profileHash[ 0 ];
            $hashProgress = $profileHash[ 1 ];
            $progressHtmlString = '';
            $tempEta = 0;
            if ( $hashProgress > 0 )
            {
               # get profile color
               $profile = new rProfile( $hashProfileId );
               $profileColor = '#' . $profile->getProfileColor ();
               $tempEta = round ( ( ( $hashProgress / 100 ) * $fullEta ), 1 );
               $direction = 'middle';

               if ( $index == 0 ) # first bar
               {
                  $direction = 'left';
               }

               if ( $index == ( $profileHashCount - 1 ) ) # last bar
               {
                  if ( ( $sumPercentDone + $hashProgress ) >= 99 )
                  {
                     $hashProgress = ( 100 - $sumPercentDone );
                  }
                  $direction = 'right';
               }

               $nextHashProgress = 0;
               if ( $index < ( $profileHashCount - 1 ) )
               {
                  $nextProfileHash = explode ( ';', $profileHashMap[ $index + 1 ] );
                  $nextHashProgress = $nextProfileHash[ 1 ];
               }

               $roadmapProgress = rProApi::getRoadmapProgress ( $useEta, $tempEta, $profileHash, $maxThreshold );
               $barWidth = ( ( ( $hashProgress / 100 ) * BARINNERWIDTH ) - 2 );
               $textWidth = strlen ( $roadmapProgress ) * CHARWIDTH;
               $progressHtmlString .= '<div class="bar ' . $direction . '" style="width: ' . $hashProgress . '%; background: ' . $profileColor . ';">';
               if ( ( $textWidth <= $barWidth ) || ( ( $textWidth > $barWidth ) && ( $nextHashProgress == 0 ) ) )
               {
                  $progressHtmlString .= '<div>' . $roadmapProgress . '</div>';
               }
               $progressHtmlString .= '</div>';
               echo $progressHtmlString;

               $sumProgressHtmlString .= $progressHtmlString;
            }
            echo '<!---->';
            $sumPercentDone += $hashProgress;
            $doneEta += $tempEta;

         }

         if ( strlen ( $sumProgressHtmlString ) == 0 )
         {
            $sumProgressHtmlString .= '<div style="text-align: left">0%</div>';
            echo $sumProgressHtmlString;
         }

         $roadmap->setDoneEta ( $doneEta );
      }
      echo '</div></div>';

      self::printScaledDetailedTextProgress ( $roadmap, $sumProgressHtmlString, $maxThreshold );
   }

   /**
    * print progress for scaled roadmap as text
    *
    * @param roadmap $roadmap
    * @param $sumProgressHtmlString
    * @param rThreshold $maxThreshold
    */
   private static function printScaledDetailedTextProgress ( roadmap $roadmap, $sumProgressHtmlString, rThreshold $maxThreshold = NULL )
   {
      $versionId = $roadmap->getVersionId ();
      $textProgressDir = $roadmap->getTextProgressDir ();
      $textProgressMain = $roadmap->getTextProgressMain ( $maxThreshold );
      $textUncertainty = $roadmap->getUncertaintyString ();
      $expectedFinishedDateString = $roadmap->getExpectedFinishedDateString () . '*';
      $versionDesiredDate = version_get_field ( $versionId, 'date_order' );
      $actualDesiredFinishedDateDeviation = $roadmap->getActualDesiredDeviation ( $versionDesiredDate );
      $versionReleaseDate = string_display_line ( date ( config_get ( 'short_date_format' ), $versionDesiredDate ) );
      $versionReleaseString = plugin_lang_get ( 'roadmap_page_release_date_planned' ) . ':&nbsp;' . $versionReleaseDate . $expectedFinishedDateString;

      echo '<div class="td h25">' . $textProgressMain . '&nbsp;</div>';
      echo '<div class="td h25">&nbsp;' . $versionReleaseString . '</div>';

      if ( strlen ( $actualDesiredFinishedDateDeviation ) > 0 )
      {
         $actualDesiredFinishedDateDeviationString = ',&nbsp;' . plugin_lang_get ( 'roadmap_page_delay' ) .
            ':&nbsp;' . $actualDesiredFinishedDateDeviation . $textUncertainty;
         echo '<div class="td h25">' . $actualDesiredFinishedDateDeviationString . '</div>';
         $versionReleaseString .= $actualDesiredFinishedDateDeviationString;
      }


      echo '<script type="text/javascript">';
      echo 'addProgressBarToDirectory (\'' . $versionId . '\',\'' . $roadmap->getProjectId () . '\',\'' . $sumProgressHtmlString . '\',\'' . $textProgressDir . '\',\'' . $versionReleaseString . '\');';
      echo '</script>';
   }

   /**
    * print progress as text
    *
    * @param roadmap $roadmap
    */
   public static function printSingleTextProgress ( roadmap $roadmap )
   {
      $overallBugAmount = count ( $roadmap->getBugIds () );
      $doneBugAmount = count ( $roadmap->getDoneBugIds () );
      $progressPercent = $roadmap->getSingleProgressPercent ();
      $useEta = $roadmap->getEtaIsSet ();
      echo '<div class="tr"><div class="td">' . PHP_EOL;
      if ( $useEta && config_get ( 'enable_eta' ) )
      {
         echo sprintf ( plugin_lang_get ( 'roadmap_page_resolved_time' ), $doneBugAmount, $overallBugAmount );
      }
      else
      {
         echo sprintf ( lang_get ( 'resolved_progress' ), $doneBugAmount, $overallBugAmount, round ( $progressPercent ) );
      }
      echo '</div></div>' . PHP_EOL;
   }

   /**
    * print progress as text
    *
    * @param roadmap $roadmap
    */
   public static function printScaledTextProgress ( roadmap $roadmap )
   {
      $bugIds = $roadmap->getBugIds ();
      $overallBugAmount = count ( $bugIds );
      $doneBugIds = rProApi::getDoneIssueIdsForAllProfiles ( $bugIds, $roadmap->getGroupId () );
      $doneBugAmount = count ( $doneBugIds );
      echo '<div class="tr"><div class="td">' . PHP_EOL;
      echo sprintf ( plugin_lang_get ( 'roadmap_page_resolved_time' ), $doneBugAmount, $overallBugAmount );
      echo '</div></div>' . PHP_EOL;
   }

   /**
    * wraps content into a cell
    *
    * @param $content
    */
   public static function printWrapperInHTML ( $content )
   {
      echo '<div class="tr"><div class="td">' . $content . '</div></div>' . PHP_EOL;
   }

   /**
    * print the initial html content for the directory
    */
   public static function htmlPluginDirectory ()
   {
      $getGroupId = NULL;
      $getProfileId = NULL;
      $getProjectId = NULL;
      $getVersionId = NULL;
      $getSort = NULL;
      if ( isset( $_GET[ 'group_id' ] ) )
      {
         $getGroupId = $_GET[ 'group_id' ];
      }
      if ( isset( $_GET[ 'profile_id' ] ) )
      {
         $getProfileId = $_GET[ 'profile_id' ];
      }
      if ( isset( $_GET[ 'project_id' ] ) )
      {
         $getProjectId = $_GET[ 'project_id' ];
      }
      if ( isset( $_GET[ 'version_id' ] ) )
      {
         $getVersionId = $_GET[ 'version_id' ];
      }
      if ( isset( $_GET[ 'sort' ] ) )
      {
         $getSort = $_GET[ 'sort' ];
      }

      $ahref = '<a class="button" href="' . plugin_page ( 'roadmap_page' );
      $vpbutton = '<input type="button" value="' . plugin_lang_get ( 'roadmap_page_sortpv' ) . '">';
      $pvbutton = '<input type="button" value="' . plugin_lang_get ( 'roadmap_page_sortvp' ) . '">';
      echo '<script type="text/javascript">';
      echo 'addRoadmapDirectoryBox (\'' . plugin_lang_get ( 'roadmap_page_directory' ) . '\',\'' . $vpbutton . '\',\'' . $pvbutton . '\',\'' . $ahref . '\',\'' . $getGroupId . '\',\'' . $getProfileId . '\',\'' . $getProjectId . '\',\'' . $getVersionId . '\',\'' . $getSort . '\');';
      echo '</script>';

      echo '<div class="spacer"></div>';
   }

   /**
    * print the html content title
    */
   public static function htmlPluginContentTitle ()
   {
      $getGroupId = NULL;
      $getProfileId = NULL;
      $getProjectId = NULL;
      $getVersionId = NULL;
      $getSort = NULL;
      if ( isset( $_GET[ 'group_id' ] ) )
      {
         $getGroupId = $_GET[ 'group_id' ];
      }
      if ( isset( $_GET[ 'profile_id' ] ) )
      {
         $getProfileId = $_GET[ 'profile_id' ];
      }
      if ( isset( $_GET[ 'project_id' ] ) )
      {
         $getProjectId = $_GET[ 'project_id' ];
      }
      if ( isset( $_GET[ 'version_id' ] ) )
      {
         $getVersionId = $_GET[ 'version_id' ];
      }
      if ( isset( $_GET[ 'sort' ] ) )
      {
         $getSort = $_GET[ 'sort' ];
      }

      echo '<div class="tr">';
      # page title
      echo '<span class="pagetitle">' . plugin_lang_get ( 'roadmap_page_content_title' ) . '</span>';
      # sort button
      echo '<noscript>';
      echo '<div class="right">';
      echo '<a class="button" href="' . plugin_page ( 'roadmap_page' );
      if ( isset( $_GET[ 'group_id' ] ) )
      {
         echo '&amp;group_id=' . $getGroupId;
      }
      if ( isset( $_GET[ 'profile_id' ] ) )
      {
         echo '&amp;profile_id=' . $getProfileId;
      }
      if ( isset( $_GET[ 'project_id' ] ) )
      {
         echo '&amp;project_id=' . $getProjectId;
      }
      if ( isset( $_GET[ 'version_id' ] ) )
      {
         echo '&amp;version_id=' . $getVersionId;
      }
      if ( $getSort == 'vp' )
      {
         echo '&amp;sort=pv">';
         echo '<input type="button" value="' . plugin_lang_get ( 'roadmap_page_sortpv' ) . '" />';
      }
      if ( $getSort == 'pv' )
      {
         echo '&amp;sort=vp">';
         echo '<input type="button" value="' . plugin_lang_get ( 'roadmap_page_sortvp' ) . '" />';
      }
      if ( $getSort != 'vp' && $getSort != 'pv' )
      {
         echo '">';
      }
      echo '</a>';
      echo '</div>';
      echo '</noscript>';
      echo '</div>';
      echo '<div class="tr"><hr /></div>';
   }

   /**
    * print the plugin profile switcher
    */
   public static function printProfileSwitcher ()
   {
      $groupIds = rGroupManager::getRGroupIds ();
      $groupCount = count ( $groupIds );

      $profileIds = rProfileManager::getRProfileIds ();
      $profileCount = count ( $profileIds );

      echo '<div class="table_center"><div class="tr">' . PHP_EOL;
      # groups are available
      if ( $groupCount > 0 )
      {
         foreach ( $groupIds as $groupId )
         {
            $group = new rGroup( $groupId );
            $groupName = $group->getGroupName ();

            echo '<div class="td">';
            self::htmlLinkGroupSwitcher ( $groupName, $groupId );
            echo '</div>' . PHP_EOL;
         }
      }
      # no groups available
      else
      {
         # print roadmap_profile-links
         if ( $profileCount > 0 )
         {
            foreach ( $profileIds as $profileId )
            {
               $profile = new rProfile( $profileId );
               $profileName = $profile->getProfileName ();

               echo '<div class="td">';
               self::htmlLinkProfileSwitcher ( string_display ( $profileName ), $profileId );
               echo '</div>' . PHP_EOL;
            }
         }
         # show whole progress, when there is more then one different profile
         if ( $profileCount > 1 )
         {
            echo '<div class="td">';
            self::htmlLinkProfileSwitcher ( plugin_lang_get ( 'roadmap_page_whole_progress' ) );
            echo '</div>' . PHP_EOL;
         }
      }
      echo '</div></div>' . PHP_EOL;
   }

   /**
    * print profile switcher for a group
    *
    * @param $groupId
    */
   public static function htmlGroupProfileSwitcher ( $groupId )
   {
      $group = new rGroup( $groupId );
      $groupProfileIds = explode ( ';', $group->getGroupProfiles () );
      $groupProfileIdCount = count ( $groupProfileIds );

      echo '<div class="table_center"><div class="tr">' . PHP_EOL;
      if ( $groupProfileIdCount > 0 )
      {
         foreach ( $groupProfileIds as $groupProfileId )
         {
            $profile = new rProfile( $groupProfileId );
            $profileName = $profile->getProfileName ();
            echo '<div class="td">';
            self::htmlLinkGroupProfileSwitcher ( string_display ( $profileName ), $groupId, $groupProfileId );
            echo '</div>' . PHP_EOL;
         }
      }
      # show whole progress, when there is more then one different profile
      if ( $groupProfileIdCount > 1 )
      {
         echo '<div class="td">';
         self::htmlLinkGroupProfileSwitcher ( plugin_lang_get ( 'roadmap_page_whole_progress' ), $groupId );
         echo '</div>' . PHP_EOL;
      }
      echo '</div></div>' . PHP_EOL;
   }

   /**
    * print the links for a group
    *
    * @param $groupName
    * @param $groupId
    */
   private static function htmlLinkGroupSwitcher ( $groupName, $groupId )
   {
      $getVersionId = NULL;
      $getProjectId = NULL;
      $getSort = NULL;
      if ( isset( $_GET[ 'version_id' ] ) )
      {
         $getVersionId = $_GET[ 'version_id' ];
      }
      if ( isset( $_GET[ 'project_id' ] ) )
      {
         $getProjectId = $_GET[ 'project_id' ];
      }
      if ( isset( $_GET[ 'sort' ] ) )
      {
         $getSort = $_GET[ 'sort' ];
      }

      $currentProjectId = helper_get_current_project ();

      echo '[ <a href="' . plugin_page ( 'roadmap_page' ) . '&amp;group_id=';
      # check specific profile id is given
      if ( $groupId != NULL )
      {
         echo $groupId;
      }
      # check version id is get parameter
      if ( $getVersionId != NULL )
      {
         echo '&amp;version_id=' . $getVersionId;
      }
      # check project id is get parameter
      if ( $getProjectId != NULL )
      {
         echo '&amp;project_id=' . $getProjectId;
      }
      echo '&amp;sproject_id=' . $currentProjectId;
      if ( $getSort != NULL )
      {
         echo '&amp;sort=' . $getSort;
      }
      else
      {
         echo '&amp;sort=pv';
      }
      echo '">';
      echo $groupName;
      echo '</a> ]';
   }

   /**
    * print the link for a profile
    *
    * @param $linkDescription
    * @param null $profileId
    */
   private static function htmlLinkProfileSwitcher ( $linkDescription, $profileId = NULL )
   {
      $getVersionId = NULL;
      $getProjectId = NULL;
      $getSort = NULL;
      if ( isset( $_GET[ 'version_id' ] ) )
      {
         $getVersionId = $_GET[ 'version_id' ];
      }
      if ( isset( $_GET[ 'project_id' ] ) )
      {
         $getProjectId = $_GET[ 'project_id' ];
      }
      if ( isset( $_GET[ 'sort' ] ) )
      {
         $getSort = $_GET[ 'sort' ];
      }

      $currentProjectId = helper_get_current_project ();

      echo '[ <a href="' . plugin_page ( 'roadmap_page' ) . '&amp;profile_id=';
      # check specific profile id is given
      if ( $profileId != NULL )
      {
         echo $profileId;
      }
      else
      {
         echo '-1';
      }
      # check version id is get parameter
      if ( $getVersionId != NULL )
      {
         echo '&amp;version_id=' . $getVersionId;
      }
      # check project id is get parameter
      if ( $getProjectId != NULL )
      {
         echo '&amp;project_id=' . $getProjectId;
      }
      echo '&amp;sproject_id=' . $currentProjectId;
      if ( $getSort != NULL )
      {
         echo '&amp;sort=' . $getSort;
      }
      else
      {
         echo '&amp;sort=pv';
      }
      echo '">';
      echo $linkDescription;
      echo '</a> ]';
   }

   /**
    * print profile link in a group
    *
    * @param $linkDescription
    * @param $groupId
    * @param null $profileId
    */
   private static function htmlLinkGroupProfileSwitcher ( $linkDescription, $groupId, $profileId = NULL )
   {
      $getProjectId = NULL;
      $getVersionId = NULL;
      if ( isset( $_GET[ 'project_id' ] ) )
      {
         $getProjectId = $_GET[ 'project_id' ];
      }
      if ( isset( $_GET[ 'version_id' ] ) )
      {
         $getVersionId = $_GET[ 'version_id' ];
      }

      $currentProjectId = helper_get_current_project ();

      echo '[ <a href="' . plugin_page ( 'roadmap_page' ) . '&amp;group_id=' . $groupId . '&amp;profile_id=';
      # check specific profile id is given
      if ( $profileId != NULL )
      {
         echo $profileId;
      }
      else
      {
         echo '-1';
      }
      # check version id is get parameter
      if ( $getVersionId != NULL )
      {
         echo '&amp;version_id=' . $getVersionId;
      }
      # check project id is get parameter
      if ( $getProjectId != NULL )
      {
         echo '&amp;project_id=' . $getProjectId;
      }
      echo '&amp;sproject_id=' . $currentProjectId;
      echo '">';
      echo $linkDescription;
      echo '</a> ]';
   }

   /**
    * print the progress of a roadmap
    *
    * @param roadmap $roadmap
    */
   public static function printVersionProgress ( roadmap $roadmap )
   {
      echo '<div class="tr">';
      $profileId = $roadmap->getProfileId ();
      if ( $profileId == -1 )
      {
         self::printScaledProgressbar ( $roadmap );
      }
      else
      {
         echo '<div class="td">';
         $useEta = $roadmap->getEtaIsSet ();
         $doneEta = $roadmap->getDoneEta ();
         $fullEta = $roadmap->getFullEta ();
         $versionId = $roadmap->getVersionId ();
         $progressPercent = round ( $roadmap->getSingleProgressPercent () );
         if ( $useEta && config_get ( 'enable_eta' ) )
         {
            $profileEffortFactor = rProApi::getProfileEffortFactor ( $roadmap );

            $calculatedFullEta = rProApi::calculateEtaUnit ( $fullEta );
            /** @var rThreshold $usedThreshold */
            $usedThreshold = $calculatedFullEta[ 2 ];
            if ( $usedThreshold != NULL )
            {
               $calculatedDoneEta = array ();
               $factor = $usedThreshold->getThresholdFactor ();
               $calculatedDoneEta[ 0 ] = $doneEta / $factor;
               $calculatedDoneEta[ 1 ] = $usedThreshold->getThresholdUnit ();
            }
            else
            {
               $calculatedDoneEta = rProApi::calculateEtaUnit ( $doneEta );
            }
            setlocale ( LC_NUMERIC, lang_get_current () );
            $progressString = round ( ( $calculatedDoneEta[ 0 ] * $profileEffortFactor ), 1 ) . $calculatedDoneEta[ 1 ] .
               '&nbsp;' . plugin_lang_get ( 'roadmap_page_bar_from' ) . '&nbsp;' . round ( $calculatedFullEta[ 0 ] * $profileEffortFactor, 1 ) . $calculatedFullEta[ 1 ];
            self::printSingleProgressbar ( $progressPercent, $progressString, $versionId, $roadmap->getProjectId () );
         }
         else
         {
            $bugIds = $roadmap->getBugIds ();
            $bugCount = count ( $bugIds );
            $progressString = round ( $progressPercent ) . '%&nbsp;' . plugin_lang_get ( 'roadmap_page_bar_from' ) . '&nbsp;' . $bugCount . '&nbsp;' . lang_get ( 'issues' );
            self::printSingleProgressbar ( $progressPercent, $progressString, $versionId, $roadmap->getProjectId () );
         }
         echo '</div>';
      }
      echo '</div>' . PHP_EOL;
   }

   /**
    * print the bug list of a roadmap
    *
    * @param $bugIds
    * @param bool $doneBugs
    */
   public static function printBugList ( $bugIds, $doneBugs = FALSE )
   {
      foreach ( $bugIds as $bugId )
      {
         /** @var BugData $bug */
         $bug = bug_get ( $bugId );
         $userId = $bug->handler_id;
         echo '<div class="tr">';
         # line through, if bug is done
         self::htmlPluginBugCol ( $doneBugs );
         # bug id
         echo string_get_bug_view_link ( $bugId ) . '&nbsp;';
         # bug symbols
         rProApi::calcBugSmybols ( $bugId );
         # bug category
         self::htmlPluginPrintCategory ( $bug->category_id );
         # bug summary
         echo string_display_line ( $bug->summary );
         # bug assigned user
         if ( $userId > 0 )
         {
            echo '&nbsp;(<a href="' . config_get ( 'path' ) . '/view_user_page.php?id=' . $userId . '">' .
               user_get_name ( $userId ) . '</a>' . ')';
         }
         # bug status
         echo '&nbsp;-&nbsp;' . string_display_line ( get_enum_element ( 'status', $bug->status ) ) . '.';
         echo '</div></div>' . PHP_EOL;
      }
   }

   /**
    * print category for bug entry in the roadmap
    *
    * @param $categoryId
    */
   private static function htmlPluginPrintCategory ( $categoryId )
   {
      if ( $categoryId == 0 )
      {
         echo '[<b>' . plugin_lang_get ( 'roadmap_page_no_cat' ) . '</b>]&nbsp;';
      }
      else
      {
         echo '[<b>' . category_get_field ( $categoryId, 'name' ) . '</b>]&nbsp;';
      }
   }

   /**
    * print opening cell element for a bug
    *
    * @param $bugIsDone
    */
   public static function htmlPluginBugCol ( $bugIsDone )
   {
      if ( $bugIsDone )
      {
         echo '<div class="td done">';
      }
      else
      {
         echo '<div class="td">';
      }
   }

   /**
    * print the project title in a roadmap
    *
    * @param $profileId
    * @param $projectId
    */
   public static function htmlPluginProjectTitle ( $profileId, $projectId )
   {
      $profile = new rProfile( $profileId );
      $profileName = string_display ( $profile->getProfileName () );
      $projectName = string_display ( project_get_name ( $projectId ) );

      echo '<div class="tr"><div class="td"><span class="pagetitle" id="p' . $projectId . '">';
      if ( $profileId == -1 )
      {
         echo sprintf ( plugin_lang_get ( 'roadmap_page_version_title' ), $projectName, plugin_lang_get ( 'roadmap_page_whole_progress' ) );
      }
      else
      {
         echo sprintf ( plugin_lang_get ( 'roadmap_page_version_title' ), $projectName, $profileName );
      }
      echo '</span></div></div>';
   }

   /**
    * print the project title in a roadmap
    *
    * @param $version
    */
   public static function htmlPluginVersionTitle ( $version, $profileId )
   {
      $versionid = string_display ( $version[ 'id' ] );
      $versionName = string_display ( $version[ 'version' ] );
      $profile = new rProfile( $profileId );
      $profileName = string_display ( $profile->getProfileName () );

      echo '<div class="tr"><div class="td"><span class="pagetitle" id="p' . $versionid . '">';
      if ( $profileId == -1 )
      {
         echo sprintf ( plugin_lang_get ( 'roadmap_page_version_title' ), $versionName, plugin_lang_get ( 'roadmap_page_whole_progress' ) );
      }
      else
      {
         echo sprintf ( plugin_lang_get ( 'roadmap_page_version_title' ), $versionName, $profileName );
      }
      echo '</span></div></div>';
   }

   /**
    * print spacer element
    */
   public static function htmlPluginSpacer ()
   {
      echo '<div class="tr"><div class="td"><div class="spacer"></div></div></div>';
   }

   /**
    * triggers whiteboard menu if installed
    */
   public static function htmlPluginTriggerWhiteboardMenu ()
   {
      if ( plugin_is_installed ( 'WhiteboardMenu' ) &&
         file_exists ( config_get_global ( 'plugin_path' ) . 'WhiteboardMenu' )
      )
      {
         require_once ( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
            'WhiteboardMenu' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'wmApi.php' );
         echo '<link rel="stylesheet" href="plugins/WhiteboardMenu/files/whiteboardmenu.css"/>';
         wmApi::printWhiteboardMenu ();
      }
   }

   /**
    * add version entry to directory
    *
    * @param $projectId
    * @param $versionId
    * @param $versionName
    */
   public static function htmlPluginAddDirectorySubVersionEntry ( $projectId, $versionId, $versionName )
   {
      echo '<script type="text/javascript">';
      echo 'addVersionEntryToDirectory (\'' . project_get_name ( $projectId ) . '\',\'' . $projectId . '\',\'' . $versionId . '\',\'' . $versionName . '\');';
      echo '</script>';
   }

   /**
    * add version entry to directory
    *
    * @param $version
    * @param $projectId
    * @param $projectName
    */
   public static function htmlPluginAddDirectorySubProjectEntry ( $version, $projectId, $projectName )
   {
      echo '<script type="text/javascript">';
      echo 'addVersionEntryToDirectory (\'' . string_display_line ( $version[ 'version' ] ) . '\',\'' . $projectId . '\',\'' . $version[ 'id' ] . '\',\'' . $projectName . '\');';
      echo '</script>';
   }

   /**
    * add project entry to directory
    *
    * @param $projectId
    */
   public static function htmlPluginAddDirectoryProjectEntry ( $projectId )
   {
      $projectName = project_get_name ( $projectId );
      echo '<script type="text/javascript">';
      echo 'addProjectEntryToDirectory (\'directory\',\'' . $projectId . '\',\'' . $projectName . '\');';
      echo '</script>';
   }

   /**
    * add project entry to directory
    *
    * @param $version
    */
   public static function htmlPluginAddDirectoryVersionEntry ( $version )
   {
      $versionName = string_display_line ( $version[ 'version' ] );
      echo '<script type="text/javascript">';
      echo 'addProjectEntryToDirectory (\'directory\',\'' . $version[ 'id' ] . '\',\'' . $versionName . '\');';
      echo '</script>';
   }

   /**
    * prints initial ressources for the page
    *
    * @param $profileColor
    */
   public static function htmlInitializeRessources ( $profileColor )
   {
      echo '<link rel="stylesheet" href="plugins/RoadmapPro/files/roadmappro.css.php?profile_color=' . $profileColor . '"/>';
      echo '<script type="text/javascript" src="plugins/RoadmapPro/files/roadmappro.js"></script>';
      echo '<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>';
      echo '<script type="text/javascript">backToTop();</script>';
   }

   /**
    * print html info text
    */
   public static function htmlInfoFooter ()
   {
      echo '<div class="tr"><div class="td" style="font-size: smaller">*' . plugin_lang_get ( 'roadmap_page_dateinfo' ) . '</div></div>';
   }
}
