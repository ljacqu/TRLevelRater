<!DOCTYPE html>
<html lang="en">
<head>
  <title>Tomb Raider Level Ratings</title>
  <link rel="stylesheet" href="./assets/style.css" />
</head>
<body>
<h1>Tomb Raider level ratings</h1>

  <?php
  require 'Configuration.php';
  require 'DatabaseHandler.php';
  require 'LevelHolder.php';

  //
  // Get average rating, and user rating if desired
  //
  $db = new DatabaseHandler();
  $levelRatings = getLevelRatingsByLevelId($db);

  $user = filter_input(INPUT_GET, 'me', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
  if (!empty($user)) {
    $userRatings = [];
    foreach ($db->getRatings(strtolower($user)) as $ratingEntry) {
      $userRatings[$ratingEntry['level']] = $ratingEntry['rating'];
    }
  }

  //
  // Create level data entries
  //
  $levelData = []; // TODO: Rename variable
  foreach (LevelHolder::getLevels() as $level) {
    $levelId = $level->aliases[0];

    $avg = isset($levelRatings[$levelId]) ? round($levelRatings[$levelId]['avg'], 2) : null;
    $cnt = isset($levelRatings[$levelId]) ? $levelRatings[$levelId]['cnt'] : null;
    $userRating = empty($userRatings) ? null : ($userRatings[$levelId] ?? null);
    $difference = ($userRating && $avg) ? ($userRating - $avg) : null;

    $levelData[$level->game] = $levelData[$level->game] ?? [];

    $levelData[$level->game][] = [
      'name' => $level->name,
      'avg' => $avg,
      'cnt' => $cnt,
      'user' => $userRating,
      'diff' => $difference
    ];
  }

  //
  // Sort if needed
  //
  $sort = filter_input(INPUT_GET, 'sort', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
  foreach ($levelData as &$gameRatings) {
    switch ($sort) {
      case 'avg': sortArrayByProperty($gameRatings, 'avg'); break;
      case 'cnt': sortArrayByProperty($gameRatings, 'cnt'); break;
      case 'user': sortArrayByProperty($gameRatings, 'user'); break;
      case 'diff': sortArrayByProperty($gameRatings, 'diff'); break;
    }
  }

  //
  // Output table and columns
  //
  $columns = [];
  $columns[] = ['Level', 'level'];
  if (!empty($userRatings)) {
    $columns[] = [$user, 'user'];
    $columns[] = ['Difference', 'diff'];
  }
  $columns[] = ['Average', 'avg'];
  $columns[] = ['Total ratings', 'cnt'];

  $sortedColumn = 'Level';
  foreach ($columns as $column) {
    if ($sort === $column[1]) {
      $sortedColumn = $column[0];
      break;
    }
  }

  foreach ($levelData as $game => $levels) {
    echo '<h2>' . str_replace('TR', 'Tomb Raider ', $game) . '</h2>';
    echo "<table><tr>";
    $linkAddition = empty($userRatings) ? '' : '&amp;me=' . urlencode($user);
    foreach ($columns as $column) {
      if ($sortedColumn === $column[0]) {
        echo '<th>' . htmlspecialchars($column[0]) . ' ↓</th>';
      } else {
        echo '<th><a href="?sort=' . $column[1] . $linkAddition . '">' . htmlspecialchars($column[0]) . '</a></th>';
      }
    }
    echo '</tr>';

    //
    // Output rows for levels
    //
    foreach ($levels as $level) {
      echo "<tr><td class='name'>" . htmlspecialchars($level['name']) . "</td>";
      if (!empty($userRatings)) {
        echo '<td style="background-color: ' . getColorForRating($level['user']) . '">' . formatNumber($level['user']) . '</td>';
        echo '<td style="background-color: ' . getColorForRatingDifference($level['diff']) . '">' . formatNumber($level['diff'], true) . '</td>';
      }
      echo '<td style="background-color: ' . getColorForRating($level['avg']) . '">' . formatNumber($level['avg']) . '</td>';
      echo '<td>' . $level['cnt'] . '</td></tr>';
    }
    echo '</table>';
  }

  // -------------
  // Functions
  // -------------

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

  function getColorForRatingDifference($difference) {
    if (!$difference) {
      return '#ccc';
    }

    $colM2 = [255,   0,   0];
    $colM1 = [255, 122, 122];
    $col0  = [255, 255, 255];
    $col1  = [122, 255, 122];
    $col2  = [  0, 255,   0];
    if ($difference < -2) {
      return rgbArrayToCssColor($colM2[0], $colM2[1], $colM2[2]);
    } else if (-2 <= $difference && $difference <= -1) {
      return interpolateColor($difference, -2, -1, $colM2, $colM1);
    } else if (-1 <= $difference && $difference <= 0) {
      return interpolateColor($difference, -1, 0, $colM1, $col0);
    } else if (0 <= $difference && $difference <= 1) {
      return interpolateColor($difference, 0, 1, $col0, $col1);
    } else if (1 <= $difference && $difference <= 2) {
      return interpolateColor($difference, 1, 2, $col1, $col2);
    }
    // $difference > 2
    return rgbArrayToCssColor($col2[0], $col2[1], $col2[2]);
  }

  function interpolateColor($rating, $min, $max, $color1, $color2) {
    $factor = ($rating - $min) / ($max - $min);

    $r = round($color1[0] + ($color2[0] - $color1[0]) * $factor);
    $g = round($color1[1] + ($color2[1] - $color1[1]) * $factor);
    $b = round($color1[2] + ($color2[2] - $color1[2]) * $factor);
    return "rgb($r $g $b)";
  }

  function rgbArrayToCssColor($r, $g, $b) {
    return "rgb($r $g $b)";
  }

  function sortArrayByProperty(&$arr, $propertyName) {
    usort($arr, function ($entry1, $entry2) use ($propertyName) {
      $prop1 = $entry1[$propertyName];
      $prop2 = $entry2[$propertyName];

      // Nulls last
      if ($prop1 === null && $prop2 !== null) {
        return 1;
      } else if ($prop1 !== null && $prop2 === null) {
        return -1;
      }

      return ($prop1 == $prop2) ? 0 : ($prop1 > $prop2 ? -1 : 1);
    });
  }

  function formatNumber($number, $addPlusIfPositive=false) {
    if ($number === null) {
      return $number;
    }
    $number = number_format($number, 2);
    return ($addPlusIfPositive && $number > 0) ? '+' . $number : $number;
  }
  ?>

</body>
</html>

