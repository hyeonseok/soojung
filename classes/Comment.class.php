<?php

class Comment {

  var $date;
  var $name;
  var $email;
  var $homepage;
  var $filename;
  var $href;

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
  function Comment($filename) { //FIXME: parameter change to entry's id
    $this->filename = $filename;
    $fd = fopen($filename, "r");
    $this->date = fgets($fd);
    $this->name = fgets($fd);
    $this->email = fgets($fd);
    $this->homepage = fgets($fd);
    fclose($fd);
    //TODO: href
  }

  function getFilename() {
    return $this->filename;
  }

  function getDate() {
    return $this->date;
  }

  function getName() {
    return $this->name;
  }

  function getEmail() {
    return $this->email;
  }

  function getHomepage() {
    return $this->homepage;
  }

  function getHref() {
    //TODO: impl
  }

  function getBody() {
    $fd = fopen($this->filename, "r");
    //ignore date, name, email, homepage
    fgets($fd);
    fgets($fd);
    fgets($fd);
    fgets($fd);
    $body = fread($fd, filesize($filename));
    fclose($fd);
    return $body;
  }

  /**
   * static method
   */
  function writeCommenet($entryId, $name, $email, $homepage, $body, $date) {
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
    fwrite($fd, $url);
    fwrite($fd, "\r\n");
    fwrite($fd, $body);
    fclose($fd);

    $msg =  $name . " said:<br />";
    $msg .= $body;
    Soojung::notifyToAdmin("new comment", $entryId, $msg);
  }

  /**
   * static method
   */
  function getRecentComments($count=10) {
    $comment_filenames = array();
    $dirs = Soojung::queryFilenameMatch("^[0-9]+$", "contents/");
    foreach ($dirs as $dir) {
      $files = Soojung::queryFilenameMatch("[.]comment$", $dir . "/");
      foreach ($files as $file) {
	$comment_filenames[] = $file;
      }
    }
    usort($comment_filenames, "cmp_base_filename");

    $comment_filenames = array_slice($comment_filenames, 0, $count);
    $comments = array();
    foreach ($comment_filenames as $f) {
      $comments[] = new Comment($f);
    }
    return $comments;
  }

}

?>