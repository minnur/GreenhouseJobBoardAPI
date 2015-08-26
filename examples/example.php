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

// The method returns a list of all of your organization's
// departments and jobs, grouped by office.
$offices = $greenhouse->getOffices();

// The method returns a list of your organization's departments
// and jobs for the given [OFFICE_ID].
$office = $greenhouse->getOffice([OFFICE_ID]);

// The method returns a list of your organization's
// departments and jobs. 
$departments = $greenhouse->getDepartments();

//  The method returns a list of jobs for a given [DEPARTMENT_ID].
$department = $greenhouse->getDepartment([DEPARTMENT_ID]);

// The method returns the list of all jobs, with or
// without description. 
$jobs = $greenhouse->getJobs(true);

// The method returns a single job corresponding to the given [JOB_ID].
// Setting second parameter to `true` will include the list
// of job application fields [optional]
$job = $greenhouse->getJob([JOB_ID], true);

// The board method returns your organization's name
// and job board content.
$board = $greenhouse->getBoard();
