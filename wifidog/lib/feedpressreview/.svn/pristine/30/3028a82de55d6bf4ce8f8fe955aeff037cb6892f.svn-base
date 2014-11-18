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
// | Homepage:     http://projects.coeus.ca/feedpressreview/           |
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
 * @brief Main include file for FeedPressReview
 * http://projects.coeus.ca/feedpressreview/
 * @author     Benoit Grégoire <bock@step.polymtl.ca> © 2005-2006 Technologies
 * Coeus inc.
 * @version    Subversion $Id:  $
 */

/** @mainpage
 FeedPressReview is a different kind of Feed (RSS or Atom) aggregator.
 It was designed to emulate how humans who have the task of picking "fresh" items from a bunch of sources.
 It has been used to replace humans doing tech watch with many users saying they couldn't tell the difference!

 You can download the library from svn:

 svn co http://projects.coeus.ca/svn/feedpressreview/trunk feedpressreview

 You welcome to use FeedPressReview as a normal aggregator.
 You will get a few nice interface trick beyond most plain aggregator, such as collapsible feed and criteria based feed expansion.
 However, using it as a classic aggregator is wasting most of it's abilities!

 FeedPressReview was designed to pick fresh items out of a pool of feeds.  It models a human's thought process in the following way:
 <ul>
 <li>Assuming all feed are equally "interesting", an item that was published
 yesterday by a feed that only publishes once a day is "fresher" than an item
 publised yesterday by a feed that publishes everyday.  As such, it should stay
 displayed longer.  That "bonus" is adjusted by the setConfigAlgorithmStrength()
 method.
 </li>
 <li>Not all feed are equally interesting (obviously).
 Some are more interesting, or more authoritative than others.
 You can reflect this by adjusting the $bias parameter of the addSourceFeed() method.</li>
 </li>
 </ul>
 The best way to find the right configuration values is to add all your feeds to the aggregator, and then play with the values untill it picks about what you would have picked.
 You will then find that as the feed change, it will probably pick just about what you would have.

 Pathological case
 <ul>
 <li>Feeds whose publication interval is much shorter than how often the server generates the XML or your cache it
 (ex: youtube's recent videos, that publishes several times per minute but you may only get once every 30 minutes)
 will just about never show up, because RssPressReview will conclude that it has no fresh items
 (as it should have had dozens in the last 30 minutes, but has none).
 To mitigate the problem, when doing it's internal calculations, FeedPressReview will shift all the dates (for comparing purpose) by the cache age.
 However, this will not help with caching on the remote server.  Maybe we could eventually do additional compensation if the
 first item more that three time the twice the average publication interval for the feed from now, and the average publication interval is very small
 (less than an hour for example, to avoid making that feeds that are "on vacation" will look "fresher" than they should).
 </li>

 </ul>
 */
/** @brief Slightly extended version of SimplePie.
 *
 * You should read the public member documentation, but for the rest refer to
 * SimplePie documentation at http://simplepie.org/docs/ */
class FprSimplePie
/* extends SimplePie This crashes SimplePie and wifidog for some reason, but it doesn't really matter as we are only using properties */ {
/** How old (in seconds) is the cache */
public $fpr_cache_age;

/** Same as fpr_real_publication_interval (if available), otherwise the the assumed publication interval for the feed, as set by the $estimatedPublicationInterval parameter of addSourceFeed() */
public $fpr_computed_publication_interval;
/** If the feed's items all have publication date, the computed publication interval (average time, in seconds, between items) */
public $fpr_real_publication_interval;
/** Order in which the feed was added by addSourceFeed */
public $fpr_feed_order;
} // End FprSimplePie

/** @brief Slightly extended version of SimplePie
 *  * You should read the public member documentation, but for the rest refer to
 * SimplePie documentation at http://simplepie.org/docs/ */
class FprSimplePie_Item /* extends SimplePie_Item  This crashes SimplePie and wifidog for some reason, but it doesn't really matter as we are only using properties */ {
    /** The real date the item was published, as processed from the RSS */
    public $fpr_realphpdate;
    /**  Same as $fpr_realphpdate if available.  Otherwise, the date of the item as estimated by using the publication interval given as $estimatedPublicationInterval to FeedPressReview::addSourceFeed().*/
    public $fpr_computed_date;
    /** The date as adjusted by the algorithm for freshness calculation purposes.  Mostly available for debugging */
    public $fpr_adjusted_date;
    /** Should this item be expanded, according to the criteria given to FeedPressReview::setConfigFeedExpansion() */
    public $fpr_should_be_expanded;
    /** It this item more recent that the last time the user watched the feed, as given to FeedPressReview::setConfigLastDisplayed() */
    public $fpr_newer_than_last_display;
} // End FprSimplePie_Item

/** @brief The main FeedPressReview class.
 Note that this class will obey gettext (including plural forms) and locale settings for localization.
 * @author     Benoit Gégoire <bock@step.polymtl.ca> © 2005-2006 Technologies Coeus inc.
 */
class FeedPressReview {
    /** FeedPressReview version */
    const VERSION = '2.0.2';
    /**
     * Define default publication interval to one item per day
     */
    const DEFAULT_PUBLICATION_INTERVAL = 86400;
    private $_rssSources; /**<
    $_rssSources is an array of arrays, each of which must contain:
    );*/

    private $_algorithmStrengthModifier = 0.75;
    private $_maxItemAge = null;
    private $_configFeedExpansion = null;
    private $_configLastDisplayed = null;
    private $_configStripHtmlTagArray = null;
    private $_cacheDir = null;
    private $_expandItemString = null;
    private $_collapseItemString = null;
    private static $_staticDoneHeader = false;
    /** Constructor.  Note that this class is affected by gettext and setlocale settings. */
    function __construct() {
        $this->setConfigFeedExpansion();
        $this->setConfigFeedOrdering();
        $this->setConfigStripHtmlTags();

    }

    /** @brief Allow you to select the directory where SimplePie will write it's cache files.
     *  The  directory must be writeable by the web server user If you do not
     * set it, the class will try to put it in a "cache" directory in the
     * curently executing script's current directory. */
    function setConfigCacheDir($path) {
        $this->_cacheDir = $path;
    }

