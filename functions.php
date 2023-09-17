<?php

function toResultJson($text) {
  return json_encode(['result' => $text], JSON_FORCE_OBJECT);
}

function findLevel($levels, $text) {
  foreach ($levels as $levelTexts) {
    foreach ($levelTexts as $levelText) {
      if (strtolower($text) === strtolower($levelText)) {
        return $levelTexts;
      }
    }
  }
  return null;
}

function extractUser() {
  if (isset($_SERVER['HTTP_NIGHTBOT_USER'])) {
    $nightbotUser = $_SERVER['HTTP_NIGHTBOT_USER'];
    return preg_replace('~^.*?name=([^&]+)&.*?$~', '\\1', $nightbotUser);
  }
  return null;
}
