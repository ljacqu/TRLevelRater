<?php
session_start();

if (!isset($_SESSION['twitch_name'])) {
  header('Location: index.php');
  exit;
}

require 'Configuration.php';
require 'Level.php';
require 'LevelHolder.php';
require 'DatabaseHandler.php';
require './assets/Page.php';

$db = new DatabaseHandler();

Page::outputStart('Edit Tomb Raider ratings');
echo '<h1>Edit Tomb Raider ratings</h1>
  <p>You are currently connected as <b>' . htmlspecialchars($_SESSION['twitch_name']) . '</b>. Log out with the "Disconnect" link at the bottom of the page.</p>
  <p> &nbsp; &raquo; <a href="index.php?me=' . htmlspecialchars($_SESSION['twitch_name'], ENT_QUOTES) . '">Compare your ratings to the average</a></p>
<p>
 To add or edit your Tomb Raider level ratings, just change them in the fields below and they\'ll be saved :)
 The rating scale is 1.0 (worst) to 5.0 (best).
</p>';

$userRatings = getUserRatingsByLevel($_SESSION['twitch_name'], $db);
$levelsByGame = getLevelsByGame($userRatings);
echo '<table>';
foreach ($levelsByGame as $game => $levels) {
  echo '<tr><td colspan="2" class="gametitle"><h2>' . str_replace('TR', 'Tomb Raider ', $game) . '</h2></td></tr>';
  echo '<tr class="header"><td>Level</td><td>Your rating</td></tr>';
  foreach ($levels as $level) {
    $alias = $level['alias'];
    $rating = $level['rating'];
    echo "<tr><td class='name'><label for='$alias'>" . htmlspecialchars($level['name']) . "</label></td>"
      . "<td class='editablerating'><input id='$alias' type='text' maxlength='3' value='$rating' /></td></tr>";
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
