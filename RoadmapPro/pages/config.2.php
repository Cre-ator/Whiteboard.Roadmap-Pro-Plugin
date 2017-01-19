<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rHtmlApi.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProApi.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProfileManager.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rProfile.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rGroupManager.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rGroup.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rThresholdManager.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rThreshold.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rEta.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'rWeekDayManager.php');

?>
<div class="col-md-12 col-xs-12">
  <div class="space-10"></div>
  <form action="<?php echo plugin_page('config_update') ?>" method="post">
    <!-- General Settings -->
    <div class="form-container">
      <div class="widget-box widget-color-blue2">
        <!-- Title -->
        <div class="widget-header widget-header-small">
          <h4 class="widget-title lighter">
            <i class="ace-icon fa fa-text-width"></i>
            <?php echo plugin_lang_get('menu_title') . ':&nbsp;' . plugin_lang_get('config_page_general'); ?>
          </h4>
        </div>
        <!-- End::Title -->
        <!-- Show Menu -->
        <div class="widget-body">
          <div class="widget-main no-padding">
            <div class="table-responsive">
              <table class="table table-bordered table-condensed table-striped">
                <td class="category width-40" width="30%">
                  <?php echo plugin_lang_get('config_page_show_menu'); ?>
                </td>
                <td class="center" width="20%">
                  <label>
                    <input type="radio" class="ace" name="show_menu"
                           value="1" <?php echo (ON == plugin_config_get('show_menu')) ? 'checked="checked" ' : '' ?>/>
                    <span class="lbl"> <?php echo lang_get('yes') ?> </span>
                  </label>
                </td>
                <td class="center" width="20%">
                  <label>
                    <input type="radio" class="ace" name="show_menu"
                           value="0" <?php echo (OFF == plugin_config_get('show_menu')) ? 'checked="checked" ' : '' ?>/>
                    <span class="lbl"> <?php echo lang_get('no') ?> </span>
                  </label>
                </td>
              </table>
            </div>
          </div>
        </div>
        <!-- End::Show Menu -->
        <!-- Show Footer -->
        <div class="widget-body">
          <div class="widget-main no-padding">
            <div class="table-responsive">
              <table class="table table-bordered table-condensed table-striped">
                <td class="category width-40" width="30%">
                  <?php echo plugin_lang_get('config_page_show_footer'); ?>
                </td>
                <td class="center" width="20%">
                  <label>
                    <input type="radio" class="ace" name="show_footer"
                           value="1" <?php echo (ON == plugin_config_get('show_footer')) ? 'checked="checked" ' : '' ?>/>
                    <span class="lbl"> <?php echo lang_get('yes') ?> </span>
                  </label>
                </td>
                <td class="center" width="20%">
                  <label>
                    <input type="radio" class="ace" name="show_footer"
                           value="0" <?php echo (OFF == plugin_config_get('show_footer')) ? 'checked="checked" ' : '' ?>/>
                    <span class="lbl"> <?php echo lang_get('no') ?> </span>
                  </label>
                </td>
              </table>
            </div>
          </div>
        </div>
        <!-- End::Show Footer -->
      </div>
    </div>
    <!-- End::General Settings -->
    <!-- Time, ETA Management -->
    <?php
    if (config_get('enable_eta')) { ?>
      <!-- Time -->
      <div class="form-container">
        <div class="widget-box widget-color-blue2">
          <!-- Title -->
          <div class="widget-header widget-header-small">
            <h4 class="widget-title lighter">
              <i class="ace-icon fa fa-text-width"></i>
              <?php echo plugin_lang_get('config_page_time_calc_title'); ?>
            </h4>
          </div>
          <!-- End::Title -->
          <!-- Head -->
          <div class="widget-body">
            <div class="widget-main no-padding">
              <div class="table-responsive">
                <table class="table table-bordered table-condensed table-striped">
                  <td class="category width-30" width="30%">
                    <?php echo plugin_lang_get('config_page_time_calc_day'); ?>
                  </td>
                  <td class="category width-30" width="30%">
                    <?php echo plugin_lang_get('config_page_time_calc_worktime'); ?>
                  </td>
                  <td class="category width-40" width="40%">
                    <?php echo plugin_lang_get('config_page_eta_unit_title'); ?>
                  </td>
                </table>
              </div>
            </div>
          </div>
          <!-- End::Head -->
          <?php
          $weekDayValue = 10;
          $weekDayConfigString = rWeekDayManager::getWorkDayConfig();
          $weekDayConfigArray = explode(';', $weekDayConfigString);
          for ($index = 0; $index < 7; $index++) {
            ?>
            <div class="widget-body">
              <div class="widget-main no-padding">
                <div class="table-responsive">
                  <table class="table table-bordered table-condensed table-striped">
                    <td class="width-30"
                        width="30%"><?php echo MantisEnum::getLabel(plugin_lang_get('config_page_time_calc_weekday_enum'), $weekDayValue); ?></td>
                    <td class="width-30" width="30%">
                      <label>
                        <input type="number" min="0" max="24" step="0.1" name="weekDayValue[]"
                               value="<?php echo $weekDayConfigArray[$index]; ?>"/>
                      </label>
                    </td>
                    <td class="width-40"
                        width="40%"><?php echo plugin_lang_get('config_page_eta_unit'); ?></td>
                  </table>
                </div>
              </div>
            </div>
            <?php
            $weekDayValue += 10;
          }
          ?>
        </div>
      </div>
      <!-- End::Time -->
      <!-- ETA Management -->
      <?php
      $thresholdCount = 0;
      $etaEnumValues = MantisEnum::getValues(config_get('eta_enum_string'));
      ?>
      <div class="form-container">
        <div class="widget-box widget-color-blue2">
          <!-- Title -->
          <div class="widget-header widget-header-small">
            <h4 class="widget-title lighter">
              <i class="ace-icon fa fa-text-width"></i>
              <?php echo plugin_lang_get('config_page_eta_management'); ?>
            </h4>
          </div>
          <!-- End::Title -->
          <!-- Standard ETA Value -->
          <div class="widget-body">
            <div class="widget-main no-padding">
              <div class="table-responsive">
                <table class="table table-bordered table-condensed table-striped">
                  <td class="category width-40" width="40%">
                    <?php echo plugin_lang_get('config_page_default_eta'); ?>
                  </td>
                  <td class="width-60" width="60%">
                    <label>
                      <select id="defaulteta" name="defaulteta">
                        <?php
                        foreach ($etaEnumValues as $etaEnumValue) {
                          echo '<option value="' . $etaEnumValue . '"';
                          check_selected(plugin_config_get('defaulteta'), $etaEnumValue);
                          echo '>' . string_display_line(get_enum_element('eta', $etaEnumValue)) . '</option>';
                        }
                        ?>
                      </select>
                    </label>
                  </td>
                </table>
              </div>
            </div>
          </div>
          <!-- End::Standard ETA Value -->
          <!-- Calc Threshold -->
          <div class="widget-body">
            <div class="widget-main no-padding">
              <div class="table-responsive">
                <table class="table table-bordered table-condensed table-striped">
                  <td class="category width-40" width="40%">
                    <?php echo plugin_lang_get('config_page_calc_threshold'); ?>
                    <br/>
                                        <span class="small">
                                            <?php echo plugin_lang_get('config_page_calc_threshold_detail'); ?>
                                        </span>
                  </td>
                  <td class="width-60" width="60%">
                    <label>
                      <input type="number" step="1" name="calcthreshold" min="0" max="100"
                             value="<?php echo plugin_config_get('calcthreshold'); ?>"/>'
                    </label>
                  </td>
                </table>
              </div>
            </div>
          </div>
          <!-- End::Calc Threshold -->
          <!-- ETA Values-->
          <!-- Head -->
          <div class="widget-body">
            <div class="widget-main no-padding">
              <div class="table-responsive">
                <table class="table table-bordered table-condensed table-striped">
                  <td class="category width-40" width="40%">
                    <?php echo plugin_lang_get('config_page_eta_name'); ?>
                  </td>
                  <td class="category width-30" width="30%">
                    <?php echo plugin_lang_get('config_page_eta_value'); ?>
                  </td>
                  <td class="category width-30" width="30%">
                    <?php echo plugin_lang_get('config_page_eta_unit_title'); ?>
                  </td>
                </table>
              </div>
            </div>
          </div>
          <!-- End::Head -->
          <?php
          foreach ($etaEnumValues as $etaEnumValue) {
            $eta = new rEta($etaEnumValue);
            ?>
            <div class="widget-body">
              <div class="widget-main no-padding">
                <div class="table-responsive">
                  <table class="table table-bordered table-condensed table-striped">
                    <td class="width-40"
                        width="40%"><?php echo string_display_line(get_enum_element('eta', $etaEnumValue)); ?></td>
                    <?php
                    if ($eta->getEtaUser() == NULL) {
                      $eta->setEtaUser(0);
                      $eta->triggerInsertIntoDb();
                    }
                    if ($eta->getEtaConfig() == ETA_NONE) {
                      echo '<td class="width-30" width="30%"><input type="hidden" name="eta_value[]" value="0"/>' . plugin_lang_get('config_page_eta_none_value') . '</td>';
                    } else {
                      echo '<td class="width-30" width="30%"><input type="number" step="0.1" name="eta_value[]" value="' . $eta->getEtaUser() . '"/></td>';
                    }
                    ?>
                    <td class="width-30"
                        width="30%"><?php echo plugin_lang_get('config_page_eta_unit'); ?></td>
                  </table>
                </div>
              </div>
            </div>
            <?php
          }
          ?>
          <!-- End::ETA Values-->
        </div>
      </div>
      <!-- End::ETA Management -->
      <!-- ETA Thresholds -->
      <div class="form-container">
        <div class="widget-box widget-color-blue2">
          <!-- Title -->
          <div class="widget-header widget-header-small">
            <h4 class="widget-title lighter">
              <i class="ace-icon fa fa-text-width"></i>
              <?php echo plugin_lang_get('config_page_roadmap_eta_threshold_management'); ?>
            </h4>
          </div>
          <!-- End::Title -->
          <!-- Head -->
          <div class="widget-body">
            <div class="widget-main no-padding">
              <div class="table-responsive">
                <table class="table table-bordered table-condensed table-striped">
                  <td class="category width-25" width="25%">
                    <?php echo plugin_lang_get('config_page_eta_threshold_to'); ?>
                  </td>
                  <td class="category width-25" width="25%">
                    <?php echo plugin_lang_get('config_page_eta_unit_title'); ?>
                  </td>
                  <td class="category width-25" width="25%">
                    <?php echo plugin_lang_get('config_page_eta_threshold_factor'); ?>
                  </td>
                  <td class="category width-25" width="25%">
                    <?php echo plugin_lang_get('config_page_profile_action'); ?>
                  </td>
                </table>
              </div>
            </div>
          </div>
          <!-- End::Head -->
          <!-- Thresholds -->
          <div id="thresholds">
            <?php
            $thresholdIds = rThresholdManager::getRThresholdIds();
            $thresholdCount = count($thresholdIds);
            if ($thresholdCount > 0) {
              # iterate through thresholds
              foreach ($thresholdIds as $thresholdId) {
                $threshold = new rThreshold($thresholdId);
                $thresholdTo = $threshold->getThresholdTo();
                $thresholdUnit = $threshold->getThresholdUnit();
                $thresholdFactor = $threshold->getThresholdFactor();
                ?>
                <div class="widget-body">
                  <div class="widget-main no-padding">
                    <div class="table-responsive">
                      <table class="table table-bordered table-condensed table-striped">
                        <td class="width-25" width="25%">
                          <label>
                            <input type="hidden" name="threshold-id[]"
                                   value="<?php echo $thresholdId; ?>"/>
                            <input type="number" step="0.1" name="threshold-to[]" size="15"
                                   maxlength="128"
                                   value="<?php echo string_display_line($thresholdTo); ?>"/>
                          </label>
                        </td>
                        <td class="width-25" width="25%">
                          <label>
                            <input type="text" name="threshold-unit[]" size="15" maxlength="128"
                                   value="<?php echo string_display_line($thresholdUnit); ?>"/>
                          </label>
                        </td>
                        <td class="width-25" width="25%">
                          <label>
                            <input type="number" step="0.1" name="threshold-factor[]" size="15"
                                   maxlength="128"
                                   value="<?php echo string_display_line($thresholdFactor); ?>"/>
                          </label>
                        </td>
                        <td class="width-25" width="25%">
                          <a class="button"
                             href="<?php echo plugin_page('config_delete') . '&amp;threshold_id=' . $thresholdId; ?>">
                            <input type="button"
                                   value="<?php echo plugin_lang_get('config_page_delete_profile'); ?>"/>
                          </a>
                        </td>
                      </table>
                    </div>
                  </div>
                </div>
                <?php
              }
            }
            ?>
          </div>
          <!-- End::Thresholds -->
          <!-- Add / Delete threshold row -->
          <div class="widget-body">
            <div class="widget-main no-padding">
              <div class="table-responsive">
                <table class="table table-bordered table-condensed table-striped">
                  <td class="width-100" width="100%">
                    <input class="btn btn-primary btn-white btn-round" type="button" id="addthresholdrownew" value="+"/>&nbsp;
                    <input class="btn btn-primary btn-white btn-round" type="button" id="delthresholdrownew" value="-"/>&nbsp;
                  </td>
                </table>
              </div>
            </div>
          </div>
          <!-- End::Add / Delete threshold row -->
        </div>
      </div>
      <!-- End::ETA Thresholds -->
      <?php
    }
    ?>
    <!-- End::Time, ETA Management -->
    <!-- Profile-Management -->
    <div class="form-container">
      <div class="widget-box widget-color-blue2">
        <!-- Title -->
        <div class="widget-header widget-header-small">
          <h4 class="widget-title lighter">
            <i class="ace-icon fa fa-text-width"></i>
            <?php echo plugin_lang_get('config_page_roadmap_profile_management'); ?>
          </h4>
        </div>
        <!-- End::Title -->
        <!-- Head -->
        <div class="widget-body">
          <div class="widget-main no-padding">
            <div class="table-responsive">
              <table class="table table-bordered table-condensed table-striped">
                <td class="category width-20" width="20%">
                  <?php echo plugin_lang_get('config_page_profile_name'); ?>
                </td>
                <td class="category width-20" width="20%">
                  <?php echo plugin_lang_get('config_page_profile_status'); ?>
                </td>
                <td class="category width-15" width="15%">
                  <?php echo plugin_lang_get('config_page_profile_color'); ?>
                </td>
                <td class="category width-15" width="15%">
                  <?php echo plugin_lang_get('config_page_profile_prio'); ?>
                </td>
                <td class="category width-15" width="15%">
                  <?php echo plugin_lang_get('config_page_profile_effort'); ?>
                </td>
                <td class="category width-15" width="15%">
                  <?php echo plugin_lang_get('config_page_profile_action'); ?>
                </td>
              </table>
            </div>
          </div>
        </div>
        <!-- End::Head -->
        <!-- Profiles -->
        <div id="profiles">
          <?php
          $profileIds = rProfileManager::getRProfileIds();
          $profileCount = count($profileIds);
          if ($profileCount > 0) {
            for ($index = 0; $index < $profileCount; $index++) {
              $profileId = $profileIds[$index];
              $profile = new rProfile($profileId);
              $dbProfileName = $profile->getProfileName();
              $dbProfileColor = $profile->getProfileColor();
              $dbProfileStatus = $profile->getProfileStatus();
              $dbProfilePriority = $profile->getProfilePriority();
              $dbProfileEffort = $profile->getProfileEffort();
              $profileStatusArray = array_map('intval', explode(';', $dbProfileStatus));
              ?>
              <div class="widget-body">
                <div class="widget-main no-padding">
                  <div class="table-responsive">
                    <table class="table table-bordered table-condensed table-striped">
                      <!-- profile name -->
                      <td class="width-20" width="20%">
                        <label>
                          <input type="hidden" name="profile-id[]"
                                 value="<?php echo $profileId; ?>"/>
                          <input type="text" name="profile-name[]" size="15" maxlength="128"
                                 value="<?php echo string_display_line($dbProfileName); ?>"/>
                        </label>
                      </td>
                      <!-- profile status -->
                      <td class="width-20" width="20%">
                        <label>
                          <select name="profile-status-<?php echo $index; ?>[]"
                                  multiple="multiple">
                            <?php
                            print_enum_string_option_list('status', $profileStatusArray);
                            ?>
                          </select>
                        </label>
                      </td>
                      <!-- profile color -->
                      <td class="width-15" width="15%">
                        <label>
                          <input class="color {pickerFace:4,pickerClosable:true}" type="text"
                                 name="profile-color[]" value="#<?php echo $dbProfileColor; ?>"/>
                        </label>
                      </td>
                      <!-- profile priority -->
                      <td class="width-15" width="15%">
                        <label>
                          <input type="number" name="profile-prio[]" size="15" maxlength="3"
                                 value="<?php echo $dbProfilePriority; ?>"/>
                        </label>
                      </td>
                      <!-- profile effort -->
                      <td class="width-15" width="15%">
                        <label>
                          <input type="number" name="profile-effort[]" size="15" maxlength="3"
                                 value="<?php echo $dbProfileEffort; ?>"/>
                        </label>
                      </td>
                      <!-- delete profile button -->
                      <td class="width-15" width="15%">
                        <a class="btn btn-primary btn-white btn-round"
                           href="<?php echo plugin_page('config_delete'); ?>&amp;profile_id=<?php echo $profileId; ?>">
                          <?php echo plugin_lang_get('config_page_delete_profile'); ?>
                        </a>
                      </td>
                    </table>
                  </div>
                </div>
              </div>
              <?php
            }
          }
          ?>
        </div>
        <!-- End::Profiles -->
        <!-- Add / Delete profile row -->
        <?php
        $statusEnumConfig = config_get('status_enum_string');
        $statusEnumValues = MantisEnum::getValues($statusEnumConfig);
        $statusEnumStrings = array();
        foreach ($statusEnumValues as $statusEnumValue) {
          array_push($statusEnumStrings, get_enum_element('status', $statusEnumValue));
        }
        $jsStatusEnumIdArray = json_encode($statusEnumValues);
        $jsStatusEnumNameArray = json_encode($statusEnumStrings);
        ?>
        <div class="widget-body">
          <div class="widget-main no-padding">
            <div class="table-responsive">
              <table class="table table-bordered table-condensed table-striped">
                <td class="width-100" width="100%">
                  <a data-state_profileid="<?php echo $jsStatusEnumIdArray; ?>"
                     data-state_profilename="<?php echo $jsStatusEnumNameArray; ?>"
                     class="btn btn-primary btn-white btn-round">+</a>
                  <input class="btn btn-primary btn-white btn-round" type="button" id="delprofilerownew" value="-"/>&nbsp;
                </td>
              </table>
            </div>
          </div>
        </div>
        <!-- End::Add / Delete profile row -->
      </div>
    </div>
    <!-- End::Profile-Management -->
    <?php
    if ($profileCount > 1) {
      ?>
      <!-- Group-Management -->
      <div class="form-container">
        <div class="widget-box widget-color-blue2">
          <!-- Title -->
          <div class="widget-header widget-header-small">
            <h4 class="widget-title lighter">
              <i class="ace-icon fa fa-text-width"></i>
              <?php echo plugin_lang_get('config_page_prfgr_management'); ?>
            </h4>
          </div>
          <!-- End::Title -->
          <!-- Head -->
          <div class="widget-body">
            <div class="widget-main no-padding">
              <div class="table-responsive">
                <table class="table table-bordered table-condensed table-striped">
                  <td class="category width-40" width="40%">
                    <?php echo plugin_lang_get('config_page_profile_name'); ?>
                  </td>
                  <td class="category width-30" width="30%">
                    <?php echo plugin_lang_get('config_page_prfgr_profiles'); ?>
                  </td>
                  <td class="category width-30" width="30%">
                    <?php echo plugin_lang_get('config_page_profile_action'); ?>
                  </td>
                </table>
              </div>
            </div>
          </div>
          <!-- End::Head -->
          <!-- Groups -->
          <div id="groups">
            <?php
            $groupIds = rGroupManager::getRGroupIds();
            $groupCount = count($groupIds);
            if ($groupCount > 0) {
              for ($index = 0; $index < $groupCount; $index++) {
                $groupId = $groupIds[$index];
                $group = new rGroup($groupId);
                $dbGroudName = $group->getGroupName();
                $dbGroupProfiles = $group->getGroupProfiles();

                $groupProfileEnumNames = array();
                $profileEnumIds = rProApi::getProfileEnumIds();
                $profileEnumNames = rProApi::getProfileEnumNames();
                $profileEnumCount = count($profileEnumIds);
                $groupProfileArray = explode(';', $dbGroupProfiles);
                foreach ($groupProfileArray as $profileId) {
                  $profile = new rProfile($profileId);
                  $profileName = $profile->getProfileName();

                  array_push($groupProfileEnumNames, $profileName);
                }
                ?>
                <div class="widget-body">
                  <div class="widget-main no-padding">
                    <div class="table-responsive">
                      <table class="table table-bordered table-condensed table-striped">
                        <!-- group name -->
                        <td class="width-40" width="40%">
                          <label>
                            <input type="hidden" name="group-id[]"
                                   value="<?php echo $groupId; ?>"/>
                            <input type="text" name="group-name[]" size="15" maxlength="128"
                                   value="<?php echo string_display_line($dbGroudName); ?>"/>
                          </label>
                        </td>
                        <!-- group profiles -->
                        <td class="width-30" width="30%">
                          <label>
                            <select name="group-profile-<?php echo $index; ?>[]"
                                    multiple="multiple">
                              <?php
                              for ($pindex = 0; $pindex < $profileEnumCount; $pindex++) {
                                $profileId = $profileEnumIds[$pindex];
                                $profileName = $profileEnumNames[$pindex];
                                echo '<option value="' . $profileId . '"';
                                check_selected($groupProfileEnumNames, $profileName);
                                echo '>' . $profileName . '</option>';
                              }
                              ?>
                            </select>
                          </label>
                        </td>
                        <!-- delete group button -->
                        <td class="width-30" width="30%">
                          <a class="btn btn-primary btn-white btn-round"
                             href="<?php echo plugin_page('config_delete'); ?>&amp;group_id=<?php echo $groupId; ?>">
                            <?php echo plugin_lang_get('config_page_delete_profile'); ?>
                          </a>
                        </td>
                      </table>
                    </div>
                  </div>
                </div>
                <?php
              }
            }
            ?>
          </div>
          <!-- End::Groups -->
          <!-- Add / Delete group row -->
          <?php
          if ($profileCount > 1) {
            $profileEnumIds = rProApi::getProfileEnumIds();
            $profileEnumNames = rProApi::getProfileEnumNames();
            $jsProfileEnumIdArray = json_encode($profileEnumIds);
            $jsProfileEnumNameArray = json_encode($profileEnumNames);
            ?>
            <div class="widget-body">
              <div class="widget-main no-padding">
                <div class="table-responsive">
                  <table class="table table-bordered table-condensed table-striped">
                    <td class="width-100" width="100%">
                      <a data-state_groupid="<?php echo $jsProfileEnumIdArray; ?>"
                         data-state_groupname="<?php echo $jsProfileEnumNameArray; ?>"
                         class="btn btn-primary btn-white btn-round">+</a>
                      <input class="btn btn-primary btn-white btn-round" data-type="minus" type="button"
                             id="delgrouprownew" value="-"/>&nbsp;
                    </td>
                  </table>
                </div>
              </div>
            </div>
            <?php
          }
          ?>
          <!-- End::Add / Delete group row -->
        </div>
      </div>
      <!-- End::Group-Management -->
      <?php
    }
    ?>
    <!-- Change Configuration Button -->
    <div class="widget-toolbox padding-8 clearfix">
      <input type="submit" class="btn btn-primary btn-white btn-round" name="config_change"
             value="<?php echo lang_get('change_configuration') ?>"/>
    </div>
    <!-- End::Change Configuration Button -->
  </form>
</div>