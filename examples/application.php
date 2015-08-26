<?php

/**
 * @file
 * GreenhouseJobBoardAPI usage examples.
 */

require '../vendor/autoload.php';
require '../src/GreenhouseJobBoardAPI.php';

use \GreenhouseJobBoardAPI\GreenhouseJobBoardAPI;

$api_url = "https://api.greenhouse.io/v1/boards/{{CLIENT_CODE}}/embed/";

$greenhouse = new GreenhouseJobBoardAPI($api_url);

$post_data = [];

$job = $greenhouse->getJob([JOB_ID], true);

$greenhouse->submitApplication($post_data);
