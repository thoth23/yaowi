<?php
include("language.php");


Class Session {

  public $lang = array();
  public $lClass;
  public $username;
  public $firstname;
  public $lastname;
  public $logged_in = false;
  public $login_message;

  function __construct($fname = NULL, $lname = NULL, $password = NULL) {
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

    // Do we need to do a login?
	if (!is_null($fname) && !is_null($lname)) {
	    $loginCheck = $this->checkPassword($fname, $lname, $password);
	    $this->logged_in = $loginCheck[0];
	    $this->login_message = $loginCheck[1];
	    $this->setVars($fname, $lname);
	}

    // Check to see if currently logged in
	


  }

  public function setVars($fname, $lname) {
    require("settings.php");
    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    // Get online user count
    $query="SELECT * FROM users WHERE username='$fname' AND lastname='$lname'";
    $result = mysql_query($query);    
    if (mysql_numrows($result)) {
	$this->firstname = mysql_result($result, 0, "username");
	$this->lastname = mysql_result($result, 0, "lastname");
	$this->username = mysql_result($result, 0, "username") . " " . mysql_result($result, 0, "lastname");
    }
  }

  public function checkPassword($firstname, $lastname, $password) {
    require("settings.php");

    $passcheck = md5(md5($password) . ":" );

    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    // Get online user count
    $query="SELECT * FROM users WHERE username='$firstname' AND lastname='$lastname'";
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

}

?>
