<?php
	
	// JSON 2 RSS (twitter search api) converter
	// scripted by Michael Tanner
	//
	// https://github.com/thonixx/json2rss-twitter
	//
	// License: CC-BY-SA
	// https://creativecommons.org/licenses/by-sa/3.0/ch/
	//
        //////////////////////////////////////
    
	
	//////////////////////////////
	////// EDIT THIS TO YOUR NEEDS
	// define what to search
	$tweetsearch = 'haekelschwein%20pic.twitter.com%20-from:haekelschwein%20-RT';
	// where to save the final rss
	$rssfile = 'haekelschwein.rss';
	///////////////////////////////
	
	// authentication
	require_once('twitter-api-php/TwitterAPIExchange.php');
	// auth config
	require_once('auth_config.php');
	
	// set right header (content type things)
	header("Content-Type: application/rss+xml; charset=utf-8");
	
	// twitter search api url
	// $json_url = 'http://search.twitter.com/search.json?q='.$tweetsearch.'&result_type=recent&count=50'; // deactivated
	$json_url = 'https://api.twitter.com/1.1/search/tweets.json';
	$getfield = '?q='.$tweetsearch.'&result_type=recent&count=50';
	// html method
	$requestMethod = 'GET';
	// authenticate and get the results
	$twitter = new TwitterAPIExchange($settings);
	$json = $twitter->setGetfield($getfield)
		     ->buildOauth($json_url, $requestMethod)
		     ->performRequest();
	// decode json string
	$twitter_array = json_decode($json, true);
	// die(var_dump($twitter_array));
	// all results in one array
	$results = $twitter_array['statuses'];
	
	// publishing date (actual refresh time)
	$pubDate = date('r');
	
	// print the beginning of the rss file
	$rss = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
		<rss xmlns:media=\"http://search.yahoo.com/mrss/\" xmlns:atom=\"http://www.w3.org/2005/Atom\" version=\"2.0\">
		<channel>
			<title>Häkelschwein Twitter</title>
			<link>http://haekelschwein-pics.tumblr.com/</link>
			<description>Häkelschwein Blog mit Twitter-Bildern.</description>
			<atom:link href=\"http://project.pixelwolf.ch/json2rss/\" rel=\"self\" type=\"application/rss+xml\" />
			<language>de-de</language>
			<pubDate>$pubDate</pubDate>
			<lastBuildDate>$pubDate</lastBuildDate>";
	
	// loop through all search results
	foreach($results as $r) {
		// was too lazy to change from curtweet to r variable
		$curTweet = $r;

		// get image url in tweet
		$tweet = $curTweet['text'];
		$text = preg_replace("/(http)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", '', $tweet);

		// get tweet time
		$time = $curTweet['created_at'];

		// status url
		$username = $curTweet['user']['screen_name'];
		$statusid = $curTweet['id'];
		$statusUrl = "https://twitter.com/$username/status/$statusid";

		// just take the pic.twitter.com url to the image
		$pictwittercom = $curTweet['entities']['media'][0]['media_url'];
		
		// build rss item
		$rss .= "<item>
				<title>$username: $text</title>
				<guid isPermaLink=\"true\">$statusUrl</guid>
				<link>$statusUrl</link>
				<pubDate>$time</pubDate>
				<description>$text &lt;img src=&quot;$pictwittercom&quot; /&gt;</description>
				<media:content type=\"image/jpeg\" url=\"$pictwittercom\"/>
			</item>";
	}
	// some rss ending stuff
	$rss .= "</channel>
</rss>";
	
	// now write the stuff to the rss feed
	file_put_contents($rssfile, $rss)

?>
