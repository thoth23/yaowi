<?php

include_once("settings.php");

function getBrowser($userAgent) {
  // Create list of browsers with browser name as array key and user agent as value. 
        $browsers = array(
                'Opera' => 'Opera',
                'Mozilla Firefox 3'=> '(Firebird/3)|(Firefox/3)', 
                'Mozilla Firefox 2'=> '(Firebird/1)|(Firefox/2)',
                'Mozilla Firefox 1'=> '(Firebird/1)|(Firefox/1)',
                'Google Chrome' => 'Chrome',
                'Galeon' => 'Galeon',
                'Mozilla'=>'Gecko',
                'MyIE'=>'MyIE',
                'Lynx' => 'Lynx',
                'Netscape' => '(Mozilla/4\.75)|(Netscape6)|(Mozilla/4\.08)|(Mozilla/4\.5)|(Mozilla/4\.6)|(Mozilla/4\.79)',
                'Konqueror'=>'Konqueror',
                'SearchBot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp/cat)|(msnbot)|(ia_archiver)',
                'Internet Explorer 8' => '(MSIE 8\.[0-9]+)',
                'Internet Explorer 7' => '(MSIE 7\.[0-9]+)',
                'Internet Explorer 6' => '(MSIE 6\.[0-9]+)',
                'Internet Explorer 5' => '(MSIE 5\.[0-9]+)',
                'Internet Explorer 4' => '(MSIE 4\.[0-9]+)',
        );

        foreach($browsers as $browser=>$pattern) { // Loop through $browsers array
    // Use regular expressions to check browser type
                if(eregi($pattern, $userAgent)) { // Check if a value in $browsers array matches current user agent.
                        return $browser; // Browser was matched so return $browsers key
                }
        }
        return 'Unknown'; // Cannot find browser so return Unknown
}

function getOS($userAgent) {
        $systems = array(
                'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)|(Windows NT 5.01)',
                'Windows XP' => 'Windows NT 5.1',
                'Windows XP 64Bit' => 'Windows NT 5.2',
                'Windows Vista' => 'Windows NT 6.0',
                'Windows 7' => 'Windows NT 7.0',
                'Windows' => 'Win',
                'Macintosh' => 'Mac',
                'Linux' => 'Linux',
                'FreeBSD' => 'FreeBSD',
                'Sun OS' => 'SunOS',
                'IRIX' => 'IRIX',
                'BeOS' => 'BeOS',
                'Linux' => '(Linux)|(Lynx)',
                'Unix' => 'nix'
        );

        foreach($systems as $system=>$pattern) {
                if(eregi($pattern, $userAgent)) {
                        return $system;
                }
        }
        return 'Unknown';
}

if ($SYS_STATS) {
  $user = array(
                'OS' => getOS($_SERVER['HTTP_USER_AGENT']),
                'Browser' => getBrowser($_SERVER['HTTP_USER_AGENT']), 
                'IP' => $_SERVER['REMOTE_ADDR'], 
                'Page' => $_SERVER['SCRIPT_NAME'] . "?" . $_SERVER['QUERY_STRING'], 
                'Referer' => $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : "None", 
                'Time' => $_SERVER['REQUEST_TIME'], 
                'User' => $session->username ? $session->username : "Unknown",
		'Gen Time' => $total_time);
}

?>