    /** @brief Set the how much bonus feeds that do not publish as often get over feed that publish more often.
     * @param $algorithmStrength A positive float, defaults to 0.75.  Typical range is between 0 and 1.
     *  At 0, you have a normal RSS aggregator, meaning the n most recent entries picked from all feeds
     *  will be displayed. 1 is usually as high as you'll want to go.  Assuming that all
     * feeds have an homogenous internal distribution (say one publishes exactly one entry a day, the
     * other one entry every two days, and the third one entry every three days), and you ask for 15 entries,
     * there will be 5 of each.  While that may not sound usefull, it still is, as the feed's distribution is
     * usually not homogenous.  Most people will probably be happy with values between 0.5 and 1
     * @return true if successfull, false if $algorithmStrength < 0 */
    function setConfigAlgorithmStrength($algorithmStrength = 0.75) {
        $retval = false;
        if ($algorithmStrength >= 0) {
            $this->_algorithmStrengthModifier = $algorithmStrength;
            $retval = true;
        }
        return $retval;
    }

    /** @brief Set the oldest entries you are willing to see.
     *   Any entries older than this will not
     *  be considered at all for display.  Note that they will still be considered for
     * publication interval calculations.  Most people do NOT want to set this, it's only usefull if
     *  all your feed publish very rarely, and you don't want entries older than a month, even if
     * there aren't enough items left to reach the number of items you asked for.
     * @param $maxItemAge: Age in seconds or null.   null means no limit (the default), a typical value would be 3600 * 24 * 30 (a month)
     * @return true if successfull, false if $maxItemAge < 0 */
    function setConfigMaxItemAge($maxItemAge = null) {
        $retval = false;
        if (is_numeric($maxItemAge) && $maxItemAge >= 0) {
            $this->_maxItemAge = $maxItemAge;
            $retval = true;
        }
        return $retval;
    }

    /** @brief Returns the allowed values for setConfigFeedExpansion
     * Use this when generating you option lists in your admin UI.
     * @return array, the index is the allowed constant, and the value the
     * description of the constant
     */
    static function getFeedExpansionAllowedOptions() {
        $array = array (
           'ALL' => _('Expand all items'
           ), 'NONE' => _('Do not expand any item'), 'FIRST' => _('Expand the first item'), 'FIRST_TWO' => _('Expand the first two items'), 'FIRST_THREE' => _('Expand the first three items'), 'PUBLISED_WITHIN_A_DAY' => _('Expand the items published within a day'), 'PUBLISED_WITHIN_TWO_DAYS' => _('Expand the items published within two days'), 'PUBLISED_WITHIN_THREE_DAYS' => _('Expand the items published within three days'), 'PUBLISED_WITHIN_A_WEEK' => _('Expand the items published within a week'), 'PUBLISED_SINCE_LAST_VIEWED' => _('Expand the items published since the user last saw the feed'),);
           return $array;
    }

    /** @brief Set the criteria that determines which feed items will be shown expanded by default.
     * Default is 'FIRST'
     * @param $configFeedExpansion: Allowable values are the indexes of
     * getFeedExpansionAllowedOptions()
     * @return true if successfull, false if $configFeedExpansion is not in
     * getFeedExpansionAllowedOptions() */
    function setConfigFeedExpansion($configFeedExpansion = 'FIRST') {
        $allowedOptions = self :: getFeedExpansionAllowedOptions();
        $retval = false;
        if (isset ($allowedOptions[$configFeedExpansion])) {
            $this->_configFeedExpansion = $configFeedExpansion;
            $retval = true;
        }
        return $retval;
    }

    /** @brief Returns the allowed values for setConfigFeedOrdering()
     * Use this when generating you option lists in your admin UI.
     * @return array, the index is the allowed constant, and the value the
     * description of the constant
     */
    static function getFeedOrderingAllowedOptions() {
        $array = array (
            'REVERSE_DATE' => _('Order items from the most recent to the last one, all sources merged'
            ), 'SOURCE_THEN_REVERSE_DATE' => _('Order by source, then by date (most recent first)'));
            return $array;
    }

    /** @brief Set in which order the feeds are displayed, and if items from all source should be merged together.
     * Default is 'REVERSE_DATE'
     * @param $configFeedOrdering: Must be an index of
     * getFeedOrderingAllowedOptions()
     * @return true if successfull, false if not in
     * getFeedOrderingAllowedOptions() */
    function setConfigFeedOrdering($configFeedOrdering = 'REVERSE_DATE') {
        $allowedOptions = self :: getFeedOrderingAllowedOptions();
        $retval = false;
        if (isset ($allowedOptions[$configFeedOrdering])) {
            $this->_configFeedOrdering = $configFeedOrdering;
            $retval = true;
        }
        return $retval;
    }

    /** @brief Set which HTML tags to strip out from the feeds.
     * Default is array('base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style')
     * @param $configStripHtmlTagArray: An array of html tags that should be stripped-out.
     * @return true if successfull, false if $configFeedOrdering is not in
     * getFeedOrderingAllowedOptions() */
    function setConfigStripHtmlTags($configStripHtmlTagArray = array (
        'base',
        'blink',
        'body',
        'doctype',
        'embed',
        'font',
        'form',
        'frame',
        'frameset',
        'html',
        'iframe',
        'input',
        'marquee',
        'meta',
        'noscript',
        'object',
        'param',
        'script',
        'style'
        )) {
            $allowedOptions = self :: getFeedOrderingAllowedOptions();
            $retval = false;
            if (is_array($configStripHtmlTagArray)) {
                $this->_configStripHtmlTagArray = $configStripHtmlTagArray;
                $retval = true;
            }
            return $retval;
        }

        /** @brief Set the date the user last read the feed from the aggregator (if you know it).
         *  Will allow the aggregator to tell the user which items are new,
         * and allow you to display them expanded if you so desire (using
         * setConfigFeedExpansion()
         * Default is null
         * @param $configLastDisplayed: PHP timestamp, or null
         * @return true */
        function setConfigLastDisplayed($configLastDisplayed = null) {
            $this->_configLastDisplayed = $configLastDisplayed;
            $retval = true;
            return $retval;
        }

