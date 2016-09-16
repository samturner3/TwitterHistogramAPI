<?php

date_default_timezone_set("Australia/Sydney");


require_once __DIR__.'/../vendor/autoload.php'; 
//use Abraham\TwitterOAuth\TwitterOAuth;
require_once 'getTweets.php';

//require_once __DIR__.'/../twitteroauth/autoload.php';
//use Abraham\TwitterOAuth\TwitterOAuth;

require_once 'autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;



$app = new Silex\Application(); 

$app['debug'] = true;


$app->get('/', function() use($app) { 
    return 'Try /hello/:name';
}); 


$app->get('/hello/{name}', function($name) use($app) { 
    return 'Hello '.$app->escape($name); 
}); 

$app->get('/histogram/', function() use($app) { 
    return 'Try /histogram/:TwitterUserName '.$app->escape($name); 
}); 

$app->get('/histogram/{name}', function($name) use($app) { 

	$tweetHistogram = new TweetsJSON();

	$tweetHistory = $tweetHistogram->GetTweetsJSON($name);
    return $tweetHistory;
}); 


$app->get('/histogram/gui/{name}', function($name) use($app) { 

	$tweetHistogram = new TweetsGUI();

	$tweetHistory = $tweetHistogram->GetTweetsGUI($name);
    return $tweetHistory;

}); 

$app->run(); 

?>