<?php

$soojung_version = '0.4.14';
setlocale(LC_TIME, "C");

header("Content-type: text/html; charset=utf-8");
if (!file_exists("config.php")) {
  echo "please execute <a href=\"install.php\">install.php</a> first.";
  exit;
}

include_once("config.php");
include_once("libs/util.php");

include_once("classes/Formatter.class.php");
include_once("classes/Entry.class.php");
include_once("classes/Soojung.class.php");
include_once("classes/Comment.class.php");
include_once("classes/Trackback.class.php");
include_once("classes/Archive.class.php");
include_once("classes/Category.class.php");
include_once("classes/Export.class.php");
include_once("classes/Import.class.php");
include_once("classes/Calendar.class.php");

define('SMARTY_DIR', 'libs/smarty/');
require(SMARTY_DIR . 'Smarty.class.php');

include_once("classes/Template.class.php");
include_once("classes/UserTemplate.class.php");
include_once("classes/AdminTemplate.class.php");

if (get_magic_quotes_gpc()) {
  function stripslashes_deep($value) {
    $value = is_array($value) ?
      array_map('stripslashes_deep', $value) :
      stripslashes($value);
    return $value;
  }
  $_POST = array_map('stripslashes_deep', $_POST);
  $_GET = array_map('stripslashes_deep', $_GET);
  $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}

/* clear global variables */
if (ini_get("register_globals")) {
  if(count($_GET))
    foreach($_GET as $key => $value)
      unset(${$key});
  if(count($_POST))
    foreach($_POST as $key => $value)
      unset(${$key});
}

# vim: ts=8 sw=2 sts=2 noet
?>