        /** @brief Add a RSS source feed to be merged into the review
         * Must be called at least once before using most get methods
         * @param $url:  The feed's URL
         * @param $estimatedPublicationInterval:  The average time interval in seconds between items published on this
         * feed.  If unset (or set to null), defaults to  DEFAULT_PUBLICATION_INTERVAL (1 * 24 * 3600 (one day) as of this writing)
         * This parameter is ignored if the the value can be computed exactly, which is usually the case for
         * RSS 2.0 and atom feeds.
         * However, if the feed does not include the date, the system needs to be able to compute approximate
         * publication date for each entry, so the entry can be weighted against the others.
         * @param $sourceBias Optional, integer > 0.  The bias to be given to the source by the selection algorithm.
         *   Default is 1 (no bias).  A bias of 2 will cause the items to "look" twice as recent to the algorithm.
         *   A bias of 0.5 to look twice as old.  Be carefull, a bias of 2 will statistically cause the
         * feed to have MORE than twice as many items displayed.  Typical values would be between 0.75 and 1.5
         * @return true if the feed could be successfully retrieved and parsed, false otherwise */

        function addSourceFeed($url, $estimatedPublicationInterval = FeedPressReview :: DEFAULT_PUBLICATION_INTERVAL, $sourceBias = 1) {
            //echo "FeedPressReview::addSourceFeed($url, $estimatedPublicationInterval)<br>\n";
            $retval = false;
            if (empty ($estimatedPublicationInterval)) {
                $estimatedPublicationInterval = FeedPressReview :: DEFAULT_PUBLICATION_INTERVAL;
            }
            if (!empty ($url)) {
                //$old_error_level = error_reporting(E_ERROR);
                $simplepieFeed = new SimplePie();
                if (!empty ($this->_cacheDir)) {
                    $simplepieFeed->set_cache_location($this->_cacheDir);
                }
                //$simplepieFeed->set_item_class('FprSimplePie_Item');
                $simplepieFeed->set_feed_url(trim($url));
                $simplepieFeed->enable_order_by_date(true);
                $simplepieFeed->strip_htmltags($this->_configStripHtmlTagArray);
                $initSuccessfull = $simplepieFeed->init();
                $this->computeCacheAge($simplepieFeed);
                //error_reporting($old_error_level);

                $feed_order = count($this->_rssSources);
                $simplepieFeed->fpr_feed_order = $feed_order;
                if ($initSuccessfull && $sourceBias > 0) {
                    $this->_rssSources[trim($url)] = array (
                    'default_publication_interval' => $estimatedPublicationInterval,
                    'simplepie_feed' => $simplepieFeed,
                    'source_bias' => $sourceBias
                    );
                    //print_r ($estimatedPublicationInterval);
                    $this->computePublicationInterval(trim($url));
                    $retval = true;
                }
            }
            else {
                echo _('addSourceFeed(): url is empty!');
            }
            return $retval;
        }

        /** @brief Was this feed successfully added in addSourceFeed()?
         *   Equivalent to checking the
         * return of addSourceFeed(), but can be called at any time.  Will
         * return false if the feed could not be retrieved or coulsdn't be parsed.
         * @param $url
         * @return true if the feed has been successfully retrieved */
        function isFeedAvailable($url) {
            if (isset ($this->_rssSources[$url])) {
                $retval = true;
            }
            else {
                $retval = false;
            }
            return $retval;
        }

        /** @brief Return the title of the feed, if the feed contains title information.
         * @param $url Url of the feed whose title we want to be checked
         * @return string or false if unavailable */
        function getFeedTitle($url) {
            $retval = false;
            $title = null;
            if (isset ($this->_rssSources[$url])) {
                $title = $this->_rssSources[$url]['simplepie_feed']->get_title();
            }
            if (!empty ($title)) {
                $retval = $title;
            }
            return $retval;
        }

        /** @brief Return the computed publication interval of the feed (if it contained date information).
         *   You must have added the feed with addSourceFeed() before you use this function
         * @param $url Url of the feed to be checked
         * @return publication interval in seconds or false if unavailable */
        public function getFeedPublicationInterval($url) {
            if (isset ($this->_rssSources[$url]['simplepie_feed']->fpr_real_publication_interval)) {
                $retval = $this->_rssSources[$url]['simplepie_feed']->fpr_real_publication_interval;
            }
            else {
                $retval = false;
            }
            return $retval;
        }
        /** @brief compute the age of the cache file for this feed
         * Set's the fpr_cache_age property of the SimplePie object
         * @param $simplePie SimplePie object
         * @return age in seconds or false if unavailable */
        private function computeCacheAge(SimplePie $simplePie)
        {
            $cache = call_user_func(array($simplePie->cache_class, 'create'), $simplePie->cache_location, call_user_func($simplePie->cache_name_function, $simplePie->feed_url), 'spc');
            if ($cache) {
                $simplePie->fpr_cache_age = time() - $cache->mtime();
            }
            else {
                $simplePie->fpr_cache_age = 0;
            }
            return $simplePie->fpr_cache_age;
        }

        /** Inverted compare function for adjusted date.  Used to sort by adjusted date, most recent first  */
        private function cmpRprAdjustedDate($a, $b) {
            if ($a['fpr_adjusted_date'] == $b['fpr_adjusted_date']) {
                return 0;
            }
            return ($a['fpr_adjusted_date'] > $b['fpr_adjusted_date']) ? -1 : 1;
        }

        /** Compare function for the output array.  Used to sort by computed date, most recent first  */
        private function cmpDisplayItemsComputedDate($a, $b) {
            if ($a['item']->fpr_computed_date == $b['item']->fpr_computed_date) {
                return 0;
            }
            return ($a['item']->fpr_computed_date > $b['item']->fpr_computed_date) ? -1 : 1;
        }
        /** Compare function for the output array.  Used to sort by source, then computed date, most recent first  */
        private function cmpDisplayItemsSourceComputedDate($a, $b) {
            if ($a['simplepie_feed']->fpr_feed_order == $b['simplepie_feed']->fpr_feed_order) {
                return self :: cmpDisplayItemsComputedDate($a, $b);
            }
            return ($a['simplepie_feed']->fpr_feed_order > $b['simplepie_feed']->fpr_feed_order) ? -1 : 1;
        }

