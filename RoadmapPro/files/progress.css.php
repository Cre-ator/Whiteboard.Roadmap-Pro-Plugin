<?php
  $background = "#" . $_GET[ 'profile_color' ];
  header('Content-type: text/css; charset: UTF-8');
?>

.progress9000 {
    position: relative;
    background: #fff;
    background-color: #fff;
    width: 400px;
    border: 1px solid #d7d7d7;
    -moz-border-radius: 6px;
    -webkit-border-radius: 6px;
    border-radius: 6px;
    margin-top: 1em;
    margin-bottom: 1em;
    padding: 1px 4px 1px 1px;
}

.progress9000 .bar {
    padding: 0px 0px 0px 1px;
    display: block;
    position: relative;
    background: <?php echo $background ?>;
    background-color: <?php echo $background ?>;
    background-image: -webkit-linear-gradient(top, <?php echo $background ?>,<?php echo $background ?>);
    background-image: -moz-linear-gradient(top, <?php echo $background ?>, <?php echo $background ?>);
    background-image: -ms-linear-gradient(top, <?php echo $background ?>, <?php echo $background ?>);
    background-image: -o-linear-gradient(top, <?php echo $background ?>, <?php echo $background ?>);
    background-image: linear-gradient(top, <?php echo $background ?>, <?php echo $background ?>);
    text-align: center;
    font-weight: normal;
    height: 1.4em;
    line-height: 1.4em;
    color: #111;
    border: solid 1px <?php echo $background ?>;
    -moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    border-radius: 4px;
}