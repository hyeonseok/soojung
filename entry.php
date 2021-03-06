<?php
if (!isset($_GET['blogid'])) {
  exit();
}
if (isset($_GET['blogid']) && !is_numeric($_GET['blogid'])) {
  exit();
}

include_once("settings.php");

if (isset($_POST["blogid"])) {
  $blogid = $_POST["blogid"];
  $name = $_POST["name"];
  $email = $_POST["email"];
  $url = $_POST["url"];
  $body = $_POST["body"];
  
  if (empty($blogid) || empty($name) || empty($body)) {
    echo "input name and comment";
    exit();
  }
  if ($url == "http://") {
    $url = "";
  }
  $t = time();
  $name = trim(strip_tags($name));
  $email = trim(strip_tags($email));
  $url = trim(strip_tags($url));
  $body = nl2br(trim(htmlspecialchars($body)));
  Comment::writeComment($blogid, $name, $email, $url, $body, $t);

  // Remembering 30 days
  setcookie('w_id',    $blogid, $t+2592000);
  setcookie('w_name',  $name,   $t+2592000);
  setcookie('w_email', $email,  $t+2592000);
  setcookie('w_url',   $url,    $t+2592000);

  $entry = Entry::getEntry($blogid);
  $temp = new UserTemplate("entry.tpl", $blogid);
  $temp->clearCache();
  header("Location: " . $entry->getHref() . "#CO" . $t);
  exit;
}

$blogid = $_GET["blogid"];

$template = new UserTemplate('entry.tpl', $blogid);
if (!$template->is_cached('entry.tpl', $blogid)) {
  $entry = Entry::getEntry($blogid);
  if ($entry === null) {
    header('Location: ' . $blog_baseurl);
    exit();
  }
  if ($_SERVER['REQUEST_URI'] != $entry->getHref()) {
    header('Location: ' . $entry->getHref());
    exit();
  }
  if ($entry->isSetOption("SECRET")) {
    exit;
  }
  $template->assign('entry', $entry);
  $template->assign('trackbacks', $entry->getTrackbacks());
  $template->assign('comments', $entry->getComments());
}

foreach (array('w_id','w_name','w_email','w_url') as $key) {
  if (isset($HTTP_COOKIE_VARS[$key])) {
    $template->assign("$key", $HTTP_COOKIE_VARS[$key]);
  }
}

$template->display('entry.tpl', $blogid);

# vim: ts=8 sw=2 sts=2 noet
?>
