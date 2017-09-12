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
  "size": 0,
  "aggs": {
    "autocomplete": {
      "terms": {
        "field": "autocomplete",
        "order": {
          "_count": "desc"
        },
        "include": {
          "pattern": "'.$term.'.*"
        }
      }
    }
  },
  "query": {
    "prefix": {
      "autocomplete": {
        "value": "'.$term.'"
      }
    }
  }
}
';
$client->setRawBody($query);
$response = $client->send();
$body = $response->getBody();
$data = json_decode($body);

$hits = $data->hits;
$autocomplete = $data->aggregations->autocomplete->buckets;

$response = [];
foreach ($autocomplete as $phrase) {
    $response[] = [
        "label" => $phrase->key,
        "category" => "dir"
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
//echo json_encode($autocomplete);
//echo $body;
