<?php

require_once 'app/Helpers/TextHelper.php';

use App\Helpers\TextHelper;

function runTest($name, $input, $expectedPattern) {
    echo "Testing: $name\n";
    $result = TextHelper::processEmbeds($input);
    
    if (preg_match($expectedPattern, $result)) {
        echo "[PASS] Match found.\n";
    } else {
        echo "[FAIL] Pattern not found.\n";
        echo "Input: $input\n";
        echo "Output: $result\n";
        echo "Expected Pattern: $expectedPattern\n";
    }
    echo "---------------------------------------------------\n";
}

echo "Starting TextHelper::processEmbeds tests...\n\n";

// 1. Instagram
runTest(
    "Instagram Post",
    '<p><a href="https://www.instagram.com/p/CushIgHgABC/">https://www.instagram.com/p/CushIgHgABC/</a></p>',
    '/blockquote class="instagram-media" data-instgrm-permalink="https:\/\/www.instagram.com\/p\/CushIgHgABC\/"/'
);

// 2. Twitter
runTest(
    "Twitter Post",
    '<p><a href="https://twitter.com/spacex/status/123456789">https://twitter.com/spacex/status/123456789</a></p>',
    '/blockquote class="twitter-tweet".*href="https:\/\/twitter.com\/spacex\/status\/123456789"/'
);

// 3. X.com (should convert to twitter.com for embed)
runTest(
    "X.com Post",
    '<p>https://x.com/spacex/status/987654321</p>',
    '/blockquote class="twitter-tweet".*href="https:\/\/twitter.com\/spacex\/status\/987654321"/'
);

// 4. Facebook Post
runTest(
    "Facebook Post",
    '<p><a href="https://www.facebook.com/zuck/posts/10111">https://www.facebook.com/zuck/posts/10111</a></p>',
    '/div class="fb-post" data-href="https:\/\/www.facebook.com\/zuck\/posts\/10111"/'
);

// 5. Facebook Video
runTest(
    "Facebook Video",
    '<p>https://www.facebook.com/watch/?v=12345</p>',
    '/div class="fb-video" data-href="https:\/\/www.facebook.com\/watch\/\?v=12345"/'
);

// 6. TikTok
runTest(
    "TikTok Video",
    '<p><a href="https://www.tiktok.com/@user/video/7777777">https://www.tiktok.com/@user/video/7777777</a></p>',
    '/blockquote class="tiktok-embed" cite="https:\/\/www.tiktok.com\/@user\/video\/7777777" data-video-id="7777777"/'
);

// 7. YouTube
runTest(
    "YouTube Video",
    '<p><a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">https://www.youtube.com/watch?v=dQw4w9WgXcQ</a></p>',
    '/iframe src="https:\/\/www.youtube.com\/embed\/dQw4w9WgXcQ"/'
);

// 8. YouTube Short link
runTest(
    "YouTube Short Link",
    '<p>https://youtu.be/dQw4w9WgXcQ</p>',
    '/iframe src="https:\/\/www.youtube.com\/embed\/dQw4w9WgXcQ"/'
);

// 9. Regular text (should not change)
runTest(
    "Regular Text",
    '<p>Just some text with a <a href="https://google.com">link</a> inside.</p>',
    '/<p>Just some text with a <a href="https:\/\/google.com">link<\/a> inside.<\/p>/'
);

// 10. Strava
runTest(
    "Strava Activity",
    '<p>https://www.strava.com/activities/1234567890</p>',
    '/iframe.*src="https:\/\/www.strava.com\/activities\/1234567890\/embed\/1"/'
);

// 11. Reddit
runTest(
    "Reddit Post",
    '<p>https://www.reddit.com/r/pics/comments/123abc/title_of_post/</p>',
    '/blockquote class="reddit-card".*href="https:\/\/www.reddit.com\/r\/pics\/comments\/123abc\/title_of_post\/\?ref=share/'
);

// 12. Threads
runTest(
    "Threads Post",
    '<p>https://www.threads.net/@user/post/123abcXYZ</p>',
    '/blockquote class="instagram-media" data-instgrm-permalink="https:\/\/www.threads.net\/@user\/post\/123abcXYZ"/'
);

// 13. Pinterest
runTest(
    "Pinterest Pin",
    '<p>https://cz.pinterest.com/pin/123456789/</p>',
    '/a data-pin-do="embedPin".*href="https:\/\/cz.pinterest.com\/pin\/123456789\/"/'
);
