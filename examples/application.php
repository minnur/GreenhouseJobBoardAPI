<?php

/**
 * @file
 * GreenhouseJobBoardAPI usage examples.
 * Custom Job Application form.
 */

require '../vendor/autoload.php';
require '../src/GreenhouseJobBoardAPI.php';

use \GreenhouseJobBoardAPI\GreenhouseJobBoardAPI;

$api_url = "https://api.greenhouse.io/v1/boards/{{CLIENT_CODE}}/embed/";
$api_key = 'Greenhouse API Key'; // required to post application forms.

$greenhouse = new GreenhouseJobBoardAPI($api_url, $api_key);

$post_data = [];

// Get job application fields by setting second argument to `true`.
$job = $greenhouse->getJob([JOB_ID], true);

$fields = [];

// `compliance' - United States Equal Employment Opportunity Commission fields
// such as Gender/Race/Veteran Status etc..
if (!empty($job->questions) && !empty($job->compliance)) {
  $fields = array_merge($job->questions, $job->compliance);
}
else {
  // Only job application fields.
  $fields = $job->questions;
}

$form = [];

foreach ($fields as $field) {
  $required = !empty($field['required']) ? $field['required'] : FALSE;
  $label = !empty($field['label']) ? $field['label'] : '';
  $elements = !empty($field['fields']) ? $field['fields'] : [];
  $type = !empty($field['type']) ? $field['type'] : '';
  if ($type == 'eeoc') {
    switch () {

    }
  }
  else {
    foreach ($elements as $element) {

      // Options for `select` and `checkboxes|radios` options.
      $options = [];
      if (!empty($element['values'])) {
      	foreach ($element['values'] as $option) {
          $options[$option->value] = $option->label;
      	}
      }

      switch ($element['type']) {

        // Respresent with an input of type file.
        case 'input_file' :
          $form[$element['name']] = [
            'label' => $label,
            'type'  => 'file',
          ];
          break;

        // Respresent with an input of type text.
        case 'input_text' :
          $form[$element['name']] = [
            'label' => $label,
            'type'  => 'text',
          ];
          break;

        // Respresent with a textarea.
        case 'textarea' :
          $form[$element['name']] = [
            'label' => $label,
            'type'  => 'textarea',
          ];
          break;

        // Can be represented as either a set of radio buttons or a select.
        case 'multi_value_single_select' :
          $form[$element['name']] = [
            'label'   => $label,
            'type'    => 'select',
            'options' => $options,
          ];
          break;

        // Can be represented as either a set of checkboxes or a multi-select.
        case 'multi_value_multi_select' :
          $form[$element['name']] = [
            'label'   => $label,
            'type'    => 'checkboxes',
            'options' => $options,
          ];
          break;

      }
    }
  }
}

$greenhouse->submitApplication($post_data);
