<!DOCTYPE html>
<html>
<head>
  <title>TR3 Level Ratings</title>
  <style>
    body, table {
      font-family: Arial;
      font-size: 10pt;
    }
    table {
      border-collapse: collapse;
    }
    td, th {
      border: 1px solid #000;
      padding: 2px;
    }
    td {
      text-align: right;
    }
    .name {
      text-align: left;
    }

  </style>
</head>
<body>
<h2>TR3 level ratings</h2>

  <?php
  require 'levels.php';
  require 'Configuration.php';
  require 'DatabaseHandler.php';

  $db = new DatabaseHandler();
  $levelRatings = getLevelRatingsByLevelId($db);

  $user = filter_input(INPUT_GET, 'me', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
  if (!empty($user)) {
    $userRatings = [];
    foreach ($db->getRatings(strtolower($user)) as $ratingEntry) {
      $userRatings[$ratingEntry['level']] = $ratingEntry['rating'];
    }
  }

  echo '<table><tr><th>Level</th><th>Average</th>' 
    . (empty($userRatings) ? '' : '<th>' . htmlspecialchars($user) . '</th>')
    . '<th>Total ratings</th></tr>';

  foreach ($data_levels as $levelTexts) {
    $levelName = $levelTexts[0];
    $levelId   = $levelTexts[1];

    $avg = isset($levelRatings[$levelId]) ? round($levelRatings[$levelId]['avg'], 2) : null;
    $cnt = isset($levelRatings[$levelId]) ? $levelRatings[$levelId]['cnt'] : null;

    echo "<tr><td class='name'>$levelName</td>"
       . "<td style='background-color: " . getColorForRating($avg) . "'>$avg</td>";
    if (!empty($userRatings)) {
      $levelRating = $userRatings[$levelId] ?? null;
      echo "<td style='background-color: " . getColorForRating($levelRating) . "'>$levelRating</td>";
    }
    echo "<td>" . ($cnt ?? 0) . "</td></tr>";
  }

  function getLevelRatingsByLevelId($db) {
    $db = new DatabaseHandler();
    $ratingsByLevelId = [];
    foreach ($db->getAverages() as $row) {
      $ratingsByLevelId[$row['level']] = $row;
    }
    return $ratingsByLevelId;
  }

  function getColorForRating($rating) {
    if (!$rating) {
      return '#ccc';
    }

    $col1 = [255,   0,   0];
    $col3 = [255, 255,   0];
    $col5 = [0,   255,   0];

    return $rating <= 3
      ? interpolateColor($rating, 1, 3, $col1, $col3)
      : interpolateColor($rating, 3, 5, $col3, $col5);
  }

  function interpolateColor($rating, $min, $max, $color1, $color2) {
    $factor = ($rating - $min) / ($max - $min);

    $r = round($color1[0] + ($color2[0] - $color1[0]) * $factor);
    $g = round($color1[1] + ($color2[1] - $color1[1]) * $factor);
    $b = round($color1[2] + ($color2[2] - $color1[2]) * $factor);
    return "rgb($r $g $b)";
  }
  ?>
</table>
</body>
</html>

