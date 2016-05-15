<?php

class Comment {

  var $date;
  var $name;
  var $email;
  var $homepage;
  var $filename;

  /**
   * Comment file name:
   * contents/[entryId]/[date].comment
   *
   * Comment file structure:
   * [date]\r\n
   * [name]\r\n
   * [email]\r\n
   * [homepage]\r\n
   * [body]
   */
  function Comment($filename) {
    $this->filename = $filename;
    $fd = fopen($filename, "r");
    $this->date = trim(fgets($fd, 1024));
    $this->name = trim(fgets($fd, 1024));
    $this->email = trim(fgets($fd, 1024));
    $this->homepage = trim(fgets($fd, 1024));
    fclose($fd);
  }

  function getHref() {
    $id = Soojung::filenameToEntryId($this->filename);
    $e = Entry::getEntry($id);
    return $e->getHref() . "#CO" . $this->date;
  }

  function getBody() {
    $fd = fopen($this->filename, "r");
    //ignore date, name, email, homepage
    fgets($fd, 1024);
    fgets($fd, 1024);
    fgets($fd, 1024);
    fgets($fd, 1024);
    $body = fread($fd, filesize($this->filename));
    fclose($fd);
    return $body;
  }

  static function writeComment($entryId, $name, $email, $homepage, $body, $date) {
    $e = Entry::getEntry($entryId);
    if ($e->isSetOption("NO_COMMENT")) {
      return;
    }
    
    $dirname = "contents/" . $entryId;
    @mkdir($dirname, 0777);

    $filename = date('YmdHis', $date) . '.comment';
    $fd = fopen($dirname . '/' . $filename, "w");
    fwrite($fd, $date);
    fwrite($fd, "\r\n");
    fwrite($fd, $name);
    fwrite($fd, "\r\n");
    fwrite($fd, $email);
    fwrite($fd, "\r\n");
    fwrite($fd, $homepage);
    fwrite($fd, "\r\n");
    fwrite($fd, $body);
    fclose($fd);

    $msg =  $name . " said:<br />";
    $msg .= $body;
    Soojung::notifyToAdmin("new comment", $entryId, $msg);
    Comment::cacheCommentList();
  }

  static function cacheCommentList() {
    $comment_filenames = array();
    $dirs = Soojung::queryFilenameMatch("/^[0-9]+$/", "contents/");
    foreach ($dirs as $dir) {
      $files = Soojung::queryFilenameMatch("/[.]comment$/", $dir . "/");
      foreach ($files as $file) {
	$comment_filenames[] = $file;
      }
    }
    usort($comment_filenames, "cmp_base_filename");

    return fwrite(fopen('contents/.commentList', 'w'), implode("\n", $comment_filenames));
  }

  static function getRecentComments($count=10) {
    if (file_exists('contents/.commentList') === false) {
      Comment::cacheCommentList();
    }
    $fp = fopen('contents/.commentList', 'r');
    while (($buffer = fgets($fp)) !== false) {
      $comments[] = new Comment(trim($buffer));
      if (count($comments) >= $count) {
        break;
      }
    }

    return $comments;
  }

}

# vim: ts=8 sw=2 sts=2 noet
?>
