<?php

class Trackback {
  
  var $date;
  var $url;
  var $name;
  var $title;
  var $excerpt;
  var $filename;

  /**
   * Trackback file name:
   * contents/[entryId]/[date].trackback
   *
   * Comment file structure:
   * [date]\r\n
   * [url]\r\n
   * [name]\r\n
   * [title]\r\n
   * [excerpt]
   */
  function Trackback($filename) {
    $this->filename = $filename;
    $fd = fopen($filename, "r");
    $this->date = trim(fgets($fd, 1024));
    $this->url = trim(fgets($fd, 1024));
    $this->name = $this->escape(trim(fgets($fd, 1024)));
    $this->title = $this->escape(trim(fgets($fd, 1024)));
    fclose($fd);
  }

  function escape($str) {
    if (strpos($str, '&') !== false && strpos($str, ';') === false) {
      return htmlspecialchars($str);
    } else if (strpos($str, '&amp;#')) {
      return htmlspecialchars_decode($str);
    } else {
      return $str;
    }
  }

  function getHref() {
    $id = Soojung::filenameToEntryId($this->filename);
    $e = Entry::getEntry($id);
    return $e->getHref() . "#TB" . $this->date;
  }

  function getExcerpt() {
    $fd = fopen($this->filename, "r");
    //ignore date, url, name, title
    fgets($fd, 1024);
    fgets($fd, 1024);
    fgets($fd, 1024);
    fgets($fd, 1024);
    $excerpt = $this->escape(strip_tags(fread($fd, filesize($this->filename))));
    fclose($fd);
    return $excerpt;
  }

  /**
   * need to check entryId is not null or anything. this is caused by tattertools.
   * static method
   */
  function writeTrackback($entryId, $url, $name, $title, $excerpt, $date = false) {

    $e = Entry::getEntry($entryId);
    if ($e->isSetOption("NO_TRACKBACK")) {
      return;
    }

    $dirname = "contents/" . $entryId;
    @mkdir($dirname, 0777);

    if ($date == false) {
      $date = time();
    }
    $filename = date('YmdHis', $date) . '.trackback';

    $name = getFirstLine($name);
    $title = getFirstLine($title);

    $fd = fopen($dirname . '/' . $filename, "w");
    fwrite($fd, $date);
    fwrite($fd, "\r\n");
    fwrite($fd, $url);
    fwrite($fd, "\r\n");
    fwrite($fd, $name);
    fwrite($fd, "\r\n");
    fwrite($fd, $title);
    fwrite($fd, "\r\n");
    fwrite($fd, $excerpt);
    fclose($fd);

    $msg = "trackback from " . $url . "<br />";
    Soojung::notifyToAdmin("new trackback", $entryId, $msg);
    Trackback::cacheTrackbackList();
  }

  /**
   * static method
   */
  function cacheTrackbackList() {
    $filenames = array();
    $dirs = Soojung::queryFilenameMatch("/^[0-9]+$/", "contents/");
    foreach ($dirs as $dir) {
      $files = Soojung::queryFilenameMatch("/[.]trackback$/", $dir . "/");
      foreach ($files as $file) {
	$filenames[] = $file;
      }
    }
    usort($filenames, "cmp_base_filename");

    return fwrite(fopen('contents/.trackbackList', w), implode("\n", $filenames));
  }

  /**
   * static method
   */
  function getRecentTrackbacks($count=10) {
    if (file_exists('contents/.trackbackList') === false) {
      Trackback::cacheTrackbackList();
    }
    $fp = fopen('contents/.trackbackList', 'r');
    while (($buffer = fgets($fp)) !== false) {
      $trackbacks[] = new Trackback(trim($buffer));
      if (count($trackbacks) >= $count) {
        break;
      }
    }

    return $trackbacks;
  }

  /**
   * static method
   */
  function sendTrackbackPing($entryId, $trackbackUrl, $encoding="UTF-8") {
    global $blog_name;
  
    $tb_url = parse_url($trackbackUrl);
    if (isset ($tb_url['port']))
      $tb_port = $tb_url['port'];
    else
      $tb_port = 80;
    $tb_path = $tb_url['path'];
    if($tb_path == '') $tb_path = '/';
    if($tb_url['query']) $tb_path .= '?'.$tb_url['query'];

    //  $permlink = rawurlencode(get_entry_link($blogid, false));
    $entry = Entry::getEntry($entryId);
    $permlink = 'http://' . $_SERVER['HTTP_HOST'] . rawurlencode($entry->getHref());

    if ($entry->title != null) {
      $tb_title = rawurlencode(mb_convert_encoding($entry->title, "UTF-8", $encoding));
    } else {
      $tb_title = rawurlencode('title');
    }

    $tb_excerpt = strip_tags($entry->getBody());
    if (strlen ($tb_excerpt) > 255) {
      $tb_excerpt = substring($tb_excerpt,252);
    }

    $tb_excerpt = rawurlencode(mb_convert_encoding($tb_excerpt, "UTF-8", $encoding));

    if (isset($blog_name)) {
      $tb_blogname = rawurlencode(mb_convert_encoding($blog_name, "UTF-8", $encoding));
    } else {
      $tb_blogname = rawurlencode('soojung blog');
    }

    $query_string = "title=$tb_title&url=$permlink&excerpt=$tb_excerpt&blog_name=$tb_blogname";
    $query_string = mb_convert_encoding($query_string, 'UTF-8', $encoding);

    # only for debugging
    #echo "query_string : $query_string<br />"; //debug code?

    $http_request  = "POST ".$tb_path." HTTP/1.1\r\n";
    $http_request .= "Host: ".$tb_url['host']."\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded";
    if (strtolower($encoding) == "cp949") {
      $http_request .= "; charset=euc-kr\r\n";
    } else {
      $http_request .= "; charset=".strtolower($encoding)."\r\n";
    }
    $http_request .= "Content-Length: ".strlen($query_string)."\r\n\r\n";
    $http_request .= $query_string."\r\n\r\n";

    $response = array();
    if (!($fp = fsockopen($tb_url['host'], $tb_port))) {
      // Cannot open trackback url
      $response['error'] = 1;
      $response['message'] = "Cannot connect to host \"".$tb_url['host']."\"";
      return $response;
    } 

    if (!fputs($fp, $http_request)) {
      echo "cannot send trackback ping<br />\n";
    }

    $line = "";
    while (!feof ($fp)) {
      $line .= fgets ($fp, 1024);
    }

    if (preg_match("/<error>[^<0-9]*([0-9]*)[^<0-9]*</error>/", $line, $regs)) {
      $response['error'] = $regs[1];
      if ($response == 0 && preg_match("/<message>([<]*)</message>/", $line, $regs)) {
	$response['message'] = $regs[1];
      }
    }

    fclose ($fp);
    return $response;
  }

}

# vim: ts=8 sw=2 sts=2 noet
?>
