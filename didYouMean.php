<?php
include "vendor/autoload.php";
use Zend\Http\Client as ZendClient;

$term = strtolower($_GET["term"] ?? $argv[1]);

$elasticsearch = sprintf('%s:%d', getenv("ES_HOST") ?: "localhost", getenv("ES_PORT") ?: 9200);
$index = getenv("ES_INDEX") ?: "company";
$type = getenv("ES_TYPE") ?: "uk";

$url = sprintf("http://%s/%s/_search", $elasticsearch, $index);
$client = new ZendClient($url);
$client->setMethod('POST');
$query = '
{
  "suggest": {
    "didYouMean": {
      "text": "'.$term.'.*",
      "phrase": {
        "field": "did_you_mean"
      }
    }
  },
  "query": {
    "multi_match": {
      "query": "'.$term.'.*",
      "fields": [
        "name",
        "title"
      ]
    }
  }
}
';
$client->setRawBody($query);
$response = $client->send();
$body = $response->getBody();
$data = json_decode($body);

$hits = [];
foreach ($data->hits->hits as $hit) {
    $hits[] = $hit->_source->name;
}

$suggestions = [];
foreach ($data->suggest->didYouMean as $didYouMean) {
    array_map(function($suggestion) use (&$suggestions) {
        $suggestions[] = $suggestion->text;
    }, $didYouMean->options);
}

header('Content-Type: application/json');
echo json_encode(
    [
        "hits" => $hits,
        "suggestions" => $suggestions,
    ]
);
//echo json_encode($autocomplete);
//echo $body;
