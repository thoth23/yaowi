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
  public $null_key = "00000000-0000-0000-0000-000000000000";

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

  function getRegionList($search="", $start=0, $end=0, $owner=NULL) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $query = "SELECT regions.*, users.username, users.lastname FROM regions LEFT JOIN users ON regions.owner_uuid = users.UUID";
    if (!is_null($search) && $search != "") $query .= " WHERE regions.regionName LIKE '" . $this->cleanQuery($search) . "'";
    if (!is_null($owner) && $owner!="") {
	if (strpos($query, "WHERE") === false)
	    $query .= " WHERE";
	else
	    $query .= " AND";
	$query .= " regions.owner_uuid = '" . $this->cleanQuery($owner) . "'";
    }
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

  function getFriendsList($uuid) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $query = "SELECT userfriends.*, agents.*, users.username, users.lastname, regions.regionName FROM userfriends LEFT JOIN agents ON friendID = agents.UUID LEFT JOIN users ON agents.UUID = users.UUID LEFT JOIN regions ON agents.currentRegion = regions.uuid WHERE userfriends.ownerID ='" . $this->cleanQuery($uuid) . "' ORDER BY agents.agentOnline";
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

    $query = "SELECT * FROM users WHERE UUID='" . $this->cleanQuery($uuid) . "'";
    $result = mysql_query($query) or die (mysql_error());

    if ($result) {
      return mysql_result($result,0,"username") . " " . mysql_result($result,0,"lastname");
    }

    // Close the database
    mysql_close(); 
  }

  function checkLocation($x, $y) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $query = "SELECT * FROM regions WHERE locX ='" . $this->cleanQuery($x) . "' AND locY = '" . $this->cleanQuery($y) . "'";
    $result = mysql_query($query) or die (mysql_error());

    if ($result) {
      return mysql_numrows($result);
    }

    // Close the database
    mysql_close(); 
  }

  public function checkSimulator($address, $port) {
    $timeout = 2;
    return @fsockopen("$address", $port, $errno, $errstr, $timeout);
  }
 
  public function gridStatusClass() {
    return "Online";
  }

  public function gridStatus() {
    return "ST_GRID_ONLINE";
  }

  public function getInventoryXML($uuid) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $xml = "<inventory>";

    // Get root folder
    $query = "SELECT * FROM inventoryfolders WHERE agentID ='$uuid' AND parentFolderID = '" . $this->null_key . "'";  ;

    if ($result = mysql_query($query)) {
      if (mysql_numrows($result)) {
        while($row=mysql_fetch_assoc($result)) {
	  $xml .="<folder><name>" . $row['folderName'] . "</name>";
	  $temp = $this->getSubFoldersRecursiveXML($row['folderID']);
	  if (!is_null($temp))
	    $xml .= $temp;
        }
      }
    }
    $xml .= "</folder></inventory>";
    return $this->formatXMLString($xml);
  }

  public function getSubFoldersRecursiveXML($folderUUID) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $query = "SELECT * FROM inventoryfolders WHERE parentFolderID = '$folderUUID'";
    $xml = "";

    if ($result = mysql_query($query)) {
      while ($row = mysql_fetch_assoc($result)) {
	$xml .= "<folder><name>" . $row['folderName'] . "</name>";

	$xml .= $this->getFolderInventoryXML($row['folderID']);

	$temp = $this->getSubFoldersRecursiveXML($row['folderID']);
	if (!is_null($temp))
	  $xml .= $this->getSubFoldersRecursiveXML($row['folderID']);

	$xml .= "</folder>";

      }
    }

    return $xml;
  }

  public function getFolderInventoryXML($folderUUID) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $xml = "";

    $query = "SELECT * FROM inventoryitems WHERE parentFolderID ='$folderUUID'";
    if ($result2 = mysql_query($query)) {
      if (mysql_numrows($result2)) {
        for ($i=0; $i < mysql_numrows($result2); $i++) {
          $xml .= "<item><name>" . mysql_result($result2, $i, "inventoryName") . "</name><uuid>"
               . mysql_result($result2, $i, "assetID") . "</uuid><type>"
               . $this->getInventoryType(mysql_result($result2, $i, "assetType")) . "</type></item>";
        }
      }
    }
    
    return $xml;

    mysql_close();
  }

  public function formatXMLString($xml) {  
  
    // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
    $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);
  
    // now indent the tags
    $token      = strtok($xml, "\n");
    $result     = ''; // holds formatted version as it is built
    $pad        = 0; // initial indent
    $matches    = array(); // returns from preg_matches()
  
    // scan each line and adjust indent based on opening/closing tags
    while ($token !== false) : 
  
      // test for the various tag states
    
      // 1. open and closing tags on same line - no change
      if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) : 
        $indent=0;
      // 2. closing tag - outdent now
      elseif (preg_match('/^<\/\w/', $token, $matches)) :
        $pad--;
      // 3. opening tag - don't pad this one, only subsequent tags
      elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
        $indent=1;
      // 4. no indentation needed
      else :
        $indent = 0; 
      endif;
    
      // pad the line with the required number of leading spaces
      $line    = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);
      $result .= $line . "\n"; // add to the cumulative result, with linefeed
      $token   = strtok("\n"); // get the next token
      $pad    += $indent; // update the pad size for subsequent lines    
    endwhile; 
  
    return $result;
  }

  public function getInventoryFoldersHirachical($uuid) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $array = array();

    $query = "SELECT folderID AS id, parentFolderID AS parent_id, folderName FROM inventoryfolders WHERE agentID ='$uuid'";
    if ($result = mysql_query($query)) {
      if (mysql_numrows($result)) {
        while($row=mysql_fetch_assoc($result)) {
	  $array[] = $row;
        }
      }
    }

    return $array;
  }

  public function getFolderContents($folderUUID) {
    require("settings.php");

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $array = array();

    $query = "SELECT * FROM inventoryitems WHERE parentFolderID = '$folderUUID'";  ;

    if ($result = mysql_query($query)) {
      while ($row = mysql_fetch_assoc($result)) {
	$array[] = $row;
      }
    }

    return $array;

  }

  public function getInventoryType($type) {
    switch ($type) {
      case 0:
	return "Texture";
	break;
      case 2:
	return "Calling card";
	break;
      case 6:
	return "Primative";
	break;
      case 10:
	return "Script";
	break;
      case 18:
	return "Clothing";
	break;
      case 19:
	return "Animation";
	break;
      case 20:
	return "Gesture";
	break;
      default:
	return "Unknown";
    }
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

  public function createUUID($prefix = '')

  {
    $chars = md5(uniqid(mt_rand(), true));
    $uuid  = substr($chars,0,8) . '-';
    $uuid .= substr($chars,8,4) . '-';
    $uuid .= substr($chars,12,4) . '-';
    $uuid .= substr($chars,16,4) . '-';
    $uuid .= substr($chars,20,12);
    return $prefix . $uuid;

  }
  
}

?>
