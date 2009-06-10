<?php
  $time = explode(' ', microtime());
  $start = $time[1] + $time[0];

  require_once("includes/session.php");  
  require_once("includes/opensim.php");  
  
  $session = new Session($_REQUEST['login_fname'], $_REQUEST['login_lname'], $_REQUEST['login_password'], $_REQUEST['action'] == 'logoff');
  $osInfo = new OpenSim();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>

    <title><?php echo $SITE_TITLE; ?> - Yaowi</title>

    <script src="<?php echo $SYSURL; ?>js/prototype.js" type="text/javascript"></script>
    <script src="<?php echo $SYSURL; ?>js/scriptaculous.js" type="text/javascript"></script>
    <script src="map/OpenLayers.js" type="text/javascript"></script>
    <script src="map/OpenLayers.js" type="text/javascript"></script>

    <?php include($TEMPLATE_PATH . "head.inc.html"); ?>
        
  </head>
  <body>
  
  <?php 
    include($TEMPLATE_PATH . "header.html"); 

    if (!is_null($_SERVER['PATH_INFO'])) {
        require_once($TEMPLATE_PATH . "wiki.php");
    } else {

        if (is_null($_REQUEST["page"])) $_REQUEST["page"] = "home";

        if (file_exists($TEMPLATE_PATH . $_REQUEST["page"] . ".html"))
	    include($TEMPLATE_PATH . $_REQUEST["page"] . ".html");
        else
  	    include($TEMPLATE_PATH . "index.html");
    }

    $time = explode(' ', microtime());
    $finish = $time[1] + $time[0];
    $total_time = round(($finish - $start), 4);
    include($TEMPLATE_PATH . "footer.html"); 

  ?>
  <div id="copyright">
    <?php 
	echo $COPYRIGHT . " :: ";
	include($TEMPLATE_PATH . "template.cfg");
	echo "Theme: <a href=\"$TEMPLATE_URL\">$TEMPLATE_NAME v$TEMPLATE_VERSION</a> - Copyright $copy $TEMPLATE_DATE - $TEMPLATE_AUTHOR";


  	require("includes/stats.php");
    ?>
  </div>
  </body>
</html>