        /** Calculate the publication interval of a feed's items.  This will fail if there is no date field that can be used.  In this case we calculate approximate intervals based on the default publication interval.  That is we assume one item is published every default publication interval, starting from half the interval in the past (to avoid always having one item published now, skewing the result.
         * @param $url The feed's url
         * @return true on success, false on failure
         */
        private function computePublicationInterval($url) {

            $real_date_missing = false; /**< Is at least one of the items missing a date? */
            $publication_interval_total = null;
            /**< running total of the difference between the date of the current item and the previous one */
            $feed_publication_interval = null;
            $previous_item_date = null;
            $simplepieFeed = & $this->_rssSources[trim($url)]['simplepie_feed'];
            if (!$simplepieFeed) {
                echo _("computePublicationInterval(): Missing feed!");
                return false;
            }
            //pretty_print_r($simplepieFeed);

            /* Note: the feed array is sorted in reverse chronological order */

            /* Calculate the publication interval for the feed */
            $items = $simplepieFeed->get_items();
            $numIntervals = 0;
            if ($items) {
                $i = 0;
                foreach ($items as $item_key => $item) {
                    $real_date = $item->get_date("U");
                    $item->fpr_realphpdate = $real_date;
                    if ($real_date == false) {
                        /*
                         *  If we do not know the date, for statistics purposes,
                         *  we will set the date as if a news item was published
                         *  every default_publication_interval, starting from half the interval in the past
                         *  (to avoid always having one item published now, skewing the result).
                         */
                        $date = time() - ($i * $this->_rssSources[$url]['default_publication_interval'] + $this->_rssSources[$url]['default_publication_interval'] / 2);
                        $real_date_missing = true;
                    }
                    else {
                        $date = $real_date;
                    }
                    /* Add the cache age to the date (unless that would push the date in the future).  This will avoid a pathological case for feeds that publish a lot more often than the cache time */
                    if ($date + $simplepieFeed->fpr_cache_age < time()) {
                        $date = $date + $simplepieFeed->fpr_cache_age;
                    }

                    if ($i == 0 && $real_date_missing == false && $real_date < time()) {
                        /*If the newest item is in the past, we must consider the interval between now and it's date as part of the average.
                         * otherwise, feeds that took a "vacation" will get an unwanted bonus
                         * on their average.*/
                        /*This also allows the algorithm to properly calculate the publication interval for feeds with a single item in the past.*/
                        $previous_item_date = time();
                        $numIntervals++; //We must skip one for the average to be correct.
                        //echo "Calculating interval before first item<br/>";
                    }
                    if ($numIntervals > 0) {
                        $publication_interval_total += $previous_item_date - $date;
                    }
                    //echo "i: $i, numIntervals: $numIntervals, fpr_cache_age: {$simplepieFeed->fpr_cache_age}, date: $date, real_date: $real_date, previous_item_date: $previous_item_date<br/>";
                    $previous_item_date = $date;
                    $item->fpr_computed_date = $date; /** So every item has sone kind of a date */
                    $i++;
                    $numIntervals++;
                } // End foreach items
            }
            //pretty_print_r($publication_interval_total);
            if ($numIntervals >= 1 && $publication_interval_total != 0) { //You need at least one interval to calculate an average interval.
                $feed_publication_interval = $publication_interval_total / ($numIntervals);
                $simplepieFeed->fpr_computed_publication_interval = $feed_publication_interval;
                if ($real_date_missing == false) {
                    $simplepieFeed->fpr_real_publication_interval = $feed_publication_interval;
                }
            }
            else {
                $simplepieFeed->fpr_computed_publication_interval = $this->_rssSources[$url]['default_publication_interval'];
            }
            //echo "<pre>"; print_r($this->_rssSources[$url]);echo "</pre>";
            return true;
        }

