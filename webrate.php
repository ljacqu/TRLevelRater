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
require './assets/Page.php';

$db = new DatabaseHandler();

Page::outputStart('Tomb Raider ratings');
echo '<h1>TR ratings</h1>
  <p>You are currently connected as <b>' . htmlspecialchars($_SESSION['twitch_name']) . '</b> and can add or edit
  ratings for TR levels below.</p>';

$userRatings = getUserRatingsByLevel($_SESSION['twitch_name'], $db);
$levelsByGame = getLevelsByGame($userRatings);
echo '<table>';
foreach ($levelsByGame as $game => $levels) {
  echo '<tr><td colspan="2" class="gametitle"><h2>' . str_replace('TR', 'Tomb Raider ', $game) . '</h2></td></tr>';
  echo '<tr class="header"><td>Level</td><td>Rating</td></tr>';
  foreach ($levels as $level) {
    $alias = $level['alias'];
    echo "<tr><td class='name'><label for='$alias'>" . htmlspecialchars($level['name']) . "</label></td>
              <td class='editablerating'>
                <input id='$alias' type='text' maxlength='3' value='{$level['rating']}' />
              </td></tr>";
  }
}
echo '</table>';
echo '<script src="./assets/editrating.js"></script><script>initRatingCells();</script>';

Page::outputEnd();

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
