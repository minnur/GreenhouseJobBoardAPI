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

foreach ($fields as $index => $field) {
  $required = !empty($field->required) ? $field->required : FALSE;
  $label = !empty($field->label) ? $field->label : '';
  $elements = !empty($field->fields) ? $field->fields : [];
  $type = !empty($field->type) ? $field->type : '';
  $eeoc_description = !empty($field->description) ? $field->description : '';
  if ($type == 'eeoc') {
   if (!empty($field->questions)) {

   $form['desciption_' . $index] = [
     'type'  => 'markup',
     'value' => $eeoc_description,
   ];

      foreach ($field->questions as $eeoc_field) {
        $required = !empty($eeoc_field->required) ? $eeoc_field->required : FALSE;
        $label = !empty($eeoc_field->label) ? $eeoc_field->label : '';
        $eeoc_elements = !empty($eeoc_field->fields) ? $eeoc_field->fields : [];
        foreach ($eeoc_elements as $element) {

          // Options for `select` and `checkboxes|radios` options.
          $options = [];
          if (!empty($element->values)) {
            foreach ($element->values as $option) {
              $options[$option->value] = $option->label;
            }
          }

          switch ($element->type) {

            // Respresent with an input of type file.
            case 'input_file' :
              $form[] = [
                'name'  => $element->name,
                'label' => $label,
                'type'  => 'file',
              ];
              break;

            // Respresent with an input of type text.
            case 'input_text' :
              $form[] = [
                'name'  => $element->name,
                'label' => $label,
                'type'  => 'text',
              ];
              break;

            // Respresent with a textarea.
            case 'textarea' :
              $form[] = [
                'name'  => $element->name,
                'label' => $label,
                'type'  => 'textarea',
              ];
              break;

            // Can be represented as either a set of radio buttons or a select.
            case 'multi_value_single_select' :
              $form[] = [
                'name'    => $element->name,
                'label'   => $label,
                'type'    => 'select',
                'options' => $options,
              ];
              break;

            // Can be represented as either a set of checkboxes or a multi-select.
            case 'multi_value_multi_select' :
              $form[] = [
                'name'    => $element->name,
                'label'   => $label,
                'type'    => 'checkboxes',
                'options' => $options,
              ];
              break;

          }
        }
      }
    }
  }
  else {
    foreach ($elements as $element) {

      // Options for `select` and `checkboxes|radios` options.
      $options = [];
      if (!empty($element->values)) {
        foreach ($element->values as $option) {
          $options[$option->value] = $option->label;
        }
      }

      switch ($element->type) {

        // Respresent with an input of type file.
        case 'input_file' :
          $form[] = [
            'name'  => $element->name,
            'label' => $label,
            'type'  => 'file',
          ];
          break;

        // Respresent with an input of type text.
        case 'input_text' :
          $form[] = [
            'name'  => $element->name,
            'label' => $label,
            'type'  => 'text',
          ];
          break;

        // Respresent with a textarea.
        case 'textarea' :
          $form[] = [
            'name'  => $element->name,
            'label' => $label,
            'type'  => 'textarea',
          ];
          break;

        // Can be represented as either a set of radio buttons or a select.
        case 'multi_value_single_select' :
          $form[] = [
            'name'    => $element->name,
            'label'   => $label,
            'type'    => 'select',
            'options' => $options,
          ];
          break;

        // Can be represented as either a set of checkboxes or a multi-select.
        case 'multi_value_multi_select' :
          $form[] = [
            'name'    => $element->name,
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

?>
<!-- Custom Application form builder example -->
<form method="post" enctype="multipart/form-data">
 <?php foreach ($form as $element) : ?>
    <div class="form-item">
     <label><?php echo $element['label']; ?></label>
     <div class="element">

        <?php if ($element['type'] == 'text' || $element['type'] == 'file') : ?>
          <input type="<?php print $element['type']; ?>" name="<?php print $element['name']; ?>">

        <?php elseif ($element['type'] == 'select') : ?>
          <select name="<?php print $element['name']; ?>">
           <?php foreach ($element['options'] as $key => $val) : ?>
             <option value="<?php print $key; ?>"><?php print $val; ?></option>
            <?php endforeach; ?>
          </select>

        <?php elseif ($element['type'] == 'checkboxes') : ?>
         <?php foreach ($element['options'] as $key => $val) : ?>
           <div class="checkbox-item"><input type="checkbox" name="<?php print $element['name']; ?>" value="<?php print $key; ?>"><?php print $val; ?></div>
          <?php endforeach; ?>

        <?php elseif ($element['type'] == 'textarea') : ?>
          <textarea name="<?php print $element['name']; ?>"></textarea>

        <?php elseif ($element['type'] == 'markup') : ?>
          <div class="markup">
           <?php print $element['value']; ?>
          </div>
        <?php endif; ?>

     </div>
    </div>
 <?php endforeach; ?>
 <div class="submit">
 <input type="submit" value="Apply">
 </div>
</form>
