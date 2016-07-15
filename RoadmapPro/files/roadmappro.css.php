<?php
  $background = "#" . $_GET[ 'profile_color' ];
  header('Content-type: text/css; charset: UTF-8');
?>

hr.project-separator {
   border-top: 1px;
}

hr.version-separator {
   border-top: 1px dotted #000000;
}

.progress-suffix {
   position: relative;
   background: #fff;
   background-color: #fff;
   height: 25px;
   margin-top: 1em;
   margin-bottom: 1em;
   padding: 1px 4px 1px 1px;
   float: left;
}

.progress9000 {
   position: relative;
   background: #fff;
   background-color: #fff;
   width: 400px;
   height: 25px;
   border: 1px solid #d7d7d7;
   -moz-border-radius: 6px;
   -webkit-border-radius: 6px;
   border-radius: 6px;
   margin-top: 1em;
   margin-bottom: 1em;
   padding: 1px 4px 1px 1px;
   float: left;
}

.progress9000 .bar {
   padding: 0px 0px 0px 0px;
   display: block;
   position: relative;
   background: <?php echo $background ?>;
   text-align: center;
   font-weight: normal;
   height: 1.5em;
   line-height: 1.4em;
   color: #111;
   border: solid 1px <?php echo $background ?>;
   -moz-border-radius: 4px;
   -webkit-border-radius: 4px;
   border-radius: 4px;
}

.progress9000 .scaledbar {
   padding: 0px 0px 0px 1px;
   display: block;
   position: absolute;
   text-align: right;
   font-weight: normal;
   height: 1.5em;
   line-height: 1.4em;
   color: #111;
   -moz-border-radius: 4px;
   -webkit-border-radius: 4px;
   border-radius: 4px;
}

pre {
   display: inline-block;
   white-space: pre-wrap; /* CSS 3 */
   white-space: -moz-pre-wrap; /* Mozilla, since 1999 */
   white-space: -pre-wrap; /* Opera 4-6 */
   white-space: -o-pre-wrap; /* Opera 7 */
   word-wrap: break-word; /* Internet Explorer 5.5+ */
}

div.rcv_tooltip_title {
   border-bottom: 1px solid #777;
   text-align: left;
}

div.rcv_tooltip_content {
   margin-top: 4px;
   text-align: left;
}

.rcv_tooltip {
   outline: none;
}

.rcv_tooltip strong {
   line-height: 30px;
}

.rcv_tooltip:hover {
   text-decoration: none;
}

.rcv_tooltip span {
   z-index: 10;
   display: none;
   padding: 4px 8px;
   margin-top: 30px;
   margin-left: -200px;
   width: 250px;
   line-height: 16px;
}

.rcv_tooltip:hover span {
   display: inline;
   position: absolute;
   border: 1px solid #777;
   color: #121212;
   background-color: #dedede;
   background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#fdfdfd), to(#dedede));
   background-image: -webkit-linear-gradient(top, #fdfdfd, #dedede);
   background-image: -moz-linear-gradient(top, #fdfdfd, #dedede);
   background-image: -ms-linear-gradient(top, #fdfdfd, #dedede);
   background-image: -o-linear-gradient(top, #fdfdfd, #dedede);
   background-image: linear-gradient(top, #fdfdfd, #dedede);
}

/* CSS3 */
.rcv_tooltip span {
   border: solid 1px #777;
   -moz-border-radius: 6px;
   -webkit-border-radius: 6px;
   border-radius: 6px;
}

div.table {
   display: table;
   border-collapse: collapse;
}

div.table_center {
   margin-left: auto;
   margin-right: auto;
   display: table;
   border-collapse: collapse;
}

div.tr {
   display: table-row;
}

div.td {
   display: table-cell;
   border: none;
   padding: 5px;
}

div.spacer {
   border: none;
   padding: 5px;
   margin-top: 30px;
}

.title_row {
   background-color: #ffffff;
   color: #000000;
   font-weight: bold;
   text-align: left;
}

.category_name_field {
   background-color: #c8c8e8;
   color: #000000;
   font-weight: bold;
}

.category_value_field-0 {
   background-color: #d8d8d8;
   color: #000000;
}

.category_value_field-1 {
   background-color: #e8e8e8;
   color: #000000;
}

.grid_center {
   text-align: center;
}

.surrounder {
   border: solid 1px #000000;
}

* {
   box-sizing: border-box;
}

.gridcol-1 {
   width: 16.66%;
}

.gridcol-2 {
   width: 33.33%;
}

.gridcol-3 {
   width: 50%;
}

.gridcol-4 {
   width: 66.66%;
}

.gridcol-5 {
   width: 83.33%;
}

.gridcol-6 {
   width: 100%;
}

[class*="gridcol-"] {
   float: left;
   min-height: 27px;
   word-wrap: break-word;
   /*   border: 1px solid #c13cff;*/
}

.row {
   width: 100%;
}

.row::after {
   content: "";
   clear: both;
   display: block;
}

div {
   padding: 1px;
}

#container2 {
   clear: left;
   float: left;
   width: 100%;
   overflow: hidden;
   background: #ffa7a7; /* column 2 background colour */
}

#container1 {
   float: left;
   width: 100%;
   position: relative;
   right: 50%;
   background: #fff689; /* column 1 background colour */
}

#col1 {
   float: left;
   width: 46%;
   position: relative;
   left: 52%;
   overflow: hidden;
}

#col2 {
   float: left;
   width: 46%;
   position: relative;
   left: 56%;
   overflow: hidden;
}