        /** @brief Return the combined select items from all the sources, in an extended SimplePie format
        @param $numberOfItemsToSelect The total number of items to select from the items from all feeds.  The algorithm will never select more, but may select less if there aren't enough items newer than the max age in setConfigMaxItemAge().
        @param &$emptyFeeds Optional output parameter:  An array (possibly) empty of extended SimplePie objects for the feeds that didn't have any items selected selected for display
        @return An array (possibly empty) with the following format:
        * $outputItems[i]['simplepie_feed'] (A FprSimplePie object)
        * $outputItems[i]['item'] (An extended FprSimplePie_Item object)
        Note: FprSimplePie and FprSimplePie_Item objects have additional properties you can use over normal SimplePie objects, see their respective documentation
        */
        function getPressReviewInfo($numberOfItemsToSelect = 20, & $emptyFeeds = null) {
            $debug = false;
            //echo "<pre>"; print_r($this->_rssSources);echo "</pre>";
            if ($debug)
            echo "FeedPressReview::getPressReviewInfo($numberOfItemsToSelect), algorithmStrengthModifier = {$this->_algorithmStrengthModifier}<br>\n";

            if (!empty ($this->_rssSources)) {
                /* Calculate the average publication interval for all feeds */
                $all_feed_publication_interval_total = null;
                reset($this->_rssSources);
                foreach ($this->_rssSources as $rss_source) {
                    $all_feed_publication_interval_total += $rss_source['simplepie_feed']->fpr_computed_publication_interval;
                }
                $all_feed_publication_interval = $all_feed_publication_interval_total / count($this->_rssSources);

                /*Calculate the adjusted dates and an array of the adjusted dates and items.
                 $item_date_array[index]['fpr_adjusted_date']
                 $item_date_array[index]['fpr_computed_date']
                 $item_date_array[index]['_rssSources_key']*/
                $item_date_array = array ();
                $item_date_array_index = 0;

                foreach ($this->_rssSources as $_rssSources_key => $rss_source) {
                    $simplepieFeed = $rss_source['simplepie_feed'];
                    $items = $simplepieFeed->get_items();
                    if ($items) {
                        foreach ($items as $item_key => $item) {
                            /* Add the cache age to the date (unless that would push the date in the future).  This will avoid a pathological case for feeds that publish a lot more often than the cache time */
                            if ($item->fpr_computed_date + $simplepieFeed->fpr_cache_age < time()) {
                                $cacheAdjustedComputedDate = $item->fpr_computed_date + $simplepieFeed->fpr_cache_age;
                            }
                            else {
                                $cacheAdjustedComputedDate = $item->fpr_computed_date;
                            }
                            /* Calculate the adjusted date */
                            $feed_publication_interval = $simplepieFeed->fpr_computed_publication_interval;
                            $sourceBias = $rss_source['source_bias'];
                            $distance_to_today = abs(time() - $cacheAdjustedComputedDate);
                            /* With no strength modifier, a feed with a publication twice as long will get a 2x bonus on the distance to today */
                            $original_adjust_factor = $all_feed_publication_interval / $feed_publication_interval;

                            /*Algorithm strength modifier doit modifier la difference de lц╘cart du ratio
                             $all_feed_publication_interval / $feed_publication_interval avec 1/1.*/
                            $adjust_factor = 1 - (1 - $original_adjust_factor) * $this->_algorithmStrengthModifier;

                            $item->fpr_adjusted_date = time() - ($distance_to_today * $adjust_factor) / $sourceBias;

                            //echo "this->_maxItemAge: $this->_maxItemAge, abs(time() - item->fpr_computed_date): ".abs(time() - $item->fpr_computed_date)."<br/>";
                            /* Memorize each date, and publication intervals so we can determine the "oldest" item to publish
                            * Only consider items whose date is not farther from today than _maxItemAge */
                            if ($this->_maxItemAge == null || abs(time() - $item->fpr_computed_date) < $this->_maxItemAge) {
                                $item_date_array[$item_date_array_index]['fpr_adjusted_date'] = $item->fpr_adjusted_date;
                                $item_date_array[$item_date_array_index]['fpr_computed_date'] = $item->fpr_computed_date;
                                $item_date_array[$item_date_array_index]['_rssSources_key'] = $_rssSources_key;
                                //$item_date_array[$item_date_array_index]['fpr_debug_realphpdate'] = $item->fpr_realphpdate;
                                //$item_date_array[$item_date_array_index]['fpr_debug_real_age'] = time() - $item->fpr_realphpdate;
                                //$item_date_array[$item_date_array_index]['fpr_debug_cache_adjusted_real_age'] = time() - $cacheAdjustedComputedDate;
                                //$item_date_array[$item_date_array_index]['fpr_debug_adjusted_age'] = time() - $item->fpr_adjusted_date;
                                $item_date_array_index++;
                            }
                        }
                    }
                    //echo "<p>$i items, average_publication_interval (days) = ". $this->_rssSources[$_rssSources_key]['average_publication_interval']/(3600 * 24) . "</p>\n";
                } // End foreach rss feeds

                /* Sort the item date array by adjusted date. */
                usort($item_date_array, array (
                 "FeedPressReview",
                 "cmpRprAdjustedDate"
                 ));

                 /*echo "<pre>";
                  print_r($item_date_array);
                  echo "</pre>";*/
                 if (count($item_date_array) > 0) {
                     /* Find the "oldest" adjusted date to display */
                     if (count($item_date_array) < $numberOfItemsToSelect) {
                         $numberOfItemsToSelect = count($item_date_array);
                     }
                     $min_fpr_adjusted_date_to_display = $item_date_array[$numberOfItemsToSelect -1]['fpr_adjusted_date'];
                 }

                 else{
                     //There weren't any items past the max_item_age to consider
                     $min_fpr_adjusted_date_to_display = 2147483647; //Typical php_max_int
                 }
                 if ($debug)
                 echo "min_fpr_adjusted_date_to_display: $min_fpr_adjusted_date_to_display<br>\n";

                 /************** Now we actually build the array of feeds and items to display
                  *
                  * $outputItems[index]['simplepie_feed']
                  * $outputItems[index]['item']
                  *
                  *  **************/
                 reset($this->_rssSources);
                 $outputItems = array ();
                 $rss_info_tmp = null;
                 $emptyFeeds = array ();
                 foreach ($this->_rssSources as $_rssSources_key => $rss_source) {
                     $display_item = null;
                     $simplepieFeed = $rss_source['simplepie_feed'];
                     $i = 0;
                     $rss_info_tmp['items'] = array ();
                     $items = $simplepieFeed->get_items();
                     if ($items) {
                         foreach ($items as $item_key => $item) {
                             if ($debug)
                             echo "Is item with fpr_adjusted_date: " . $item->fpr_adjusted_date . " after the minimum date: $min_fpr_adjusted_date_to_display?<br>\n";
                             if ($item->fpr_adjusted_date >= $min_fpr_adjusted_date_to_display) {
                                 /* This item is fresh enough to be displayed */
                                 $display_item['simplepie_feed'] = $simplepieFeed;
                                 $display_item['item'] = $item;
                                 $outputItems[] = $display_item;
                                 $i++;
                             }

                         } // End foreach items
                     }
                     $simplepieFeed->fpr_num_selected_items = $i;
                     if ($i == 0) {
                         $emptyFeeds[] = $simplepieFeed;
                     }
                     if ($debug)
                     echo $i . " items (out of " . count($items) . ") are after the minimum date in feed $_rssSources_key<br>\n";
                 } // End foreach rss feeds

                 /* Now sort the display array by computed date */
                 usort($outputItems, array (
                  "FeedPressReview",
                  "cmpDisplayItemsComputedDate"
                  ));

                  /* Setup the criterias for feed expansion.  Feed will be expanded if it meets $expand_first_n or if it meets BOTH min and max date */
                  $expand_min_date = time();
                  $expand_max_date = time();
                  $expand_first_n = 0;
                  //echo $this->_configFeedExpansion;
                  switch ($this->_configFeedExpansion) {
                      case 'ALL' : //Expand all items
                          $expand_first_n = 1000000;
                      case 'NONE' : //Do not expand any item
                          break;
                      case 'FIRST' : //Expand the first item
                          $expand_first_n = 1;
                          break;
                      case 'FIRST_TWO' : //Expand the first two items
                          $expand_first_n = 2;
                          break;
                      case 'FIRST_THREE' : //Expand the first three items
                          $expand_first_n = 3;
                          break;
                      case 'PUBLISED_WITHIN_A_DAY' : //Expand the items published within a day
                          $expand_min_date = $expand_min_date -60 * 60 * 24;
                          $expand_max_date = $expand_max_date +60 * 60 * 24;
                          break;
                      case 'PUBLISED_WITHIN_TWO_DAYS' : //Expand the items published within two days
                          $expand_min_date = $expand_min_date -60 * 60 * 24 * 2;
                          $expand_max_date = $expand_max_date +60 * 60 * 24 * 2;
                          break;
                      case 'PUBLISED_WITHIN_THREE_DAYS' : //Expand the items published within three days
                          $expand_min_date = $expand_min_date -60 * 60 * 24 * 3;
                          $expand_max_date = $expand_max_date +60 * 60 * 24 * 3;
                          break;
                      case 'PUBLISED_WITHIN_A_WEEK' : //Expand the items published within a week
                          $expand_min_date = $expand_min_date -60 * 60 * 24 * 7;
                          $expand_max_date = $expand_max_date +60 * 60 * 24 * 7;
                          break;
                      case 'PUBLISED_SINCE_LAST_VIEWED' : //Expand the items published since the user last saw the feed'
                          $expand_min_date = $expand_min_date -60 * 60 * 24;
                          $expand_max_date = $expand_max_date +60 * 60 * 24;
                          break;
                  }

                  $i = 0;
                  foreach ($outputItems as $display_item) {
                      $item = $display_item['item'];

                      /* Check if the item should be expanded */
                      $compareDate = $item->fpr_computed_date;
                      if ($debug)
                      echo "compareDate: $compareDate, expand_min_date: $expand_min_date, expand_max_date, $expand_max_date, i: $i, expand_first_n: $expand_first_n<br/>";
                      if (($compareDate > $expand_min_date && $compareDate < $expand_max_date) || $i < $expand_first_n) {
                          $item->fpr_should_be_expanded = true;
                          if ($debug)
                          echo "^EXPAND<br/>";
                      }
                      else {
                          $item->fpr_should_be_expanded = false;
                      }

                      if (isset ($item->fpr_realphpdate)) {
                          /* Check if the item is newer than the last visit */
                          if ($this->_configLastDisplayed != null && $item->fpr_realphpdate > $this->_configLastDisplayed) {
                              $item->fpr_newer_than_last_display = true;
                          }
                          else {
                              $item->fpr_newer_than_last_display = false;
                          }
                      }
                      $i++;
                  }

                  if ($this->_configFeedOrdering == 'SOURCE_THEN_REVERSE_DATE') {
                      /** Re-sort the display array by source date */
                      usort($outputItems, array (
                      "FeedPressReview",
                      "cmpDisplayItemsSourceComputedDate"
                      ));
                  }

                  /*echo "\n<pre>\n";
                   print_r($outputItems);
                   echo "\n</pre>\n";*/

                  return $outputItems;
            }
            else
            return array();
        }

