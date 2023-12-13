<?php

session_start();

if (!isset($_SESSION['twitch_name'])) {
  header('Location: twitchconnect.php');
  exit;
}

require 'Configuration.php';
require 'Level.php';
require 'LevelHolder.php';
require 'DatabaseHandler.php';

$db = new DatabaseHandler();

echo '<link rel="stylesheet" href="./assets/style.css" />
<img src="' . htmlspecialchars($_SESSION['twitch_image']) . '" style="width: 70px; float: right; border-radius: 50%" title="Connected as ' . $_SESSION['twitch_name'] . '" />
<h1>TR ratings</h1>
  <p>You are currently connected as <b>' . htmlspecialchars($_SESSION['twitch_name']) . '</b> and can add or edit
  ratings for TR levels below.</p>';

$userRatings = getUserRatingsByLevel($_SESSION['twitch_name'], $db);
$levelsByGame = getLevelsByGame($userRatings);
foreach ($levelsByGame as $game => $levels) {
  echo '<h1>' . str_replace('TR', 'Tomb Raider ', $game) . '</h1>';
  echo '<table><tr><th>Level</th><th>Rating</th></tr>';
  foreach ($levels as $level) {
    echo '<tr><td>' . htmlspecialchars($level['name']) . '</td><td>' . $level['rating'] . '</td></tr>';
  }
  echo '</table>';
}

function getUserRatingsByLevel(string $user, DatabaseHandler $db): array {
  $ratingsByLevel = [];

  $userRatings = $db->getRatings(strtolower($user));
  foreach ($userRatings as $dbRow) {
    $ratingsByLevel[$dbRow['level']] = $dbRow['rating'];
  }
  return $ratingsByLevel;
}

function getLevelsByGame(array $ratingsByLevel): array {
  $result = [];

  foreach (LevelHolder::getLevels() as $level) {
    $result[$level->game] = $result[$level->game] ?? [];
    $result[$level->game][] = [
      'name' => $level->name,
      'alias' => $level->aliases[0],
      'rating' => $ratingsByLevel[$level->aliases[0]] ?? ''
    ];
  }
  return $result;
}
