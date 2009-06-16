<?php
require("language.php");

Class Session {

  public $lang = array();
  public $langCode;
  public $lClass;
  public $username;
  public $firstname;
  public $lastname;
  public $uuid;
  public $logged_in = false;
  public $login_message;
  public $userIP;
  public $userCountry;

  public $userlevel;
  public $authorlevel;
  public $supportlevel;

  private $DBPrefix;
  private $SITENAME;
  private $SITEEMAIL;
  private $SITEURL;

  function __construct($fname = NULL, $lname = NULL, $password = NULL, $logoff = false) {
    require("settings.php");
    $this->DBPrefix = $Y_DB_PREFIX;
    $this->SITENAME = $SITE_TITLE;
    $this->SITEEMAIL = $SYSMAIL;
    $this->SITEURL = $SYSURL;

    $this->lClass = new Language();
    session_start();

    $this->userIP=$_SERVER['REMOTE_ADDR'];

    if (!isset($_SESSION['country'])) {
      $ccHandle = @fopen("http://api.hostip.info/country.php?ip=" . $this->userIP, 'r');
      if ($ccHandle) {
	$cCode = fread($ccHandle, 1024);
        fclose($ccHandle);
      }
      if (strlen($cCode)==2) {
	$this->userCountry = strtolower($cCode);
	$_SESSION['country'] = strtolower($cCode);
      }
      
    } else {
      $this->userCountry = $_SESSION['country'];
    }

    if (!is_null($_REQUEST['lang'])) {
      $this->lang = $this->lClass->getLanguageDef($_REQUEST['lang']);
      $_SESSION['lang'] = $_REQUEST['lang'];
      $this->langCode = $_REQUEST['lang'];
    }
    elseif (!is_null($_SESSION['lang'])) {
      $this->lang = $this->lClass->getLanguageDef($_SESSION['lang']);
      $this->langCode = $_SESSION['lang'];
    } else {

      // Are we doing the auto language?

      if ($AUTO_LANGUAGE && !is_null($this->userCountry)) {
        // Do we have the language?
        $langs = $this->lClass->getLanguages();
	if (in_array($this->userCountry, $langs)) {
	  // We have the language
          $_SESSION['lang'] = $this->userCountry;
          $this->lang = $this->lClass->getLanguageDef($this->userCountry);
          $this->langCode = $this->userCountry;
	} else {
          // If all else fails, default to language in settings
          $_SESSION['lang'] = $BASE_LANGUAGE;
          $this->lang = $this->lClass->getLanguageDef($BASE_LANGUAGE);
          $this->langCode = $BASE_LANGUAGE;
	}
      } else {
        // If all else fails, default to language in settings
        $_SESSION['lang'] = $BASE_LANGUAGE;
        $this->lang = $this->lClass->getLanguageDef($BASE_LANGUAGE);
        $this->langCode = $BASE_LANGUAGE;
      }
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

  function queryDatabase($query) {
    require("settings.php");
    // Open the Database
    mysql_connect($DB_HOST,$DB_USER,$DB_PASS) or die (mysql_error());
    @mysql_select_db($DB_NAME) or die("Unable to select database $DB_NAME");

    $ret = mysql_query($query);

    mysql_close();
    return $ret;
  }

  function queryYaowiDatabase($query) {
    require("settings.php");
    // Open the Database
    mysql_connect($Y_DB_HOST,$Y_DB_USER,$Y_DB_PASS) or die (mysql_error());
    @mysql_select_db($Y_DB_NAME) or die("Unable to select database $Y_DB_NAME");
    $ret = mysql_query($query) or die(mysql_error());

    mysql_close();

    return $ret;
  }
  
  public function setVars($fname, $lname) {

    $query = "SELECT * FROM users WHERE username='" . $this->cleanQuery($fname) . "' AND lastname='" . $this->cleanQuery($lname) . "'";

    $result = $this->queryDatabase($query);

    if (mysql_numrows($result)) {
        $this->logged_in = true;
	$this->firstname = mysql_result($result, 0, "username");
	$this->lastname = mysql_result($result, 0, "lastname");
	$this->username = mysql_result($result, 0, "username") . " " . mysql_result($result, 0, "lastname");
	$this->uuid = mysql_result($result, 0, "UUID");

	$_SESSION['fname'] = mysql_result($result, 0, "username");
	$_SESSION['lname'] = mysql_result($result, 0, "lastname");
	$_SESSION['uuid'] = mysql_result($result, 0, "UUID");
	$query = "SELECT * FROM " . $this->DBPrefix . "users WHERE uuid = '" . $this->uuid . "'";
	$res = $this->queryYaowiDatabase($query);
	if (!is_null($res) && mysql_numrows($res)>0) {
	  $this->userlevel = mysql_result($res,0,"adminLevel");
	  $this->supportlevel = mysql_result($res,0,"supportLevel");
	  $this->authorlevel = mysql_result($res,0,"authorLevel");
	}
    }
  }

  public function checkPassword($firstname, $lastname, $password) {
    $passcheck = md5(md5($password) . ":" );

    // Get user
    $query = "SELECT * FROM users WHERE username='" . $this->cleanQuery($firstname) . "' AND lastname='" . $this->cleanQuery($lastname) . "'";

    $result = $this->queryDatabase($query);
    if (mysql_numrows($result)) {
	// We have found the user
	if ($passcheck == mysql_result($result, 0, "passwordHash")) {
	    // Check to see if they are in the web interface user db
	    $query = "SELECT * FROM " . $this->DBPrefix . "users WHERE uuid = '" . mysql_result($result, 0, "UUID") . "'";
	    $res = $this->queryYaowiDatabase($query);
	    if (!$res || mysql_numrows($res)==0) {
		// User doesn't exist!
		$query = "INSERT INTO " . $this->DBPrefix . "users (uuid, created, userip, active) VALUES ('" . mysql_result($result, 0, "UUID") . "', '" . time() . "', '" . $_SERVER['REMOTE_ADDR'] . "', 1)";
		if ($this->queryYaowiDatabase($query)) {
	          return array(true, "Login ok");
		} else {
		  echo $query;
		  return array(false, "Error adding user to Yaowi database");
		}
	    } else {
	      return array(true, "Login ok");
	    }
	} else {
	    return array(false, "Password incorrect");
	}

    } else {
	return array(false, "Username not found");
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
  
  public function checkEmailFormat($email) {
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
      return false;
    }

    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
      if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
        return false;
      }
    }
    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { 
      $domain_array = explode(".", $email_array[1]);
      if (sizeof($domain_array) < 2) {
        return false;
      }
      for ($i = 0; $i < sizeof($domain_array); $i++) {
        if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
          return false;
        }
      }
    }
    return true;
  }

  public function checkEmailByDNS($email) {
    if (!is_null(PHP_OS) && stristr(PHP_OS, 'WIN') === false) {
      $email_array = explode("@", $email);
       return checkdnsrr($email_array[1], "MX");
    } else {
      return true;
    }
  }

  public function createUser($uuid, $user_fname, $user_lname, $user_pass, $user_startregion, $user_email, $user_realfname, $user_reallname, $user_dob) {
    $query = "INSERT INTO " . $this->DBPrefix . "users (uuid, email, real_firstname, real_lastname, user_dob, created, userip, active) VALUES ('" . $this->cleanQuery($uuid) . "', '" . $this->cleanQuery($user_email) . "', '" . $this->cleanQuery($user_realfname) . "', '" . $this->cleanQuery($user_reallname) . "', '" . $this->cleanQuery($user_dob) . "', '" . time() . "', '" . $_SERVER['REMOTE_ADDR'] . "', 1)"; 
    $result = $this->queryYaowiDatabase($query);
    if ($result) {
    $authcode = md5(md5(md5(time()) . $_user_fname) . $user_lname) . md5($uuid);
      $query = "INSERT INTO " . $this->DBPrefix . "authcodes (uuid, user_fname, user_lname, user_password, user_startregion, authcode, timestamp) VALUES ('" . $this->cleanQuery($uuid) . "',
'" . $this->cleanQuery($user_fname) . "', '" . $this->cleanQuery($user_lname) . "', '" . $this->cleanQuery($user_pass) . "', '" . $this->cleanQuery($user_startregion) . "', '"
. $authcode . "', '" . time() . "')";
      $result = $this->queryYaowiDatabase($query);
      if ($result) {
	$authlink = $this->SITEURL . "index.php?verify=" . $authcode;
	$to      = $user_email;
	$subject = 'Authenticate ' . $this->SITENAME . " account";
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: ' . $this->SITEEMAIL . "\r\n" .
	    'Reply-To: ' . $this->SITEEMAIL . "\r\n" .
	    'X-Mailer: PHP/' . phpversion();

	$message = '
<html>
<head>
  <title>Welcome to ' . $this->SITENAME . '</title>
</head>
<body>
  <p>Hello ' . $user_realfname . '</p>
  <p>' . str_replace("AUTH_LINK", $authlink, str_replace("SITENAME", $this->SITENAME, $this->lang['REG_VERIFY_EMAIL'])) . '</p>
</body>
</html>
';
	if (mail($to, $subject, $message, $headers)) {
	  echo "<table width=100% height=100%><tr><td align=center>" . str_replace("EMAIL_ADDRESS", $user_email, $this->lang['REG_VERIFY_REG']) . "</td></tr></table>";
	} else {
	  echo "Sorry, there has been an error - please contact the site administrator. If you are the site administrator, we recommend checking your mail logs";
	}

      }
    }
  }

}

?>
