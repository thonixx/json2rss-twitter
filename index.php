<?php
	
	// JSON 2 RSS (twitter search api) converter
	// scripted by Michael Tanner
	
	//header("Content-Type: application/rss+xml; charset=utf-8");
	
	// define what to search
	$tweetsearch = 'haekelschwein%20pic.twitter.com%20-from:haekelschwein%20-RT';
	// twitter search api url
	$json_url = 'http://search.twitter.com/search.json?q='.$tweetsearch.'&result_type=recent&count=50';
	// get the results
	$json = file_get_contents($json_url);
	// decode json string
	$twitter_array = json_decode($json, true);
	// all results in one array
	$results = $twitter_array['results'];
	
	
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
	
	foreach($results as $r) {
		// loop through all search results
		$curTweet = $r;

		// get image url in tweet
		$tweet = $curTweet['text'];
		preg_match("/(http)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", $tweet, $url);
		$url = $url[0];

		// get tweet text
		$text = trim(str_replace($url, '', $tweet));

		// get tweet time
		$time = $curTweet['created_at'];

		// status url
		$username = $curTweet['from_user'];
		$statusid = $curTweet['id'];
		$statusUrl = "https://twitter.com/$username/status/$statusid/";

		// twitter status api
		$status_api = "https://api.twitter.com/1/statuses/show.json?id=$statusid&include_entities=true";
		// get the results
		$tweetjson = file_get_contents($status_api);
		// decode json string
		$tweet_array = json_decode($tweetjson, true);
		// all results in one array
		$pictwittercom = $tweet_array['entities']['media'][0]['media_url'];

		$rss .= "<item>
				<title>$text</title>
				<guid isPermaLink=\"true\">$statusUrl</guid>
				<link>$statusUrl</link>
				<pubDate>$time</pubDate>
				<description>$text &lt;img src=&quot;$pictwittercom&quot; /&gt;</description>
				<media:content type=\"image/jpeg\" url=\"$pictwittercom\"/>
			</item>";
	}
	$rss .= "</channel>
	</rss>";
	
	file_put_contents('haekelschwein.rss', $rss)

?>
