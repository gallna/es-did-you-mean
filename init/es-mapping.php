<?php
include "vendor/autoload.php";
use Zend\Http\Client as ZendClient;

$elasticsearch = sprintf('%s:%d', getenv("ES_HOST") ?: "localhost", getenv("ES_PORT") ?: 9200);
$index = getenv("ES_INDEX") ?: "company";
$type = getenv("ES_TYPE") ?: "uk";

$mapping = '
{
  "'.$type.'": {
    "properties": {
      "name": {
        "type": "string",
        "copy_to": [
          "did_you_mean",
          "autocomplete"
        ]
      },
      "title": {
        "type": "string",
        "copy_to": [
          "autocomplete",
          "did_you_mean"
        ]
      },
      "autocomplete": {
        "type": "string",
        "analyzer": "autocomplete"
      },
      "did_you_mean": {
        "type": "string",
        "analyzer": "didYouMean"
      }
    }
  }
}
';


$url = sprintf("http://%s/%s/_mapping/%s", $elasticsearch, $index, $type);
$client = new ZendClient($url);
$client->setMethod('PUT');
$client->setRawBody($mapping);
$client->send();
