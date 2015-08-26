<?php

/**
 * @file
 * GreenhouseJobBoardAPI usage examples.
 */

require '../vendor/autoload.php';
require '../src/GreenhouseJobBoardAPI.php';

use \GreenhouseJobBoardAPI\GreenhouseJobBoardAPI;

$api_url = "https://api.greenhouse.io/v1/boards/{{CLIENT_CODE}}/embed/";
$api_key = 'Greenhouse API Key'; // required to post application forms.

$greenhouse = new GreenhouseJobBoardAPI($api_url, $api_key);

$post_data = [];

$job = $greenhouse->getJob([JOB_ID], true);

$greenhouse->submitApplication($post_data);
