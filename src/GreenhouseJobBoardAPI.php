<?php

/**
 * @file
 * Greenhouse Job Board API integration PHP library.
 * Author : Minnur Yunusov
 * Company: Chapter Three
 */

namespace GreenhouseJobBoardAPI;

use Curl\Curl;

/**
 * Greenhouse Job Board API integration class interface.
 */
interface InterfaceGreenhouseJobBoardAPI {

  /**
   * The method returns a list of all of your organization's departments and jobs, grouped by office.
   */
  public function getOffices();

  /**
   * The method returns a list of your organization's departments and jobs for the given $office_id.
   */
  public function getOffice($office_id);

  /**
   * The method returns a list of your organization's departments and jobs. 
   */
  public function getDepartments();

  /**
   * The method returns a list of jobs for a given $department_id.
   */
  public function getDepartment($department_id);

  /**
   * The method returns the list of all jobs, with or without description.
   * Setting the content querystring parameter to "true" will include
   * the job description in the response [optional].
   */
  public function getJobs($content = false);

  /**
   * The method returns a single job corresponding to the given $job_id.
   * Setting the $questions parameter to "true" will include the list 
   * of job application fields [optional]; these fields can be used to 
   * dynamically construct your own job application form. 
   */
  public function getJob($job_id, $questions = false);

  /**
   * The board method returns your organization's name and job board content.
   */
  public function getBoard();

  /**
   * Response callback.
   */
  public function response($response);

  /**
   * Send request to Greenhouse API.
   */
  public function request($method, $endpoint, Array $params = []);

  /**
   * Post request to Greenhouse API (Submit application form).
   */
  public function submitApplication(Array $data = []);

}

/**
 * Greenhouse integartion class.
 */
class GreenhouseJobBoardAPI implements InterfaceGreenhouseJobBoardAPI {

  /** @var (const) GreenhouseJobBoardAPI version */
  const VERSION = '0.0.2';

  /** @var (const) CRLF */
  const EOL = "\r\n";

  /** @var (string) Multipat data boundary unique string. */
  private $boundary;

  /** @var (string) Greenhouse board API URL */
  private $api_url = '';

  /** @var (string) Greenhouse API Key only used to submit applications */
  private $api_key = '';

  /** @var (object) HTTP client class. */
  private $client;

  /**
   * Implements __construct().
   */
  public function __construct($api_url, $api_key = '') {
    // Initialize cURL
    $this->client = new Curl();
    // Example: https://api.greenhouse.io/v1/boards/{{CLIENT_CODE}}/embed/
    $this->api_url = $api_url;
    $this->api_key = $api_key;
    $this->boundary = md5(uniqid() . microtime());
  }

  /**
   * The /offices method returns a list of all of your organization's
   * departments and jobs, grouped by office.
   */
  public function getOffices() {
    return $this->get('offices');
  }

  /**
   * The /office method returns a list of your organization's departments
   * and jobs for the given {{officeId}}
   */
  public function getOffice($office_id) {
    return $this->get('office',
      [
        'id' => $office_id
      ]
    );
  }

  /**
   * The /departments method returns a list of your organization's
   * departments and jobs.
   */
  public function getDepartments() {
    return $this->get('departments');
  }

  /**
   * The /department method returns a list of jobs for
   * a given {{departmentId}}.
   */
  public function getDepartment($department_id) {
    return $this->get('department',
      [
        'id' => $department_id
      ]
    );
  }

  /**
   * The /jobs method returns the list of all jobs, with or without
   * description. Setting the content querystring parameter to "true"
   * will include the job description in the response [optional].
   */
  public function getJobs($content = false) {
    return $this->get('jobs',
      [
        'content' => $content ? 'true' : 'false'
      ]
    );
  }

  /**
   * The /job method returns a single job corresponding to the 
   * given {{jobId}}. Setting the questions querystring parameter
   * to "true" will include the list of job application fields [optional];
   * these fields can be used to dynamically construct 
   * your own job application form.
   */
  public function getJob($job_id, $questions = false) {
    return $this->get('job',
      [
        'id'        => $job_id,
        'questions' => $questions ? 'true' : 'false'
      ]
    );
  }

  /**
   * The board method returns your organization's name and
   * job board content.
   */
  public function getBoard() {
    return $this->get();
  }

