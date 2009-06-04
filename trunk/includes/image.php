<?php 

if (!is_null($_REQUEST["uuid"]) && !is_null($_REQUEST["name"])) {

  require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $query = "SELECT serverIP, serverHttpPort FROM regions WHERE uuid = '" . $_REQUEST["uuid"] . "'";
    $result = mysql_query($query);

    if ($result && mysql_numrows($result)) {
      $source = "http://" . mysql_result($result,0,"serverIP") . ":" . mysql_result($result,0,"serverHttpPort") . "/index.php?method=regionImage" . str_replace("-", "", $_REQUEST["uuid"]);
//      $source = "http://94.102.156.98:9000/index.php?method=regionImage" . str_replace("-", "", $_REQUEST["uuid"]);
      $handle = @fopen($source,'rb');
      if ($handle) {
	while(!feof($handle)) {
	  $content .= fread($handle, 1024);
	}
	header('Content-Length: ' . strlen($content));
	header('Content-Type: image/jpeg');
	echo $content; 
      }
    } 

    // Close the database
    mysql_close();


}
?>
