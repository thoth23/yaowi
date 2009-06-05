<?php
include("language.php");


Class Session {

  public $lang = array();
  public $lClass;
  public $username;
  public $firstname;
  public $lastname;
  public $uuid;
  public $logged_in = false;
  public $login_message;

  function __construct($fname = NULL, $lname = NULL, $password = NULL, $logoff = false) {
    $this->lClass = new Language();
    session_start();
    if (!is_null($_REQUEST['lang'])) {
      $this->lang = $this->lClass->getLanguageDef($_REQUEST['lang']);
      $_SESSION['lang'] = $_REQUEST['lang'];
    }
    elseif (!is_null($_SESSION['lang']))
      $this->lang = $this->lClass->getLanguageDef($_SESSION['lang']);
    else {
      // Default to english
      $_SESSION['lang'] = "en";
      $this->lang = $this->lClass->getLanguageDef('en');
    }

    if (!$logoff) {

    // Do we need to do a login?
	if (!is_null($fname) && !is_null($lname)) {
	    $loginCheck = $this->checkPassword($fname, $lname, $password);
	    $this->login_message = $loginCheck[1];
	    if ($loginCheck[0]) {	    
  	        $this->setVars($fname, $lname);
		header("location: index.php?page=" . $_REQUEST['page']);
	    }
	} else if (!is_null($_SESSION['fname']) && !is_null($_SESSION['lname'])) {
	    $this->setVars($_SESSION['fname'], $_SESSION['lname']);
	}
    } else {
	$this->logged_in = false;
	$this->firstname = NULL;
	$this->lastname = NULL;
	$this->username = NULL;
	$this->uuid = NULL;

	$_SESSION['fname'] = NULL;
	$_SESSION['lname'] = NULL;
	header("location: index.php?page=" . $_REQUEST['page']);
    }
  }

  public function setVars($fname, $lname) {
    require("settings.php");
    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    // Get online user count
    $query = "SELECT * FROM users WHERE username='" . $this->cleanQuery($fname) . "' AND lastname='" . $this->cleanQuery($lname) . "'";

    $result = mysql_query($query);    
    if (mysql_numrows($result)) {
        $this->logged_in = true;
	$this->firstname = mysql_result($result, 0, "username");
	$this->lastname = mysql_result($result, 0, "lastname");
	$this->username = mysql_result($result, 0, "username") . " " . mysql_result($result, 0, "lastname");
	$this->uuid = mysql_result($result, 0, "UUID");
	$_SESSION['fname'] = mysql_result($result, 0, "username");
	$_SESSION['lname'] = mysql_result($result, 0, "lastname");
	$_SESSION['uuid'] = mysql_result($result, 0, "UUID");
    }
  }

  public function checkPassword($firstname, $lastname, $password) {
    require("settings.php");

    $passcheck = md5(md5($password) . ":" );

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    // Get user
    $query = "SELECT * FROM users WHERE username='" . $this->cleanQuery($firstname) . "' AND lastname='" . $this->cleanQuery($lastname) . "'";

    $result = mysql_query($query);
    if (mysql_numrows($result)) {
	// We have found the user
	if ($passcheck == mysql_result($result, 0, "passwordHash")) 
		return array(true, "Login ok");
	else
		return array(false, "Password incorrect");

    } else {
	return array(false, "Username not found");
    }

    mysql_close();
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
