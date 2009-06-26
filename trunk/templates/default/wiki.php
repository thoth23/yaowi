<?php
/*
*   Wiki processing file
*
*   @version: wiki.php 2009-06-03
*   @copyright (c) 2009 Jay Eames
*   @licence http://opensource.org/licenses/gpl-license.php GNU Public License
*
*   Parts of this code are based on open source work by Johannes Buchner
*   http://johbuc6.coconia.net/doku.php/mediawiki2html_machine/code
*
*
*   All date format lines as per the php date() format definition
*/

require("includes/wiki.php");
$wikiText = new Wiki();

$pageSplit = explode(":", substr($_SERVER['PATH_INFO'],1),2);
if (count($pageSplit)>1) {
  if ($pageSplit[0]=="Special") {
    $PAGE = $pageSplit[1];
  } else {
    $PAGE = substr($_SERVER['PATH_INFO'],1);
  }
} else {
  $pageSplit = explode("&", substr($_SERVER['PATH_INFO'],1),2);

  if (count($pageSplit)>1) {
    $PAGE = $pageSplit[0];
    $actions = str_replace("action=", "", $pageSplit[1]);
  } else {
    $PAGE = substr($_SERVER['PATH_INFO'],1);
  }
}
if ($PAGE=="index.php" || $PAGE=="")
  $PAGE = "Main_Page";

if (!is_null($_REQUEST['action']))
  $actions = $_REQUEST['action'];

if ($actions == $session->lang['WIKI_EDIT_SUBMIT']) {
  $wikiText->updatePage($PAGE, $_REQUEST['pageTitle'], $_REQUEST['updateText'], $session->username, $_REQUEST['updateComment']);
}

$wikiText->getPage($PAGE);


