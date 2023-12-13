<?php

session_start();

if (!isset($_SESSION['twitch_name'])) {
  die('Error: You are not logged in');
}

require 'Configuration.php';
require 'DatabaseHandler.php';
require 'Level.php';
require 'LevelHolder.php';

$rating = (float) filter_input(INPUT_POST, 'rating', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
$levelName = filter_input(INPUT_POST, 'level', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);

if (!$rating) {
  die('Error: Missing rating');
} else if (!$levelName) {
  die('Error: Missing level');
}

if ($rating < 1) {
  die('Error: Rating must be between 1.0 and 5.0');
} else if ($rating > 5) {
  die('Error: Rating must be between 1.0 and 5.0');
}
$rating = round($rating, 1);

$level = LevelHolder::findLevel($levelName);
if (!$level) {
  die('Error: Unknown level');
}

$db = new DatabaseHandler();
$db->addOrUpdateRating($_SESSION['twitch_name'], $level->aliases[0], $rating);
echo 'Success';