        /** @brief Not recommended!  Returns the javascript necessary for the getOutputHtml() method.
         * You should instead reference the feedpressreview.js file directly from
         * your html head.
         *
         * If you cannot (feedpressreview not installed inside your document root),
         * this static method returns the content of feedpressreview.js in a block
         * appropriate to be written inside your html 'head' element.  If you no
         * longuer have access to modify the content of the 'head' html element in
         * your script, you can usually include this content before using
         * getOutputHtml(), but make sure you include it only once.
         @return HTML 'script' element */
        static public function getOutputJs() {
            $retval = null;
            $retval .= '<script language="JavaScript" type="text/javascript">' . "\n";
            $filename = dirname(__FILE__) . "/feedpressreview.js";
            $handle = fopen($filename, "r");
            $contents = fread($handle, filesize($filename));
            fclose($handle);
            $retval .= $contents;
            $retval .= '</script>' . "\n";
            return $retval;
        }
        /** Get an item's metadata
        @return string HTML markup */
        private function getItemInfoHtml(SimplePie_item $item) {
            $html = '';
            $html .= "<ul>\n";
            /* Is item never than the user's last visit? */
            if ($item->fpr_newer_than_last_display) {
                $html .= "<li><span class='fpr_newer_than_last_display_img'></span><span class='fpr_newer_than_last_display_text'>" . _("This item has been added since your last visit") . "</span></li>\n";
            }
            /* Authors */
            $authors = $item->get_authors();
            if ($authors) {
                $str = '';
                $first = true;
                foreach ($authors as $author) {
                    $email = $author->get_email();
                    $link = $author->get_link();
                    $name = $author->get_name();
                    if (!$first) {
                        $str .= ', ';
                    }
                    $str = $name . ' ' . $email . ' ' . $link;

                }
                $html .= "<li><span class='fpr_caption'>" . ngettext("Author:", "Authors:", count($authors)) . "</span> {$str}</li>\n";
            }

            /* Categories */
            $categories = $item->get_categories();
            if ($categories) {
                $str = '';
                $first = true;
                foreach ($categories as $category) {
                    if (!$first) {
                        $str .= ', ';
                    }
                    $str = $category;
                }
                $html .= "<li><span class='fpr_caption'>" . ngettext("Category:", "Categories:", count($categories)) . "</span> {$str}</li>\n";
            }
            $html .= "</ul>\n";
            return $html;
        }

