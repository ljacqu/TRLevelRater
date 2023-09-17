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

$arg = trim(filter_input(INPUT_GET, 'arg', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR));

if (empty($arg)) {
  die(toResultJson('Specify a level (e.g. !rating madubu), or run !ratings to see all'));
}

$levelTexts = findLevel($data_levels, $arg);
if ($levelTexts === null) {
  die(toResultJson('Unknown level! Use the full name, most relevant word, or the first three letters to identify a level (e.g. "tinnos", "cra", "rx tech")'));
}
$levelName = $levelTexts[0];
$levelId = $levelTexts[1];

$db = new DatabaseHandler();

$avgAndCount = $db->getAverage($levelId);

if ((int) $avgAndCount['cnt'] === 0) {
  echo toResultJson($levelName . ' does not have any ratings yet. Rate it from 1 to 5 with !rate');
} else if ((int) $avgAndCount['cnt'] === 1) {
  echo toResultJson($levelName . ' has been rated once, namely with ' . round($avgAndCount['avg'], 0) . '/5');
} else {
  echo toResultJson($levelName . ' has a rating of ' . round($avgAndCount['avg'], 2) . '/5 (total ' . $avgAndCount['cnt'] . ' ratings)');
}
