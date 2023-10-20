<?php

require 'Configuration.php';
require 'functions.php';
require 'DatabaseHandler.php';
require 'LevelHolder.php';

header('Content-Type: application/json; charset=utf-8');

$secret = filter_input(INPUT_GET, 'secret', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
if ($secret !== Configuration::API_SECRET) {
  die(toResultJson('Error: Invalid API secret'));
}

$arg = trim(filter_input(INPUT_GET, 'arg', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR));

if (empty($arg)) {
  die(toResultJson('Specify a level (e.g. !rating madubu), or run !ratings to see all'));
}

$level = LevelHolder::findLevel($arg);
if ($level === null) {
  die(toResultJson('Unknown level! Use the full name, most relevant word, or the first three letters to identify a level (e.g. "tinnos", "cra", "rx tech")'));
}

$db = new DatabaseHandler();
$avgAndCount = $db->getAverage($level->getId());

if ((int) $avgAndCount['cnt'] === 0) {
  echo toResultJson($level->name . ' does not have any ratings yet. Rate it from 1 to 5 with !rate');
} else if ((int) $avgAndCount['cnt'] === 1) {
  echo toResultJson($level->name . ' has been rated once, namely with ' . round($avgAndCount['avg'], 0) . '/5');
} else {
  echo toResultJson($level->name . ' has a rating of ' . round($avgAndCount['avg'], 2) . '/5 (total ' . $avgAndCount['cnt'] . ' ratings)');
}
