<?php

/*
*   OpenSim Class file
*
*   @version: opensim.php 2009-06-03
*   
*   Copyright (c) <YEAR>, <OWNER>
*   All rights reserved.
*
*/

require("settings.php");

class OpenSim
{
  public $user_count;
  public $region_count;
  public $online_count;
  public $unique_count;

  function __construct() {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    // Get online user count
    $query = "SELECT * FROM agents where agentOnline=1";
    if (mysql_query($query))
      $this->online_count = mysql_numrows(mysql_query($query));

    // Get unique count
    $last = time() - 2592000;
    $query="SELECT * FROM agents where loginTime >= $last OR logoutTime >= $last";
    if (mysql_query($query))
      $this->unique_count = mysql_numrows(mysql_query($query));

    // Get Total user count
    $query="SELECT * FROM users";
    if (mysql_query($query))
      $this->user_count = mysql_numrows(mysql_query($query));

    // Get Region count
    $query="SELECT * FROM regions";
    if (mysql_query($query))
      $this->region_count = mysql_numrows(mysql_query($query));

    // Close the database
    mysql_close();
  }

  function getRegionList($search="", $start=0, $end=0) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $query = "SELECT regions.*, users.username, users.lastname FROM regions LEFT JOIN users ON regions.owner_uuid = users.UUID";
    if (!is_null($search) && $search != "") $query .= $this->cleanQuery(" WHERE regions.regionName LIKE '$search'");
    $query .= " ORDER BY regionName";

    if ($start || $end)	$query .= $this->cleanQuery(" LIMIT $start, $end");

    if ($result = mysql_query($query)) {
      if (mysql_numrows($result)) {
        while($row=mysql_fetch_assoc($result)) {
          $array[] = $row;
        }
      }
    }

    return $array;

    // Close the database
    mysql_close();
  }

  function getRegionBitmap($uuid) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $query = $this->cleanQuery("SELECT serverIP, serverHttpPort FROM regions WHERE uuid = '$uuid'");
    $result = mysql_query($query);

    if ($result) {
      $source = "http://" . mysql_result($result,0,"serverIP") . ":" . mysql_result($result,0,"serverHttpPort") . "/index.php?method=regionImage" . str_replace("-", "", $uuid);
      $handle = fopen($source,'r');
      while(!feof($handle)) {
	$content .= fread($handle,1024);
      }
      fclose($handle);
      return $content;
    }

    // Close the database
    mysql_close();
    
  }

  function getOnlineList($search="", $start=0, $end=0) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $query = "SELECT agents.*, users.username, users.lastname, regions.regionName FROM agents LEFT JOIN users ON agents.UUID = users.UUID LEFT JOIN regions ON agents.currentRegion = regions.uuid WHERE agents.agentOnline = 1";

    if ($search != "") $query .= $this->cleanQuery(" AND users.username LIKE '$search' OR users.lastname LIKE '$search'");
    $query .= " ORDER BY agents.loginTime";

    if ($start || $end)	$query .= $this->cleanQuery(" LIMIT $start, $end");

    if ($result = mysql_query($query)) {
      if (mysql_numrows($result)) {
        while($row=mysql_fetch_assoc($result)) {
          $array[] = $row;
        }
      }
    }

    return $array;

    // Close the database
    mysql_close();
  }

  function getFullUserList($search="", $start=0, $end=0) {
    require("settings.php");

    // Split the search terms
    if ($search !="") {
	if (strpos($search, " ")) {
	  $split = explode(" ", $search, 2);
	  $fname = $split[0];
	  $lname = $split[1];
	} else {
	  $fname = $search;
	  $lname = '%';
	}
    }

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $query = "SELECT agents.*, users.username, users.lastname, regions.regionName FROM agents LEFT JOIN users ON agents.UUID = users.UUID LEFT JOIN regions ON agents.currentRegion = regions.uuid";

    if ($search != "") $query .= $this->cleanQuery(" WHERE users.username LIKE '$fname' AND users.lastname LIKE '$lname'");
    $query .= " ORDER BY users.username, users.lastname";

    if ($start || $end)	$query .= $this->cleanQuery(" LIMIT $start, $end");

    if ($result = mysql_query($query)) {
      if (mysql_numrows($result)) {
        while($row=mysql_fetch_assoc($result)) {
          $array[] = $row;
        }
      }
    }

    return $array;

    // Close the database
    mysql_close();
  }

  function getNameFromUUID($uuid) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $query = $this->cleanQuery("SELECT * FROM users WHERE UUID='$uuid'");
    $result = mysql_query($query) or die (mysql_error());

    if ($result) {
      return mysql_result($result,0,"username") . " " . mysql_result($result,0,"lastname");
    }

    // Close the database
    mysql_close(); 
  }

  public function checkSimulator($address, $port) {
    $timeout = 2;
    return @fsockopen("$address", $port, $errno, $errstr, $timeout);
  }

  public function cleanQuery($string)
  {
    if(get_magic_quotes_gpc()) $string = stripslashes($string);

    if (phpversion() >= '4.3.0')
      $string = mysql_real_escape_string($string);
    else
      $string = mysql_escape_string($string);
    return $string;
  }

  
}

?>