?>
<div id="wiki">
<table width=100%>
  <tr valign=top>
    <td class='wikiLinks'><?php echo $session->lang['WIKI_TITLE_NAVIGATION']; ?></td>
    <td id='wikiSpacer'>&nbsp;</td>
    <td id='wikiTopLinks'><table width=100%><tr><td>[ <?php
	echo "<a href='" . $SYSURL . "index.php/" . str_replace("Talk:", "", $PAGE) . "'>" . $session->lang['WIKI_TOPLINK_PAGE'] . "</a> |  ";
	echo "<a href='" . $SYSURL . "index.php/Talk:$PAGE'>" . $session->lang['WIKI_TOPLINK_DISCUSS'] . "</a> ]</td><td align=center>[ ";
	if ($session->logged_in) {
	    echo "<a href='" . $SYSURL . "index.php/$PAGE&action=edit'>" . $session->lang['WIKI_TOPLINK_EDIT'] . "</a> | ";
	} else {
	    echo "<a href='" . $SYSURL . "index.php/$PAGE&action=edit'>" . $session->lang['WIKI_TOPLINK_SOURCE'] . "</a> | ";
	}
	echo "<a href='" . $SYSURL . "index.php/$PAGE&action=history'>" . $session->lang['WIKI_TOPLINK_HISTORY'] . "</a>";
	if ($session->logged_in) {
	    echo " | <a href='" . $SYSURL . "index.php/$PAGE&action=delete'>" . $session->lang['WIKI_TOPLINK_DELETE'] . "</a> | ";
	    echo "<a href='" . $SYSURL . "index.php/$PAGE&action=move'>" . $session->lang['WIKI_TOPLINK_MOVE'] . "</a> | ";
            if ($session->userlevel == 5)
	        echo "<a href='" . $SYSURL . "index.php/$PAGE&action=protect'>" . $session->lang['WIKI_TOPLINK_PROTECT'] . "</a> | ";
	    echo "<a href='" . $SYSURL . "index.php/$PAGE&action=watch'>" . $session->lang['WIKI_TOPLINK_WATCH'] . "</a> ]";
	} else {
	    echo " ]";
	}
	echo "</td><td align=right width=200>";
	if ($session->logged_in) {
	    echo " &nbsp; &nbsp; [ ";
	    echo "<a href='" . $SYSURL . "index.php/Special:Watchlist'>" . $session->lang['WIKI_MY_WATCHLIST'] . "</a> | ";
	    echo "<a href='" . $SYSURL . "index.php/Special:Watchlist'>" . $session->lang['WIKI_MY_CONTRIBS'] . "</a> ]";
	}
    ?> 
    </td></tr></table>
  </tr>
  <tr valign=top>
    <td class='wikiLinks'>
      <div class='wikiNav'>
	<li><a href='<?php echo $SYSURL; ?>index.php/Main_Page'><?php echo $session->lang['WIKI_MAIN_PAGE']; ?></a></li>
	<li><a href='<?php echo $SYSURL; ?>index.php/Special:Recent_Changes'><?php echo $session->lang['WIKI_RECENT_CHANGES']; ?></a></li>
	<li><a href='<?php echo $SYSURL; ?>index.php/Special:Random'><?php echo $session->lang['WIKI_RANDOM_PAGE']; ?></a></li>
      </div>
      <br><?php echo $session->lang['WIKI_TITLE_SUPPORT']; ?>
      <div class='wikiNav'>
        <li><a href='<?php echo $SYSURL; ?>index.php/FAQ'><?php echo $session->lang['WIKI_SUPPORT_FAQ']; ?></a></li>
      </div>
      <br><?php echo $session->lang['WIKI_TITLE_SEARCH']; ?>
      <div class='wikiNav' align=center>
        <form method='post'>
	  <table>
            <tr><td align=center><input type='text' name='wikiSearch'></td></tr>
	    <tr><td align=center><input type='submit' value='<?php echo $session->lang['WIKI_TITLE_SEARCH']; ?>'></td></tr>
	  </table>
	</form>
      </div>
      <br><?php echo $session->lang['WIKI_TITLE_TOOLBOX']; ?>
      <div class='wikiNav'>
        <li><a href='<?php echo $SYSURL; ?>index.php/Special:Whatlinkshere<?php echo $_SERVER['PATH_INFO']; ?>'><?php echo $session->lang['WIKI_SP_WHAT_LINKS']; ?></a></li>
        <li><a href='<?php echo $SYSURL; ?>index.php/Special:Upload'><?php echo $session->lang['WIKI_SP_UPLOAD']; ?></a></li>
        <li><a href='<?php echo $SYSURL; ?>index.php/Special:SpecialPages'><?php echo $session->lang['WIKI_SP_SPECIAL_PAGES']; ?></a></li>
      </div>
    </td>
    <td>&nbsp;</td>
    <td>
      <div id='wikiMain'>
      <?php 
        if ($actions == $session->lang['WIKI_EDIT_PREVIEW']) echo "<h1 class='wikiTitle' style='color: red;'>Preview</h1><br><div id='wikiPreview'>";

	echo "<h1 class='wikiTitle'>" . ($actions==$session->lang['WIKI_EDIT_PREVIEW'] ? $_REQUEST['pageTitle'] : $wikiText->PageTitle) . "</h1>";
	if ($wikiText->PageExists || $actions != "") {
	  if ($actions == $session->lang['WIKI_EDIT_PREVIEW']) {
	    echo $wikiText->parseText("\n" . $_REQUEST['updateText']);
	    echo "<br><br></div><hr><b style='color: red'>" . $session->lang['WIKI_EDIT_PREV_ONLY'] . "</b><br><br>";
	  }
	  if ($actions == "edit" || $actions == $session->lang['WIKI_EDIT_PREVIEW']) {
	    echo "<h1>Edit</h1>";
	    echo "<form method='post' action='" . $SYSURL . "index.php/" . $PAGE . "'>";
	    echo "<table width=100%><tr><td>" . $session->lang['WIKI_EDIT_PAGE_TITLE'] . ": <input type='text' name='pageTitle' value='" . ($actions==$session->lang['WIKI_EDIT_PREVIEW'] ? $_REQUEST['pageTitle'] : $wikiText->PageTitle) . "' style='width: 50%;'></td></tr>";
	    echo "<tr><td>&nbsp</td></tr>";
	    echo "<tr><td><textarea style='width: 100%; height: 300px;' name='updateText'>" . ($actions==$session->lang['WIKI_EDIT_PREVIEW'] ? $_REQUEST['updateText'] : $wikiText->UnparsedText) . "</textarea></td></tr>";
	    if ($wikiText->PageExists) {
	      echo "<tr><td>" . $session->lang['WIKI_EDIT_COMMENT'] . ": <input type='text' name='updateComment' value='" . $_REQUEST['updateComment'] . "' style='width: 50%;'></td></tr>";
	    }
	    echo "<tr><td><input type='submit' name='action' value='" . $session->lang['WIKI_EDIT_PREVIEW'] . "'> &nbsp; ";
	    echo "<input type='submit' name='action' value='" . $session->lang['WIKI_EDIT_SUBMIT'] . "'> &nbsp; ";
	    echo "<a href='" . $SYSURL . "index.php/" . $PAGE . "'><input type='button' value='" . $session->lang['WIKI_EDIT_CANCEL'] . "'></a></td></tr>";
	    echo "</table></form>";
	  } else {
	    echo $wikiText->ParsedText;
	  }
	} else {
	  $Text = str_replace("[", "<a href='" . $SYSURL . "index.php/Special:Search?wikiSearch=" . $PAGE . "'>", 
		    str_replace("]", "</a>", 
		    str_replace("{", "<a href='" . $SYSURL . "index.php/" . $PAGE . "&action=edit'>",
		    str_replace("}", "</a>", $session->lang['WIKI_NO_PAGE']))));
	  $noText = explode("|", $Text);
	  echo $noText[0];
	  if (count($noText)>1 && $session->logged_in && ($session->userlevel = 5 || !$wikiText-PageProtected)) {
	    echo " " . $noText[1];
	  }
	}
      ?>
      </div>
    </td>
  </tr>
</table>
</div>
