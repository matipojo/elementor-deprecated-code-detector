<?php

const WP_ORG_API = 'https://api.wordpress.org/plugins/info/1.2/';
const WP_ORG_QUERY = [
    'action' => 'query_plugins',
    'request' => [
        'search' => 'elementor',
        'page' => 1,
        'per_page' => 101,
        'fields' => [
            'description' => false,
            'ratings' => false,
            'tags' => false,
            'icons' => false,
        ],
    ]
];

$response =  file_get_contents(WP_ORG_API . '?' . http_build_query(WP_ORG_QUERY));
$data = json_decode($response, true);
$slugs = array_column($data['plugins'], 'slug');

// remove elementor
$key = array_search('elementor', $slugs);
unset($slugs[$key]);

// for each plugin, run composer require with * version
foreach ($slugs as $slug) {
    $command = 'composer require wpackagist-plugin/' . $slug . ':*';
    echo $command . PHP_EOL;
    exec($command);
}
