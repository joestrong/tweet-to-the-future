<?php

require_once __DIR__.'/../vendor/autoload.php';

use TwitterOAuth\Auth\ApplicationOnlyAuth;
use TwitterOAuth\Serializer\ArraySerializer;

$credentials = [
    'consumer_key' => "q76Uz7tqlYNMRkjc2kBsAFsv5",
    'consumer_secret' => "j1nDGT3uRRJQKtLy3dyjPjCuBUexXKFZc1vqPi2lY7B6Gx7Xwd",
];

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../',
));

$app->get('/', function() use($app) {
    return $app['twig']->render('index.twig');
});

$app->get('/get-tweet/{username}', function($username) use($app, $credentials) {
    $auth = new ApplicationOnlyAuth($credentials, new ArraySerializer());
    $params = array(
        'screen_name' => $username,
        'count' => 50,
        'exclude_replies' => true
    );
    $tweets = $auth->get('statuses/user_timeline', $params);
    //echo '<pre>'; print_r($auth->getHeaders()); echo '</pre>';
    //echo '<pre>'; print_r($response); echo '</pre><hr />';
    return makeTweet($tweets);
});

function makeTweet($tweets) {
    $commonWords = getCommonWords($tweets);
    $commonWords = array_merge($commonWords, getRandomHashTag());
    return json_encode($commonWords);
}

function getCommonWords($tweets) {
    $words = getWordCounts($tweets);
    $words = array_splice($words, 0, 10);
    return $words;
}

function getWordCounts($tweets) {
    $commonWords = [];
    foreach ($tweets as $tweet) {
        foreach (explode(' ', $tweet['text']) as $word) {
            if(!empty($commonWords[$word])) {
                $commonWords[$word] = [
                    'word' => $word,
                    'count' => $commonWords[$word]['count'] + 1,
                ];
            } else {
                $commonWords[$word] = [
                    'word' => $word,
                    'count' => 1,
                ];
            }
        }
    }
    usort($commonWords, function($a, $b) {
        return $b['count'] <=> $a['count'];
    });
    return $commonWords;
}

function getRandomHashtag() {
    $hashtags = [
        '#cyborgBillGates',
        '#marsApartments',
        '#invisibleninjas',
        '#spaceFood',
        '#myRobotAttackedMe',
    ];
    return [
        10 => [
            'word' => $hashtags[rand(0, count($hashtags))]
        ],
    ];
}

$app->run();
