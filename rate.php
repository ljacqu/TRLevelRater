<?php

require 'Configuration.php';
require 'functions.php';
require 'DatabaseHandler.php';
require 'levels.php';

header('Content-Type: application/json; charset=utf-8');

$secret = filter_input(INPUT_GET, 'secret', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
if ($secret !== Configuration::API_SECRET) {
  die(toResultJson('Error: Invalid API secret'));
}

$arg = trim( filter_input(INPUT_GET, 'arg', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR) );

if (empty($arg)) {
  die(toResultJson('Rate levels from 1 to 5, e.g. !rate jungle 4. See ratings with !rating jungle'));
}

if (!preg_match('/^([a-z0-9_ -]+) ([1-5])$/i', $arg, $matches)) {
  die(toResultJson('Rate levels from 1 to 5, e.g. !rate thames 4 or !rate rx tech 3'));
}

$user = extractUser();
if (empty($user)) {
  die(toResultJson('Error! Failed to get user'));
}

$levelTexts = findLevel($data_levels, trim($matches[1]));
if ($levelTexts === null) {
  die(toResultJson('Unknown level! Use the full name, most relevant word, or the first three letters to identify a level (e.g. "tinnos", "cra", "rx tech")'));
}
$levelName = $levelTexts[0];
$levelId   = $levelTexts[1];

$db = new DatabaseHandler();

$existingRating = $db->getRating($user, $levelId);
$oldRating = empty($existingRating) ? null : (int) $existingRating[0];
$previousRatingText = empty($existingRating) ? '' : ' (old rating: ' . $oldRating . ')';

$rating = (int) $matches[2];
if ($oldRating === $rating) {
  die(toResultJson('@' . $user . ', your rating for ' . $levelName . ' is already ' . $rating . '/5 :D'));
}

$db->addOrUpdateRating($user, $levelId, (int) $matches[2]);
if ($oldRating) {
  if ($oldRating > $rating) {
    $emojis = ['🔽', '📉', '👇'];
  } else {
    $emojis = ['⬆️', '💹', '🚀'];
  }

  $emoji = $emojis[rand(0, count($emojis) - 1)];
  echo toResultJson($user . ' changed their rating of ' . $levelName . ' from ' . $oldRating . '/5 to ' . $rating . '/5 ' . $emoji);
} else {
  echo toResultJson($user . ' rated ' . $levelName . ' ' . $matches[2] . '/5');
}