        /** Get a feed's metadata
        @return string HTML markup */
        private function getFeedInfoHtml(SimplePie $simplepieFeed) {
            $html = '';
            $url = $simplepieFeed->get_image_url();
            if (!empty($url)) {
                $width = $simplepieFeed->get_image_width();
                if (empty ($width)) {
                    //$width = 88;
                }
                $height = $simplepieFeed->get_image_height();
                if (empty ($height)) {
                    //$height = 31;
                }

                $title = $simplepieFeed->get_image_title();
                $imgHtml = "<img class='fpr_feed_image' src='$url' alt='$title' width='$width' height='$height' />\n";
                $link = $simplepieFeed->get_image_link();
                if (!empty ($link)) {
                    $html .= "<a href='$link' title='$title'>$imgHtml</a>";
                }
                else {
                    $html .= $imgHtml;
                }
            }
            else {
                $url = @ $simplepieFeed->get_favicon();
                if (!empty ($url)) {
                    $title = _("Favicon from the web site");
                    $html .= "<img class='fpr_feed_image' src='$url' alt='$title' />\n";
                }
            }
            $html .= "<ul>\n";
            $str = $simplepieFeed->get_title();
            if (!empty ($str)) {
                $html .= "<li><span class='fpr_caption'>" . _("Title:") . "</span> {$str}</li>\n";
            }

            $str = $simplepieFeed->get_link();
            if (!empty ($str)) {
                $html .= "<li><span class='fpr_caption'>" . _("Link:") . "</span> <a href='{$simplepieFeed->feed_url}'>$str</a></li>\n";
            }

            $str = $simplepieFeed->get_description();
            if (!empty ($str)) {
                $html .= "<li><span class='fpr_caption'>" . _("Description:") . "</span> {$str}</li>\n";
            }
            $str = $simplepieFeed->get_copyright();
            if (!empty ($str)) {
                $html .= "<li><span class='fpr_caption'>" . _("Copyright:") . "</span> {$str}</li>\n";
            }
            if (!empty ($simplepieFeed->fpr_real_publication_interval)) {
                $rate = (3600 * 24) / $simplepieFeed->fpr_real_publication_interval;
                $ratelStr = sprintf(ngettext("%.2f item/day", "%.2f items/day", round($rate, 0)), $rate);
                $html .= "<li><span class='fpr_caption'>" . _("Publication interval:") . "</span> {$ratelStr}</li>\n";
            }
            $str = ngettext("%d out of %d items was selected from this feed.", "%d out of %d items were selected from this feed.", $simplepieFeed->fpr_num_selected_items);
            $html .= "<li>" . sprintf($str, $simplepieFeed->fpr_num_selected_items, $simplepieFeed->get_item_quantity()) . "</li>\n";
            if ($simplepieFeed->get_type() & SIMPLEPIE_TYPE_NONE)
            {
                $typeStr = _("Unknown");
            }
            elseif ($simplepieFeed->get_type() & SIMPLEPIE_TYPE_RSS_ALL)
            {
                $typeStr = _("RSS");
            }
            elseif ($simplepieFeed->get_type() & SIMPLEPIE_TYPE_ATOM_ALL)
            {
                $typeStr = _("Atom");
            }
            elseif ($simplepieFeed->get_type() & SIMPLEPIE_TYPE_ALL)
            {
                $typeStr = _("Unknown feed type");
            }

            $html .= "<li><span class='fpr_caption'>{$typeStr}:</span> <a href='{$simplepieFeed->feed_url}'>{$simplepieFeed->feed_url}</a></li>\n";
            $html .= "</ul>\n";
            return $html;
        }

