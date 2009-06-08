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

//$wikiText = new Wiki("<strong>This is a test</strong>\n \n \n= Test =\n \n== Test again ==\n \n=== Test 3 ===\n \n==== Test 4====\n \n=== Test 5 ===\n \n== Test 6 ==\n \n= Test new header =");
$wikiText = new Wiki("");
$pageSplit = explode(":", substr($_SERVER['PATH_INFO'],1),2);
if (count($pageSplit)>1) {
  if ($pageSplit[0]=="Special" || $pageSplit[0]=="Talk") {
    $PAGE = $pageSplit[1];
  } else {
    $PAGE = substr($_SERVER['PATH_INFO'],1);
  }
} else {
  $PAGE = substr($_SERVER['PATH_INFO'],1);
}
?>

<div id="wiki">
<table width=100%>
  <tr valign=top>
    <td class='wikiLinks'>Navigation</td>
    <td id='wikiSpacer'>&nbsp;</td>
    <td id='wikiTopLinks'><table width=100%><tr><td>[ <?php
	echo "<a href='" . $SYSURL . "index.php/" . $PAGE . "'>" . $session->lang['WIKI_TOPLINK_PAGE'] . "</a> |  ";
	echo "<a href='" . $SYSURL . "index.php/Talk:$PAGE'>" . $session->lang['WIKI_TOPLINK_DISCUSS'] . "</a> ]</td><td align=center>[ ";
	if ($session->logged_in) {
	    echo "<a href='" . $SYSURL . "index.php?title=$PAGE&action=edit'>" . $session->lang['WIKI_TOPLINK_EDIT'] . "</a> | ";
	} else {
	    echo "<a href='" . $SYSURL . "index.php?title=$PAGE&action=edit'>" . $session->lang['WIKI_TOPLINK_SOURCE'] . "</a> | ";
	}
	echo "<a href='" . $SYSURL . "index.php?title=$PAGE&action=history'>" . $session->lang['WIKI_TOPLINK_HISTORY'] . "</a>";
	if ($session->logged_in) {
	    echo " | <a href='" . $SYSURL . "index.php?title=$PAGE&action=delete'>" . $session->lang['WIKI_TOPLINK_DELETE'] . "</a> | ";
	    echo "<a href='" . $SYSURL . "index.php?title=$PAGE&action=move'>" . $session->lang['WIKI_TOPLINK_MOVE'] . " | </a>";
            if ($session->userlevel == 5)
	        echo "<a href='" . $SYSURL . "index.php?title=" . substr($_SERVER['PATH_INFO'],1) . "&action=protect'>" . $session->lang['WIKI_TOPLINK_PROTECT'] . "</a> | ";
	    echo "<a href='" . $SYSURL . "index.php?title=" . substr($_SERVER['PATH_INFO'],1) . "&action=watch'>" . $session->lang['WIKI_TOPLINK_WATCH'] . "</a> ]";
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
	<li><a href='<?php echo $SYSURL; ?>index.php/Special:Recentchanges'><?php echo $session->lang['WIKI_RECENT_CHANGES']; ?></a></li>
	<li><a href='<?php echo $SYSURL; ?>index.php/Special:Random'><?php echo $session->lang['WIKI_RANDOM_PAGE']; ?></a></li>
      </div>
    </td>
    <td>&nbsp;</td>
    <td id='wikiMain'>
      <?php 
	echo "<h1>" . str_replace("_", " ", substr($_SERVER['PATH_INFO'],1)) . "</h1>";
	echo $wikiText->ParsedText; 
      ?>
    </td>
  </tr>
</table>
</div>
