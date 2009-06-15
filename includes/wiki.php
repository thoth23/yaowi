<?

Class Wiki
{
  public $PageTitle;
  public $PagePath;
  public $TableOfContents;
  public $ParsedText;
  public $Error;
  public $PageProtected = false;
  public $PageLastEdited;
  public $PageLastEditor;
  public $PageLastEditComment;
  public $PageCounter;
  public $PageExists = false;

  function __construct() {

  }
  
  public function getPage($page) {
    include("settings.php");
    if (!is_null($page) && $page != "") {

      mysql_connect($Y_DB_HOST,$Y_DB_USER,$Y_DB_PASS) or die (mysql_error());
      @mysql_select_db($Y_DB_NAME) or die("Unable to select database $DB_NAME");

      $query = "SELECT * FROM " . $Y_DB_PREFIX . "wiki_pages WHERE page_path = '" . $this->cleanQuery($page) . "'";
      $result = mysql_query($query);
      if ($result) {
	if (mysql_numrows($result)) {
	  $this->PagePath 		= mysql_result($result,0, "page_path");
	  $this->PageTitle 		= mysql_result($result,0, "page_title");
	  $this->PageProtected 		= mysql_result($result,0, "page_is_protected");
	  $this->PageLastEdited 	= mysql_result($result,0, "page_last_edited");
	  $this->PageLastEditor 	= mysql_result($result,0, "page_last_editor");
	  $this->PageLastEditComment 	= mysql_result($result,0, "page_edit_comment");

	  $this->parseText(mysql_result($result,0, page_text));
	  $this->pageExists 		= true;

	} else {
	  $this->PagePath	= $page;
	  $this->PageTitle	= str_replace("_", " ", $page);
	}
      } else {
	$this->Error = "Sorry, there has been an error (" . mysql_error() . ")";
      }

    } else {
      $this->Error = "Sorry, there has been an error (NULL page request)";
    }
  }

  public function parseText($text) {
    $html = $text;
    $html = html_entity_decode($html);
    $html = str_replace('&ndash;','-',$html);
    $html = str_replace('&quot;','"',$html);
    $html = preg_replace('/\&amp;(nbsp);/','&${1};',$html);
 
	
    $html = str_replace('{{PAGENAME}}',$title,$html);
	
    // Table
    $html = $this->convertTables($html);
	
    $html = $this->simpleText($html);

    $this->TableOfContents = $this->parseTOC($html);
    $tocSplit = strpos($html, "<a name=");
    if ($tocSplit!==false)
    {
	$html = substr($html, 0, $tocSplit) . $this->TableOfContents . substr($html, $tocSplit);
    }
    $this->ParsedText = $html;

  }

  function parseTOC($html) {
    $toc = array();
    $startPos;
    $curPos = strpos($html, "<a name='");
    while ($curPos !== false) {
      $startPos = $curPos + 1;
      $endPos = strpos($html, "'></a>", $startPos);
      $toc[] = array ("level" => substr($html,$endPos+8,1), "link" => substr($html, $startPos+8, ($endPos-$startPos)-8));
      $curPos = strpos($html, "<a name='", $startPos);
    }
    $table = "<table id='toc' cellspacing=0 cellpadding=0><tr><td><div id='tocTitle'><h2>Contents</h2></div><ul>";
    $tocLevel = array(0,0,0,0,0,0);
    $lastLevel = $toc[0]['level']-1;
    $curLevel = 0;
    foreach ($toc as $entry) {
	if ($entry['level'] < $lastLevel) {
	  $dif = $lastLevel - $entry['level'];
	  if ($curLevel - $dif <=0) {
	    $tocTemp = $tocLevel[0];
	    $tocLevel = array($tocTemp+1,0,0,0,0,0);
	  } else {
	    $curLevel -= $dif;
	    $tocLevel[$curLevel-1]++;
	    for ($i=$curLevel; $i < 6; $i++)
	      $tocLevel[$i] = 0;
	  }
	} elseif ($entry['level'] == $lastLevel) {
	  $tocLevel[$curLevel]++;
	} else {
	  $tocLevel[$curLevel++]++;
	}


	$tocLevelOut = "";
	$tocLevelCSS = 0;
	foreach ($tocLevel as $lev) {
	  if ($lev!=0) { 
	    $tocLevelCSS++;
	    $tocLevelOut .= "$lev.";
	  }
	}
	$tocLevelOut = substr($tocLevelOut,0,-1);
	if ($lastLevel != $entry['level']) $table .= "</ul>\n<ul class='toclevel-$tocLevelCSS'>";
	$table .= "<li class='toclevel-$tocLevelCSS'><a href='#" . $entry['link'] . "'>$tocLevelOut " . $entry['link'] . "</a></li>";

	$lastLevel = $entry['level'];
    }
    $table .= "</ul></td></tr></table>";

    return $table;
  }

  function simpleText($html){
 
    $html = str_replace('&ndash;','-',$html);
    $html = str_replace('&quot;','"',$html);
    $html = preg_replace('/\&amp;(nbsp);/','&${1};',$html);
 
    //formatting
    // bold
    $html = preg_replace('/\'\'\'([^\n\']+)\'\'\'/','<strong>${1}</strong>',$html);
    // emphasized
    $html = preg_replace('/\'\'([^\'\n]+)\'\'?/','<em>${1}</em>',$html);
    //interwiki links
    $html = preg_replace_callback('/\[\[([^\|\n\]:]+)[\|]([^\]]+)\]\]/',array($this,'helper_interwikilinks'),$html);
    // without text
    $html = preg_replace_callback('/\[\[([^\|\n\]:]+)\]\]/',array($this,'helper_interwikilinks'),$html);
    $html = preg_replace('/{{([^\|\n\}]+)([\|]?([^\}]+))+\}\}/','Interwiki: ${1} &raquo; ${3}',$html);

    // categories
    $html = preg_replace('/\[\[([^\|\n\]]{2})([\:]([^\]]+))?\]\]/','Translation: ${1} &raquo; ${3}',$html);
    $html = preg_replace('/\[\[([^\|\n\]]+)([\:]([^\]]+))?\]\]/','Category: ${1} - ${2}',$html);

    // image
    $html = preg_replace('/\[\[([^\|\n\]]+)([\|]([^\]]+))+\]\]/','Image: ${0}+${1}+${2}+${3}',$html);
	
    //links
    $html = preg_replace_callback('/\[([^\[\]\|\n\': ]+)\]/',array($this,'helper_externlinks'),$html);
    // with text
    $html = preg_replace_callback('/\[([^\[\]\|\n\' ]+)[\| ]([^\]\']+)\]/',array($this,'helper_externlinks'),$html);
	
    // allowed tags
    $html = preg_replace('/&lt;(\/?)(small|sup|sub|u)&gt;/','<${1}${2}>',$html);
	
    $html = preg_replace('/\n*&lt;br *\/?&gt;\n*/',"\n",$html);
    $html = preg_replace('/&lt;(\/?)(math|pre|code|nowiki)&gt;/','<${1}pre>',$html);
    $html = preg_replace('/&lt;!--/','<!--',$html);
    $html = preg_replace('/--&gt;/',' -->',$html);
 
    // headings
    for($i=7;$i>0;$i--){
	// $html = preg_replace('/\n+[=]{'.$i.'}([^=]+)[=]{'.$i.'}\n*/','<h'.$i.'>${1}</h'.$i.'>', $html );
	$html = preg_replace('/\n+[=]{'.$i.'}([^=]+)[=]{'.$i.'}\n*/','<a name=\'${1}\'></a><h'.$i.'>${1}</h'.$i.'>', $html );

    }
    // Ugly hack to sort the link
    $html = str_replace("<a name=' ", "<a name='", str_replace(" '></a><h", "'></a><h",$html)); 
	
    //lists
    $html = preg_replace('/(\n[ ]*[^#* ][^\n]*)\n(([ ]*[*]([^\n]*)\n)+)/', '${1}<ul>'."\n".'${2}'.'</ul>'."\n", $html);
    $html = preg_replace('/(\n[ ]*[^#* ][^\n]*)\n(([ ]*[#]([^\n]*)\n)+)/', '${1}<ol>'."\n".'${2}'.'</ol>'."\n",	$html);
    $html = preg_replace('/\n[ ]*[\*#]+([^\n]*)/','<li>${1}</li>',$html);	
    $html = preg_replace('/----/','<hr />',$html);
 
    // line breaks
    $html = preg_replace('/[\n\r]{4}/',"<br/><br/>",$html);
    $html = preg_replace('/[\n\r]{2}/',"<br/>",$html);
	
    $html = preg_replace('/[>]<br\/>[<]/',"><",$html);
 
    return $html;
  }

  function helper_externlinks($matches){
    $target = $matches[1];
    $text = empty($matches[2])?$matches[1]:$matches[2];
    return '<a href="'.$target.'">'.$text.'</a>';
  }

  function helper_interwikilinks($matches){
    $target = $matches[1];
    $text = empty($matches[2])?$matches[1]:$matches[2];
    $class=" class=\"dunno\" ";
    return '<a '.$class.' href="?page='.$target.'">'.$text.'</a>';
  }

  function convertTables($text){
    $lines = explode("\n",$text);
    $innertable = 0;
    $innertabledata = array();
    foreach($lines as $line){
      //echo "<pre>".++$i.": ".htmlspecialchars($line)."</pre>";
      $line = str_replace("position:relative","",$line);
      $line = str_replace("position:absolute","",$line);
      if(substr($line,0,2) == '{|'){
	// inner table
	$innertable++;
      }
      $innertabledata[$innertable] .= $line . "\n";
      if($innertable){
	// we're inside
	if(substr($line,0,2) == '|}'){
	    $innertableconverted = convertTable($innertabledata[$innertable]);
	    $innertabledata[$innertable] = "";
	    $innertable--;
	    $innertabledata[$innertable] .= $innertableconverted."\n";
	}
      }
    }
    return $innertabledata[0];

  }

  function convertTable($intext){
    $text = $intext;
    $lines = explode("\n",$text);
    $intable = false;
	
    //var_dump($lines);
    foreach ($lines as $line) {
      $line = trim($line);
      if(substr($line,0,1) == '{'){
	//begin of the table
	$stuff = explode('| ',substr($line,1),2);
	$tableopen = true;
	$table = "<table ".$stuff[0].">\n";
      } else if (substr($line,0,1) == '|'){
	// table related
	$line = substr($line,1);
	if(substr($line,0,5) == '-----'){
	  // row break
	  if($thopen) $table .="</th>\n";
	  if($tdopen) $table .="</td>\n";
	  if($rowopen) $table .="\t</tr>\n";
	  $table .= "\t<tr>\n";
	  $rowopen = true;
	  $tdopen = false;
	  $thopen = false;
	} else if (substr($line,0,1) == '}') {
	  // table end
	  break;
	} else {
	  // td
	  $stuff = explode('| ',$line,2);
	  if ($tdopen) $table .="</td>\n";
	  if (count($stuff)==1)	$table .= "\t\t<td>".simpleText($stuff[0]);
	  else $table .= "\t\t<td ".$stuff[0].">".simpleText($stuff[1]);
	  $tdopen = true;
	}
      } else if(substr($line,0,1) == '!') {
	// th
	$stuff = explode('| ',substr($line,1),2);
	if ($thopen) $table .="</th>\n";
	if (count($stuff)==1) $table .= "\t\t<th>" . simpleText($stuff[0]);
	else $table .= "\t\t<th ".$stuff[0] . ">" . simpleText($stuff[1]);
	$thopen = true;
      }else{
	// plain text
	$table .= simpleText($line) ."\n";
      }
      //echo "<pre>".++$i.": ".htmlspecialchars($line)."</pre>";
      //echo "<p>Table so far: <pre>".htmlspecialchars($table)."</pre></p>";
    }

    if($thopen)	$table .="</th>\n";
    if($tdopen)	$table .="</td>\n";
    if($rowopen) $table .="\t</tr>\n";
    if($tableopen) $table .="</table>\n";
    //echo "<hr />";
    //echo "<p>Table at the end: <pre>".htmlspecialchars($table)."</pre></p>";
    //echo $table;	
    return $table;
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
