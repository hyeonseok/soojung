<?php
if (!isset($_REQUEST['blogid'])) {
  $error = 'blogid is required.';
}
if (isset($_GET['__mode']) && $_GET['__mode'] !== 'rss') {
  $error = '__mode should be rss.';
}
if (!isset($_GET['__mode']) && !isset($_GET['url'])) {
  $error = 'url is required.';
}

include_once("settings.php");

if (!Entry::exists($_REQUEST["blogid"])) {
  $error = 'Article not found.';
}

header("Content-type: text/xml");

if (isset($error)) {
  exit('<?xml version="1.0" encoding="utf-8"?><response><error>1</error><message>' . $error . '</message></response>');
}

if (isset($_GET['__mode'])) {
  $blogid = $_GET["blogid"];
  $entry = Entry::getEntry($blogid);

  $excerpt = strip_tags($entry->getBody());
  if (strlen ($excerpt) > 255)
    $excerpt = substring($excerpt,252);

  echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
  echo "<response>\n";
  echo "<error>0</error>\n";
  echo '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">'."\n";
  echo "<channel>\n";
  echo "<title>".$blog_name."</title>\n";
  echo "<link>".$blog_baseurl."/trackback.php?blogid=".$_GET["blogid"]."</link>\n";
  echo "<description>".$blog_desc."</description>\n";
  echo "<item>\n";
  echo "<title>".$entry->title."</title>\n";
  echo "<link>".$entry->getHref()."</link>\n";
  echo "<description>".$excerpt."</description>\n";
  echo "</item>\n";
  echo "</channel>\n";
  echo "</rss></response>\n";
  exit;
}

if (isset($_REQUEST["url"])) {
  $id = $_REQUEST["blogid"];
  $url = $_REQUEST["url"];
  $title = stripslashes($_REQUEST["title"]);
  $excerpt = stripslashes(strip_tags($_REQUEST["excerpt"]));
  if (strlen ($excerpt) > 255)
    $excerpt = substring($excerpt,252);  
  $name = stripslashes($_REQUEST["blog_name"]);
  $title = convert_to_utf8($title);
  $excerpt = convert_to_utf8($excerpt);
  $name = convert_to_utf8($name);
  
    Trackback::writeTrackback($id, $url, $name, $title, $excerpt);
    $temp = new Usertemplate("index.tpl", 1);
    $temp->clearCache();
    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    echo "<response>\n";
    echo "<error>0</error>\n";
    echo "</response>\n";
}

# vim: ts=8 sw=2 sts=2 noet
?>
