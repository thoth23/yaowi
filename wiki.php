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

$wikiText = new Wiki("<strong>This is a test</strong>\n \n \n= Test =\n \n== Test again ==\n \n=== Test 3 ===\n \n==== Test 4====\n \n=== Test 5 ===\n \n== Test 6 ==\n \n= Test new header =");

?>

<div id="wiki">
<table>
  <tr valign=top>
    <td id='wikiLinks' rowspan=2>Test</td>
    <td id='wikiSpacer'>&nbsp;</td>
    <td id='wikiTopLinks'>Page Edit History Delete Move Protect Watch</td>
  </tr>
  <tr valign=top>
    <td>&nbsp;</td>
    <td id='wikiMain'>
      <?php echo $wikiText->ParsedText; ?>
    </td>
  </tr>
</table>
</div>
