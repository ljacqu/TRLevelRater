<?php

function toResultJson($text) {
  return json_encode(['result' => $text], JSON_FORCE_OBJECT);
}

function extractUser() {
  if (isset($_SERVER['HTTP_NIGHTBOT_USER'])) {
    $nightbotUser = $_SERVER['HTTP_NIGHTBOT_USER'];
    return preg_replace('~^.*?name=([^&]+)&.*?$~', '\\1', $nightbotUser);
  }
  return null;
}
