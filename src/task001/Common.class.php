<?php

class Common {
  
  public static function sendResponse($code, $data = [])
  {
    http_response_code($code);
  
    if (gettype($data) === 'string') {
      echo $data;
    } else {
      header('Content-type: application/json');
      echo json_encode($data);
    }
    exit;
  }
}
