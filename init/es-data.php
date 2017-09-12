<?php
include "vendor/autoload.php";
use Zend\Http\Client as ZendClient;

$term = strtolower($_GET["term"] ?? $argv[1]);

$elasticsearch = sprintf('%s:%d', getenv("ES_HOST") ?: "localhost", getenv("ES_PORT") ?: 9200);
$index = getenv("ES_INDEX") ?: "company";
$type = getenv("ES_TYPE") ?: "uk";

$url = sprintf("http://%s/%s/%s", $elasticsearch, $index, $type);
$client = new ZendClient($url);
$client->setMethod('POST');

// use Faker to generate some data
$faker = Faker\Factory::create();

for ($i=0; $i<=(getenv("ES_COUNT") ?: 200); $i++) {
    $query["name"] = $faker->name;
    if (0 == ($i % 10)) echo $i.": ".$query["name"]."\n";
    $client->setRawBody(json_encode($query));
    $client->send();
}
