<?php
include "vendor/autoload.php";
use Zend\Http\Client as ZendClient;

$elasticsearch = sprintf('%s:%d', getenv("ES_HOST") ?: "localhost", getenv("ES_PORT") ?: 9200);
$index = getenv("ES_INDEX") ?: "company";
$type = getenv("ES_TYPE") ?: "uk";

$settings = '
{
  "settings": {
    "index": {
      "analysis": {
        "filter": {
          "stemmer": {
            "type": "stemmer",
            "language": "english"
          },
          "autocompleteFilter": {
            "max_shingle_size": "5",
            "min_shingle_size": "2",
            "type": "shingle"
          },
          "stopwords": {
            "type": "stop",
            "stopwords": [
              "_english_"
            ]
          }
        },
        "analyzer": {
          "didYouMean": {
            "filter": [
              "lowercase"
            ],
            "char_filter": [
              "html_strip"
            ],
            "type": "custom",
            "tokenizer": "standard"
          },
          "autocomplete": {
            "filter": [
              "lowercase",
              "autocompleteFilter"
            ],
            "char_filter": [
              "html_strip"
            ],
            "type": "custom",
            "tokenizer": "standard"
          },
          "default": {
            "filter": [
              "lowercase",
              "stopwords",
              "stemmer"
            ],
            "char_filter": [
              "html_strip"
            ],
            "type": "custom",
            "tokenizer": "standard"
          }
        }
      }
    }
  }
}
';

$url = sprintf("http://%s/%s", $elasticsearch, $index);
$client = new ZendClient($url);
$client->setMethod('PUT');
$client->setRawBody($settings);
$client->send();