        /** Get a feed's title as well as collapsed metadata
        @return string HTML markup */
        private function getFeedHtml(SimplePie $simplepieFeed) {
            $html = '';
            $titleHtml = '';
            $contentHtml = '';
            $titleHtml .= "<div class='fpr_title_str'>\n";
            $feed_link = $simplepieFeed->get_link();
            if (!empty ($feed_link)) {
                $titleHtml .= "<a class='y' href='" . $feed_link . "'>" . $simplepieFeed->get_title() . "</a>";
            }
            else {
                $titleHtml .= $simplepieFeed->get_title();
            }
            $titleHtml .= "</div>\n";
            $contentHtml .= self :: getFeedInfoHtml($simplepieFeed);
            $linkHtml = null;
            self :: genColapseableDiv($linkHtml, $titleHtml, $contentHtml, false);

            $html .= "$titleHtml\n";
            $html .= $linkHtml;
            $html .= $contentHtml;
            return $html;
        }
        /** @brief The simplest way to generate final HTML output.
         * getOutputHtml() will output a full HTML structure for the feeds, obeying
         * all options.  You must reference the feedpressreview.js (prefered) or
         * include it using the getOutputJs() method before using getOutputHtml. The
         * resulting html is styled using a number of fpr_ CSS classes.  We suggest
         * you start with the example/feedpressreview.css file and modify it to suit
         * your needs. If you need more customization options than what is offered
         * by getOutputHtml(), you will have to use the output of
         * getPressReviewInfo() instead and build your own HTML.
         @param $numberOfItemsToSelect The total number of items to select from the items from all feeds.  The algorithm will never select more, but may select less if there aren't enough items newer than the max age in setConfigMaxItemAge().
         @param $displayEmptyFeeds optional bolean, defaults to false.  Should we show the feed information for feeds that didn't have and items in the final selection?
         @param $expandItemString optional string, defaults to '+', null is an acceptable value (to use a background image instead, or to disable the feature)  The caption displayed in the area the user must click on to expand an item.  Defaults to '+'
         @param $collapseItemString  optional string, defaults to '+', null is an acceptable value (to use a background image instead, or to disable the feature)  The caption displayed in the area the user must click on to collapse an item.  Defaults to '-'
         */
        function getOutputHtml($numberOfItemsToSelect = 20, $displayEmptyFeeds = false, $expandItemString = '+', $collapseItemString = '-') {
            $this->_expandItemString = $expandItemString;
            $this->_collapseItemString = $collapseItemString;
            $groupBySource = false;
            if ($this->_configFeedOrdering == 'SOURCE_THEN_REVERSE_DATE') {
                $groupBySource = true;
            }
            $html = '';

            $emptyFeeds = null;
            $outputItems = $this->getPressReviewInfo($numberOfItemsToSelect, $emptyFeeds);
            $previous_simplepie_feed = null;
            $html .= "<ul class='fpr_list'>\n";
            foreach ($outputItems as $display_item) {
                $simplepieFeed = $display_item['simplepie_feed'];
                $item = $display_item['item'];
                $itemHtml = '';

                /* Format the date */
                $item_getdate = getdate($item->fpr_realphpdate);
                $today_getdate = getdate();
                if ($item_getdate['year'] == $today_getdate['year'] && $item_getdate['yday'] == $today_getdate['yday'] - 1) {
                    $display_date = _("Yesterday");
                }
                else
                if ($item_getdate['year'] == $today_getdate['year'] && $item_getdate['yday'] == $today_getdate['yday']) {
                    $display_date = _("Today");
                }
                else
                if ($item_getdate['year'] == $today_getdate['year'] && $item_getdate['yday'] == $today_getdate['yday'] + 1) {
                    $display_date = _("Tommorow");
                }
                else {
                    if ($item->fpr_realphpdate != -1 && $item->fpr_realphpdate != 0) {
                        $display_date = strftime("%x", $item->fpr_realphpdate);
                    }
                    else {
                        $display_date = '';
                        //$display_date = "Estimated: ".strftime("%x", $item['fpr_computed_date']);
                    }
                }

                /* Display the feed link, if we sort by feed */
                if ($groupBySource && $previous_simplepie_feed !== $simplepieFeed) {
                    $itemHtml .= "<li class='fpr_feed_source fpr_feed_title'>\n";
                    $itemHtml .= _('Source: ') . $this->getFeedHtml($simplepieFeed);
                    $itemHtml .= "</li>\n";
                }

                /* Process enclosures */
                $enclosures = $item->get_enclosures();
                $enclosuresHtml = '';
                if ($enclosures) {
                    $enclosuresHtml .= "<div class='fpr_enclosure'>\n";
                    $enclosuresHtml .= "<div class='fpr_enclosure_inner'>\n";
                    foreach ($enclosures as $enclosure) {
                        $embeed_params = array (
                           'alt' => _('Unknown enclosure type'
                           ), 'width' => '100%', 'height' => '100%',);
                           $enclosuresHtml .= $enclosure->native_embed($embeed_params) . "<br/>\n";
                           $length_str = null;
                           $length = $enclosure->get_length();
                           if ($length) {
                               if ($length < 1024) {
                                   $length_str = sprintf("%.0f B", $length);
                               }
                               else
                               if ($length < 1024 * 1024) {
                                   $length_str = sprintf("%.2f KiB", $length / 1024);
                               }
                               else
                               if ($length < 1024 * 1024 * 1024) {
                                   $length_str = sprintf("%.2f MiB", $length / (1024 * 1024));
                               }
                               else {
                                   $length_str = sprintf("%.2f GiB", $length / (1024 * 1024));
                               }

                           }
                           $link = $enclosure->get_link();
                           $mime = $enclosure->get_type();
                           $title_str = sprintf(_("Download enclosure %s %s"), $length_str, $mime);
                           $link_str = _("Download enclosure");
                           $enclosuresHtml .= "<div class='fpr_enclosure_download'><a href='$link' title='$title_str' type='$mime'>$link_str</a></div>\n";
                    }
                    $enclosuresHtml .= "</div>\n";
                    $enclosuresHtml .= "</div><!-- End fpr_enclosure -->\n";

                }

                /* Display the item */
                $titleHtml = '';
                $titleHtml .= "<div class='fpr_title_str'>\n";
                $link = $item->get_link();
                if (!empty ($link)) //TODO: Support more than one link
                {
                    $titleHtml .= "<a class='fpr_item_link' href='{$link}'>{$item->get_title()}</a>";
                }
                else {
                    $titleHtml .= "{$item->get_title()}";
                }
                $titleHtml .= "</div>\n";

                $contentHtml = '';

                $contentHtml .= $enclosuresHtml;
                $contentHtml .= "<div class='fpr_item_metadata'>\n";
                $contentHtml .= $this->getItemInfoHtml($item);
                $contentHtml .= "</div>\n";
                $contentHtml .= "<div class='fpr_text'>\n";
                $contentHtml .= $item->get_description();
                $contentHtml .= "</div>\n";
                if ($groupBySource == false) {
                    $contentHtml .= "<div class='fpr_feed_source'>\n";
                    $contentHtml .= _('Source: ') . $this->getFeedHtml($simplepieFeed);
                    $contentHtml .= "</div>\n";
                }
                $contentHtml .= "<br class='fpr_clear'/>\n";

                $linkHtml = '';
                self :: genColapseableDiv($linkHtml, $titleHtml, $contentHtml, $item->fpr_should_be_expanded);

                $itemHtml .= "<li class='fpr_item'>\n";
                $itemHtml .= "<div class='fpr_item_title'>\n";

                if ($item->fpr_newer_than_last_display) {
                    $itemHtml .= "<span class='fpr_newer_than_last_display_img'></span>\n";
                }
                $itemHtml .= "<span class='fpr_title_date'>$display_date</span>";
                $itemHtml .= $titleHtml . ' ' . $linkHtml;
                $itemHtml .= "</div>\n";

                $itemHtml .= $contentHtml;
                $itemHtml .= "</li>\n";
                $html .= $itemHtml;
                $previous_simplepie_feed = $simplepieFeed;
            }

            if ($displayEmptyFeeds && !empty ($emptyFeeds)) {
                $html .= "<div class='fpr_empty_feeds'>\n";
                $html .= ngettext("No selected items from source: ", "No selected items from sources: ", count($emptyFeeds));
                $html .= "<ul>\n";
                foreach ($emptyFeeds as $simplepieFeed) {
                    $html .= "<li>" . self :: getFeedHtml($simplepieFeed) . "</li>\n";
                }
                $html .= "</ul>\n";
                $html .= "</div>\n";
            }
            $html .= "</ul>\n";
            return $html;
        }
        private function genColapseableDiv(& $linkHtml, & $mouseoverHtml, & $contentHtml, $defaultExpand = false) {
            $linkHtml = '';
            $mouseoverHtmlOrig = $mouseoverHtml;
            $mouseoverHtmlTmp = '';
            $contentHtmlOrig = $contentHtml;
            $contentHtmlTmp = '';
            $html = '';
            $dhtmlId = "summary_" . mt_rand(1, 10000);

            $mouseoverHtmlTmp .= "<div class='fpr_mouseover' onmouseover=\"changestyle('$dhtmlId','visible');\" onmouseout=\"changestyle('$dhtmlId','hidden');\">\n";
            $mouseoverHtmlTmp .= $mouseoverHtmlOrig;
            $mouseoverHtmlTmp .= "</div>\n";
            $mouseoverHtml = $mouseoverHtmlTmp;

            /* Expand/collapse switch */
            $defaultExpand ? $switch_content = $this->_collapseItemString : $switch_content = $this->_expandItemString;
            $linkHtml .= "<a class='fpr_expand_switch' href='#dummy' onclick=\"toggleItemVisibility('$dhtmlId'); if(this.innerHTML=='$this->_expandItemString'){this.innerHTML='-';}else{this.innerHTML='$this->_expandItemString';}\">$switch_content</a> ";

            $contentHtmlTmp .= "<div class='fpr_popup_outer_div'>\n";
            $defaultExpand ? $class = 'fpr_popup_inner_div_expanded' : $class = 'fpr_popup_inner_div';
            $defaultExpand ? $script = "changestyle('$dhtmlId','visible');" : $script = '';
            $contentHtmlTmp .= "<div class='$class' id='$dhtmlId'>\n";
            $contentHtmlTmp .= $contentHtmlOrig;
            $contentHtmlTmp .= "</div>\n";
            $contentHtmlTmp .= "</div>\n";
            $contentHtml = $contentHtmlTmp;
        }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
