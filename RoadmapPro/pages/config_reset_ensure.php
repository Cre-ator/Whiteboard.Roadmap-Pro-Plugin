<?php

auth_reauthenticate ();
html_page_top1 ( plugin_lang_get ( 'config_reset_ensure_title' ) );
html_page_top2 ();

echo '<div align="center">';
echo '<hr size="1" width="50%" />';
echo plugin_lang_get ( 'config_reset_ensure_expl' ) . '<br/><br/>';

echo '<form action="' . plugin_page ( 'config_reset_confirm' ) . '" method="post">';

echo '<table class="width50" cellspacing="1">';

echo '<tr>';
echo '<td class="center">';
echo '<input type="submit" name="con_reset" class="button" value="' . plugin_lang_get ( 'config_reset_ensure_conf' ) . '"/>&nbsp';
echo '<input type="submit" name="not_reset" class="button" value="' . plugin_lang_get ( 'config_reset_ensure_dont' ) . '"/>';
echo '</td>';
echo '</tr>';

echo '</table>';

echo '</form>';