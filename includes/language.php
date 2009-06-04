<?php

Class Language 
{
  
    public function getLanguages()
    {
        $d = dir(getcwd() . "/language/");
	$langs = array();
	while (false !== ($entry = $d->read())) {
            if($entry != '.' && $entry != '..') {
		$langs[] = $entry;
	    }
        }
        $d->close();
	return empty($langs) ? false : $langs;
    }

    public function getLanguageBar()
    {
        $d = dir(getcwd() . "/language/");
	$langs = array();
	while (false !== ($entry = $d->read())) {
            if($entry != '.' && $entry != '..') {
		$langs[] = $entry;
	    }
        }
        $d->close();

	$pageURL = 'http';
 	if ($_SERVER["HTTPS"] == "on") $pageURL .= "s";
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
	  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
	  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}

	if ($langs) {
	  $out = "<table id='languageBar'><tr>";
	  foreach ($langs as $l) {
	    $path = getcwd() . "/language/$l/$l.png";
	    if (file_exists($path)) {
		if (strpos($pageURL, "lang=")>-1) {
		    $replace = substr($pageURL, strpos($pageURL, "lang=")-1,8);
		    $pageURL = str_replace($replace, "", $pageURL);
		}
		if (substr($pageURL, -1)=="?")
		    $out .= "<td><a href='" . $pageURL . "lang=$l'><img src='language/$l/$l.png' alt='$i' border=0></a></td>";
		else if (strpos($pageURL, "?")>-1)
		    $out .= "<td><a href='$pageURL&lang=$l'><img src='language/$l/$l.png' alt='$i' border=0></a></td>";
		else
		    $out .= "<td><a href='$pageURL?lang=$l'><img src='language/$l/$l.png' alt='$i' border=0></a></td>";
	    }
	  }
	  $out .= "</tr></table>";
	} else {
	  echo "Error!";
	}
	return $out;
    }

    public function getLanguageDef($l = "en") {
        $path = getcwd() . "/language/$l/lang.cfg";
	$lang = array();

        if (file_exists($path)) {
	    include($path);
	}

	return($lang);
    }

}

?>
