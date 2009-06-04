<?php
require("settings.php");

if (file_exists("../map/no-mapimage.jpg")) {
  $handle = fopen("../map/no-mapimage.jpg", 'r');
  if ($handle) {
    while (!feof($handle))
      $content .= fread($handle,1024);

    fclose($handle);
  }
}

if(!is_null($_REQUEST['x']) && !is_null($_REQUEST['y']))
{
    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    // Get online user count
    $query="SELECT * FROM regions WHERE locX = '" . $_REQUEST['x'] . "' AND locY = '" . $_REQUEST['y'] . "'";
    $result = mysql_query($query);
    if ($result && mysql_numrows($result)) {
      $uuid = str_replace("-", "", mysql_result($result, 0, "uuid"));
      $server = "http://" . mysql_result($result, 0, "serverIP") . ":" . mysql_result($result, 0, "serverPort");

      $source = $server . "/index.php?method=regionImage" . $uuid;

      @$handle = fopen($source,'r');
      if ($handle) {
	$content = NULL;
	while (!feof($handle))
          $content .= fread($handle,1024);

        fclose($handle);
      }

    }
}

header('Content-Length: ' . strlen($content));
header('Content-Type: image/jpeg');
echo $content;

?>
