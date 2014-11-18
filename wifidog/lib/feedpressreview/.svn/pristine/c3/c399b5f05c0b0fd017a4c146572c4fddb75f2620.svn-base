<?php

// +-------------------------------------------------------------------+
// | FeedPressReview                                                   |
// | =============================                                     |
// |                                                                   |
// | FeedPressReview is an aggregator designed to display the freshest |
// | items from a group of feed                                        |
// +-------------------------------------------------------------------+
// | PHP version 5 required.                                           |
// +-------------------------------------------------------------------+
// | Homepage:     http://projects.coeus.ce/FeedPressReview/           |
// +-------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or     |
// | modify it under the terms of the GNU General Public License as    |
// | published by the Free Software Foundation; either version 2 of    |
// | the License, or (at your option) any later version.               |
// |                                                                   |
// | This program is distributed in the hope that it will be useful,   |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of    |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the     |
// | GNU General Public License for more details.                      |
// |                                                                   |
// | You should have received a copy of the GNU General Public License |
// | along with this program; if not, contact:                         |
// |                                                                   |
// | Free Software Foundation           Voice:  +1-617-542-5942        |
// | 59 Temple Place - Suite 330        Fax:    +1-617-542-2652        |
// | Boston, MA  02111-1307,  USA       gnu@gnu.org                    |
// |                                                                   |
// +-------------------------------------------------------------------+

/** @file 
 * @brief A simplistic example for FeedPressReview 
 * http://projects.coeus.ce/feedpressreview/
* @author     Benoit Grégoire <bock@step.polymtl.ca> 
* @author © 2005-2006 Technologies
* @version    Subversion $Id:  $
*/

/*
 * Load required classes
 */
require_once ('../../simplepie/simplepie.inc');
require_once ('../FeedPressReview.inc');

$fpr = new FeedPressReview();
//$fpr->setConfigCacheDir(); //Set this to a directory to which you have write access
$fpr->setConfigCacheDir('/var/www/wifidog-auth/wifidog/tmp/simplepie_cache');
$fpr->setConfigAlgorithmStrength(0.5);
$fpr->setConfigMaxItemAge(3600 * 24 * 30); //A month
$fpr->setConfigFeedOrdering('REVERSE_DATE');
$fpr->setConfigFeedExpansion('FIRST_THREE');

//Pretend we last looked at the feed an hour ago
$fpr->setConfigLastDisplayed(time() - 3600);

//$fpr->addSourceFeed('http://newsrss.bbc.co.uk/rss/newsonline_world_edition/front_page/rss.xml');
$fpr->addSourceFeed('http://youtube.com/rss/global/recently_added.rss');
//$fpr->addSourceFeed('http://auth.ilesansfil.org/hotspot_status.php?format=RSS');
//$fpr->addSourceFeed('http://www.alternatives.ca/backend.php3');
$html = $fpr->getOutputHtml(10, true, "See more", "-");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Feed Press Review demo</title>


<meta http-equiv="content-type" content="text/html; charset='UTF-8'" />
<link rel="stylesheet" type="text/css" href='./feedpressreview.css' />
<script type='text/javascript' src='../feedpressreview.js' />
</head>

<body>
<?php

echo $html
?>

</body>
</html>