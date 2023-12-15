  <?php
  session_start();

  require 'Configuration.php';
  require 'DatabaseHandler.php';
  require 'LevelHolder.php';
  require './assets/Page.php';

  Page::outputStart('Tomb Raider Level Ratings');
  echo '<h1>Tomb Raider level ratings</h1>';

  $user = filter_input(INPUT_GET, 'me', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
  $sort = filter_input(INPUT_GET, 'sort', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
  outputTableInfoAndLinks($user, $sort);

  //
  // Get average rating, and user rating if desired
  //
  $db = new DatabaseHandler();
  $levelRatings = getLevelRatingsByLevelId($db);

  $userRatings = [];
  if (!empty($user)) {
    foreach ($db->getRatings(strtolower($user)) as $ratingEntry) {
      $userRatings[$ratingEntry['level']] = $ratingEntry['rating'];
    }
  }

  //
  // Create level data entries
  //
  $levelsByGame = createAndGroupLevelEntries($levelRatings, $userRatings, !isset($_GET['global']));

  //
  // Sort if needed
  //

  foreach ($levelsByGame as &$gameRatings) {
    switch ($sort) {
      case 'avg':
      case 'cnt':
      case 'user':
      case 'diff':
        sortArrayByProperty($gameRatings, $sort);
        break;
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

  $linkAddition = (empty($userRatings) ? '' : '&amp;me=' . urlencode($user))
    . (isset($_GET['global']) ? '&amp;global' : '');
  echo '<table>';
  foreach ($levelsByGame as $game => $levels) {
    echo '<tr><td colspan="' . count($columns) . '" class="gametitle"><h2 id="' . $game . '">'
      . str_replace('TR', 'Tomb Raider ', $game)
      . '</h2></td></tr><tr class="header">';

    foreach ($columns as $column) {
      if ($sortedColumn === $column[0]) {
        echo '<td>' . htmlspecialchars($column[0]) . ' ↓</td>';
      } else {
        echo '<td><a href="?sort=' . $column[1] . $linkAddition . '#' . $game . '">' . htmlspecialchars($column[0]) . '</a></td>';
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
  }
  echo '</table>';

  Page::outputEnd();

  // -------------
  // Functions
  // -------------

  function getLevelRatingsByLevelId(DatabaseHandler $db): array {
    $ratingsByLevelId = [];
    foreach ($db->getAverages() as $row) {
      $ratingsByLevelId[$row['level']] = $row;
    }
    return $ratingsByLevelId;
  }

  function createAndGroupLevelEntries(array $levelRatings, array $userRatings, bool $groupByGame): array {
    $levelsByGame = [];
    foreach (LevelHolder::getLevels() as $level) {
      $levelId = $level->aliases[0];
      $group = $groupByGame ? $level->game : 'Ratings';
      $name = $level->name . ($groupByGame ? '' : " ($level->game)");

      $levelRating = $levelRatings[$levelId] ?? null;
      $avg = $levelRating ? round($levelRating['avg'], 2) : null;
      $cnt = $levelRating ? $levelRating['cnt'] : null;
      $userRating = $userRatings[$levelId] ?? null;
      $difference = ($userRating && $avg) ? ($userRating - $avg) : null;

      $levelsByGame[$group] = $levelsByGame[$group] ?? [];

      $levelsByGame[$group][] = [
        'name' => $name,
        'avg' => $avg,
        'cnt' => $cnt,
        'user' => $userRating,
        'diff' => $difference
      ];
    }
    return $levelsByGame;
  }

  function getColorForRating(?float $rating): string {
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

  function getColorForRatingDifference(?float $difference): string {
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

  function interpolateColor(?float $rating, float $min, float $max, array $color1, array $color2): string {
    $factor = ($rating - $min) / ($max - $min);

    $r = round($color1[0] + ($color2[0] - $color1[0]) * $factor);
    $g = round($color1[1] + ($color2[1] - $color1[1]) * $factor);
    $b = round($color1[2] + ($color2[2] - $color1[2]) * $factor);
    return "rgb($r $g $b)";
  }

  function rgbArrayToCssColor(int $r, int $g, int $b): string {
    return "rgb($r $g $b)";
  }

  function sortArrayByProperty(array &$arr, string $propertyName): void {
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

  function formatNumber(?float $number, bool $addPlusIfPositive=false): ?string {
    if ($number === null) {
      return null;
    }
    $number = number_format($number, 2);
    return ($addPlusIfPositive && $number > 0) ? '+' . $number : $number;
  }

  function outputTableInfoAndLinks(?string $user, ?string $sort): void {
    if (isset($_SESSION['twitch_name'])) {
      $nameEscaped = htmlspecialchars($_SESSION['twitch_name']);
      echo "<p>You are logged in as <b>$nameEscaped</b>. <a href='webrate.php'>Edit your ratings</a></p>";
    } else {
      echo '<p>You can log in with Twitch to submit your ratings! <a href="twitchconnect.php">Connect with Twitch</a></p>';
    }

    echo '<p>Click on any table header to sort by it. ';
    if (isset($_SESSION['twitch_name'])) {
      $linkQueryBegin = '?'
        . (isset($_GET['global']) ? 'global' : 'f')
        . ($sort ? '&amp;sort=' . urlencode($sort) : '');
      if ($_SESSION['twitch_name'] === $user) {
        echo "<a href='$linkQueryBegin'>Hide your ratings</a>";
      } else {
        $linkWithUser = $linkQueryBegin . '&amp;me=' . urlencode($_SESSION['twitch_name']);
        echo "<a href='$linkWithUser'>Show your ratings</a>";
      }
    }
    echo '</p><p>';

    $pageQuery = ($user ? '&amp;me=' . urlencode($user) : '')
      . ($sort ? '&amp;sort=' . urlencode($sort) : '');
    if (isset($_GET['global'])) {
      echo '<a href="?f' . $pageQuery . '">Group ratings by game</a>';
    } else {
      echo '<a href="?global' . $pageQuery . '">Show all ratings in same table</a>';
    }
    echo '</p>';
  }
