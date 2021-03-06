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
    $sentence = createSentence($commonWords);
    $sentence = $sentence . " " . getRandomHashtag();
    return json_encode($sentence);
}

function getCommonWords($tweets) {
    $words = getWordCounts($tweets);
    $words = removeStupidWords($words);
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
    $commonWords = array_map(function($word) {
        return $word['word'];
    }, $commonWords);
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
    return $hashtags[rand(0, count($hashtags))];
}

function createSentence($commonWords) {
    $sentences = [
        sprintf("I really enjoy %s, and like to %s every day", array_pop($commonWords), array_pop($commonWords)),
        sprintf("%s and %s. End of.", array_pop($commonWords), array_pop($commonWords)),
        sprintf("Remember %s? Retro. #%s", array_pop($commonWords), array_pop($commonWords)),
        sprintf("%s and %s are great", array_pop($commonWords), array_pop($commonWords)),
        sprintf("#%s #%s #%s #%s #%s", array_pop($commonWords), array_pop($commonWords), array_pop($commonWords), array_pop($commonWords), array_pop($commonWords)),
        sprintf("What happened to Bill Gates? %s? %s? #whoknows", array_pop($commonWords), array_pop($commonWords)),
        sprintf("Sandwiches, filled with %s and %s", array_pop($commonWords), array_pop($commonWords)),
        sprintf("Who is Mr %s? A %s? #lol", array_pop($commonWords), array_pop($commonWords)),
        sprintf("I just %s and %s #yolo", array_pop($commonWords), array_pop($commonWords)),
    ];
    return $sentences[rand(0, count($sentences) - 1)];
}

function removeStupidWords($words) {
    $words = array_filter($words, function($word) {
        $badwords = [
            'the',
            'a',
            'and',
            'RT',
            'to',
            '?',
            'of',
        ];
        if(in_array($word, $badwords)) {
            return false;
        }
        return true;
    });
    return $words;
}

$app->run();