  /**
   * Get request to Greenhouse API.
   */
  private function get($method = '', Array $params = []) {
    $endpoint = $this->api_url . $method;
    return $this->request('get', $endpoint, $params);
  }

  /**
   * Get file information and its contents to upload.
   *
   * @param (string) $path Path to a file included in the POST request.
   *
   * @return (array) Associative array. The array contains information about a file.
   */
  protected function getFileInformation($path) {
    $file = pathinfo($path);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $path);
    $contents = file_get_contents($path);

    return [
      'name'      => $file['filename'],
      'filename'  => $file['basename'],
      'extension' => $file['extension'],
      'mimetype'  => $mimetype,
      'contents'  => $contents,
      'size'      => strlen($contents)
    ];
  }

  /**
   * Generate individual multipart data parts.
   *
   * @param (array) $attributes Associative array with information about each file (mimetype, filename, size).
   * @param (string) $mimetype Multipart mime type.
   * @param (string) $contents Contents of the multipart content chunk.
   *
   * @return (string) Raw HTTP multipart chunk formatted according to the RFC.
   *
   * @see https://www.ietf.org/rfc/rfc2388.txt
   */
  private function multipartPart(Array $attributes, $mimetype = null, $contents = null) {
    $multipart = '';
    $headers = [];
    foreach ($attributes as $name => $value) {
      $headers[] = $name . '="' . $value . '"';
    }
    // Generate multipart data and contents.
    $multipart .= '--' . $this->boundary . static::EOL;
    if (!empty($mimetype)) {
      $multipart .= 'Content-Type: ' . $mimetype . static::EOL;
    }
    $multipart .= 'Content-Disposition: form-data; ' . join('; ', $headers) . static::EOL;
    $multipart .= static::EOL . $contents . static::EOL;
    return $multipart;
  }

  /**
   * Finalize multipart data.
   *
   * @param (array) $multiparts Multipart data with its headers.
   *
   * @return (string) Raw HTTP multipart data formatted according to the RFC.
   *
   * @see https://www.ietf.org/rfc/rfc2388.txt
   */
  protected function multipartFinalize(Array $multiparts = []) {
    $contents = '';
    foreach ($multiparts as $multipart) {
      $contents .= $multipart;
    }
    $contents .= '--' . $this->boundary  . '--';
    $contents .= static::EOL;
    return $contents;
  }

  /**
   * Post request to Greenhouse API (Submit application form).
   */
  public function submitApplication(Array $data = []) {
    $multiparts = [];
    $url_components = parse_url($this->api_url);
    if (empty($this->api_key)) {
      throw new \Exception('API Key required to submit application forms.');
    }
    $endpoint = $url_components['scheme'] . '://' . $this->api_key . '@' . $url_components['host'] . '/v1/applications/';
    if (count($data)) {

      foreach ($data as $key => $value) {
        if (!empty($value['filepath'])) {
          // Get file information.
          $info = $this->getFileInformation($value['filepath']);
          // Add to multipart array.
          $multiparts[] = $this->multipartPart(
            [
              'filename'   => $info['basename'],
              'name'       => $info['filename'],
              'size'       => $info['size'],
            ],
            $info['mimetype'],
            $info['contents']
          );
        }
        else {
          $multiparts[] = $this->multipartPart(
            [
              'name' => $key
            ],
            NULL,
            $value
          );
        }
      }
      $post_data = $this->multipartFinalize($multiparts);
      $this->client->setHeader('Content-Type', 'multipart/form-data; boundary=' . $this->boundary);
      return $this->request('post', $endpoint, $post_data);
    }
    return FALSE;
  }

  /**
   * Response callback.
   * Override this method if you need to preprocess $reponse.
   */
  public function response($response) {
    return $response;
  }

  /**
   * Send request to Greenhouse API.
   */
  public function request($method, $endpoint, Array $params = []) {
    try {
      $response = $this->client->{$method}($endpoint, $params);
    }
    catch (\Exception $e) {
      echo 'Caught exception: ' .  $e->getMessage() . "\n";
    }
    // If error response.
    if (isset($response->error)) {
      throw new \Exception($response->error);
    }
    return $this->response($response);
  }

}
