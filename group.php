<?php

require 'common.php';

if (isset($_GET['group'])) {
	$group = preg_replace('@[^A-Za-z0-9.-]@', '', $_GET['group']);
} else {
	error("Missing group");
}

if (isset($_GET['format'])) {
	$format = $_GET['format'];
} else {
	// assume html
	$format = 'html';
}

if (isset($_GET['i'])) {
	$i = (int)$_GET['i'];
} else {
	$i = 0;
}

try {
	$nntpClient = new \Web\News\Nntp(NNTP_HOST);
	$overview = $nntpClient->getArticlesOverview($group, $i);
} catch (Exception $e) {
	error($e->getMessage());
}

$host = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, "UTF-8");
switch($format) {
	case 'rss':
	header("Content-type: text/xml");
    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";?>
<rss version="0.93">
 <channel> 
  <title><?php echo $host; ?>: <?php echo $group?></title>
  <link>http://<?php echo $host; ?>/group.php?group=<?php echo $group?></link>
  <description></description>
<?php  break;
case 'rdf':
header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
?>
<rdf:RDF
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
        xmlns="http://my.netscape.com/rdf/simple/0.9/">
 <channel>
  <title><?php echo $host; ?>: <?php echo $group?></title>
  <link>http://<?php echo $host; ?>/group.php?group=<?php echo $group?></link>
  <description><?php echo $group?> Newsgroup at <?php echo NNTP_HOST; ?></description>
  <language>en-US</language>
 </channel>
<?php
break;
case 'html':
default:
head($group);
navbar($group, $overview['group']['low'], $overview['group']['high'], $overview['group']['start']);
echo '  <table class="stripped" width="100%">' . "\n";
echo '   <tr>' . "\n";
echo '    <th>#</td>' . "\n";
echo '    <th>subject</th>' . "\n";
echo '    <th>author</th>' . "\n";
echo '    <th>date</th>' . "\n";
echo '    <th>lines</th>' . "\n";
echo '   </tr>' . "\n";
break;
}

# list of articles
# TODO: somehow determine the correct charset
$charset = "utf-8";

foreach ($overview['articles'] as $articleNumber => $details) {
	/*  $date = date("H:i:s M/d/y", strtotime($odate)); */
	$date822 = date("r", strtotime($details['date']));

	switch($format) {
		case 'rss':
		echo "  <item>\n";
		echo "   <link>http://$host/$group/$articleNumber</link>\n";
		echo "   <title>", format_subject($details['subject'], $charset), "</title>\n";
		echo "   <description>", htmlspecialchars(format_author($details['author'], $charset), ENT_QUOTES, "UTF-8"), "</description>\n";
		echo "   <pubDate>$date822</pubDate>\n";
		echo "  </item>\n";
		break;
		case 'rdf':
		echo " <item>\n";
		echo "  <title>", format_subject($details['subject'], $charset), "</title>\n";
		echo "  <link>http://$host/$group/$articleNumber</link>\n";
		echo "  <description>", htmlspecialchars(format_author($details['author'], $charset), ENT_QUOTES, "UTF-8"), "</description>\n";
		echo "  <pubDate>$date822</pubDate>\n";
		echo " </item>\n";
		break;
		case 'html':
		default:
		echo "   <tr>\n";
		echo "    <td><a href=\"/$group/$articleNumber\">$articleNumber</a></td>\n";
		echo "    <td><a href=\"/$group/$articleNumber\">";
		echo format_subject($details['subject'], $charset);
		echo "</a></td>\n";
		echo "    <td vcard\">".format_author($details['author'], $charset)."</td>\n";
		echo "    <td align=\"center\"><tt>" . format_date($details['date']) . "</tt></td>\n";
		echo "    <td align=\"right\">{$details['lines']}</td>\n";
		echo "   </tr>\n";
	}
}

switch ($format) {
	case 'rss':
	echo " </channel>\n</rss>\n";
	break;
	case 'rdf':
	echo "</rdf:RDF>\n";
	break;
	case 'html':
	default:
	echo "  </table>\n";
	navbar($group, $overview['group']['low'], $overview['group']['high'], $overview['group']['start']);
	foot();
}

function navbar($g, $f, $l, $i) {
	echo '  <table border="0" cellpadding="2" cellspacing="2" width="100%">' . "\n";
	echo '   <tr>' . "\n";
	echo '    <th class="nav">';
	if ($i > $f) {
		$p = max($i-20,$f);
		echo "<a href=\"/" . htmlspecialchars($g, ENT_QUOTES, "UTF-8") . "/start/$p\"><b>&laquo; previous</b></a>";
	} else {
		echo "&nbsp;";
	}
	echo '</th>' . "\n";
	$j = min($i + 20, $l);
	$c = $l - $f + 1;
	echo '    <th align="center">'.htmlspecialchars($g, ENT_QUOTES, "UTF-8")." ($i-$j of $c)</th>\n";
	echo '    <th align="right" class="nav">';
	if ($i+20 <= $l) {
		$n = min($i + 20, $l - 19);
		echo "<a href=\"/" . htmlspecialchars($g, ENT_QUOTES, "UTF-8") . "/start/$n\"><b>next &raquo;</b></a>";
	}
	else {
		echo "&nbsp;";
	}
	echo '</th>' . "\n";
	echo '   </tr>' . "\n";
	echo '  </table>' . "\n";
}
