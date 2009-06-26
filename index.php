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

    <script src="<?php echo $SYSURL; ?>js/tiny_mce/tiny_mce.js" type="text/javascript"></script>

    <script type="text/javascript">
        tinyMCE.init({
                // General options
                mode : "exact",
                theme : "advanced",
		elements : "tinyMCE",
                plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

                // Theme options
                theme_advanced_buttons1 : "bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,fontsizeselect,|,forecolor,backcolor",
                theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview",
                theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,charmap,emotions,iespell,media,advhr,|,fullscreen",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_resizing : true,

                // Drop lists for link/image/media/template dialogs
                template_external_list_url : "lists/template_list.js",
                external_link_list_url : "lists/link_list.js",
                external_image_list_url : "includes/imagelist.php",
                media_external_list_url : "lists/media_list.js",

                // Replace values for the template plugin
                template_replace_values : {
                        username : "Some User",
                        staffid : "991234"
                }
        });
    </script>

    <?php include($TEMPLATE_PATH . "head.inc.html"); ?>
        
  </head>
  <body<?php if ($session->login_message!="") echo " onLoad=\"alert('" . $session->login_message . "')\""; ?>>
  <?php 
    include($TEMPLATE_PATH . "header.html"); 

    if (!is_null($_SERVER['PATH_INFO'])) {
        require_once($TEMPLATE_PATH . "wiki.php");
    } elseif (!$session->logged_in && !is_null($_REQUEST['verify']) && $_REQUEST['verify']!="") {
	echo "<table width=100% height=100%><tr><td align=center>" . $session->verifyAccount($_REQUEST['verify']) . "</td></tr></table>";
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
  <?php
    if (trim($SYS_GOOGLE_WPI) != "") {
  ?>
    <script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script type="text/javascript">
	try {
	  var pageTracker = _gat._getTracker("<?php echo $SYS_GOOGLE_WPI; ?>");
	  pageTracker._trackPageview();
	} catch(err) {}
    </script>
  <?php
    }
  ?>
  </body>
</html>
