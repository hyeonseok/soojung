<?php

class Soojung {

  public static function queryFilenameMatch($query, $path="contents/") {
    $list = array();
    if (is_dir($path) === false) {
      return $list;
    }
    $file_list = scandir($path);
    if ($file_list === false) {
      return $list;
    }
    foreach($file_list as $file) {
      if (preg_match($query, $file)) {
	$list[] = $path . $file;
      }
    }
    return $list;
  }

  public static function queryNumFilenameMatch($query, $path="contents/") {
    $number = 0;
    if (is_dir($path) === false) {
      return $number;
    }
    $file_list = scandir($path);
    if ($file_list === false) {
      return $number;
    }
    foreach($file_list as $file) {
      if (preg_match($query, $file)) {
	$number++;
      }
    }
    return $number;
  }

  public static function entryIdToFilename($entryId) {
    $f = Entry::getEntryList("/_" . $entryId . "[.]entry$/", 1);
    return $f[0];
  }

  public static function filenameToEntryId($filename) {
    if (strpos($filename, ".entry") != false) {
      $i = strrpos($filename, "_") + 1;
      $j = strrpos($filename, ".");
      return substr($filename, $i, $j-$i);
    } else { //comment, trackback
      $dirs = explode("/", $filename);
      return $dirs[1];
    }
  }

  public static function notifyToAdmin($title, $entryId, $msg) {
    global $notify, $admin_email;
    if ($notify != true) {
      return;
    }

    $entry = Entry::getEntry($entryId);
    $message = "<html><head></head><body>";
    $message .= $msg;
    $message .= "<br /><a href=\"" . $entry->getHref() . "\">check out</a>";
    $message .= "</body></html>";

    $title = "soojung: " . $title;
    mail($admin_email, $title, $message, "Content-Type: text/html; charset=\"utf-8\"");
  }

  public static function createNewEntryId() {
    clearstatcache();
    $fd = fopen("contents/.info", "r");
    flock($fd, LOCK_SH);
    $i = trim(fread($fd, filesize("contents/.info")));
    flock($fd, LOCK_UN);
    fclose($fd);
    
    locked_filewrite("contents/.info", ((int)$i)  + 1);
    return $i;
  }

  public static function getTemplates() {
    $list = array();
    $filenames = Soojung::queryFilenameMatch("/.+/", "templates/");
    foreach($filenames as $filename) {
      $filename = basename($filename);
      if ($filename == "admin" or $filename == "CVS") {
	continue;
      }
      if ($filename[0] != '.') {
	$list[] = $filename;
      }
    }
    return $list;
  }

  public static function getFormatter($format) {
    switch($format) {
    case "plain":
      return new PlainFormatter();
    case "html":
      return new HtmlFormatter();
    case "bbcode":
      return new BBcodeFormatter();
    default:
      return new Formatter();
    }
  }

  public static function writeConfigFile($blogname, $blogdesc, $blogurl, $perpage, $blogfancyurl, $blognotify,
			     $adminname, $adminemail, $adminpassword, $skin = "simple", $license = "none",
			     $words="수신거부\n기적의 영문법") {
    $fd = fopen("config.php", "w");
    fwrite($fd, "<?php\n");
    fwrite($fd, '$blog_name="' . $blogname . "\";\n");
    fwrite($fd, '$blog_desc="' . $blogdesc . "\";\n");
    fwrite($fd, '$blog_baseurl="' . trim_slash($blogurl) . "\";\n");
    fwrite($fd, '$blog_entries_per_page=' . $perpage . ";\n");
    if ($blogfancyurl == "on") {
      fwrite($fd, '$blog_fancyurl=true;' . "\n");
    } else {
      fwrite($fd, '$blog_fancyurl=false;' . "\n");
    }
    if ($blognotify == "on") {
      fwrite($fd, '$notify=true;' . "\n");
    } else {
      fwrite($fd, '$notify=false;' . "\n");
    }
    fwrite($fd, '$blog_skin="' . $skin . "\";\n");
    fwrite($fd, '$admin_name="' . $adminname . "\";\n");
    fwrite($fd, '$admin_email="' . $adminemail . "\";\n");
    if ($adminpassword === FALSE) {
      global $admin_password;
      fwrite($fd, '$admin_password="' . $admin_password . "\";\n");
    } else {
      fwrite($fd, '$admin_password="' . $adminpassword . "\";\n");
    }
    fwrite($fd, '$entries_license="' . $license . "\";\n");
    fwrite($fd, "?>");
    fclose($fd);
  }

  function writeHtaccess($filename = ".htaccess") {
    $f = fopen($filename, "w");
    fwrite($f, "RewriteEngine On\n");
    fwrite($f, "RewriteRule ^(.+)/([0-9]+)/([0-9]+)/([0-9]+)/([0-9]+)[.]html$ ");
    fwrite($f, "entry.php?blogid=$5\n");
    fwrite($f, "RewriteRule ^([0-9]+)/([0-9]+)/([0-9]+) ");
    fwrite($f, "index.php?archive=$1$2$3\n");
    fwrite($f, "RewriteRule ^([0-9]+)/([0-9]+) ");
    fwrite($f, "index.php?archive=$1$2\n");
    fwrite($f, "RewriteRule ^page/([0-9]+)$ ");
    fwrite($f, "index.php?page=$1\n");
    fwrite($f, "RewriteRule ^([^/.]+)$ ");    
    fwrite($f, "index.php?category=$1\n");
    fwrite($f, "RewriteRule ^([^/.]+)/([^/.]+)$ ");
    fwrite($f, "index.php?category=$1/$2\n");
    fclose($f);
  }

  function deleteHtaccess($filename = ".htaccess") {
    // FIXME: file permission error. unlink disable.
    $f = fopen($filename, "w");
    fwrite($f, "");
    fclose($f);
  }
}

# vim: ts=8 sw=2 sts=2 noet
?>
