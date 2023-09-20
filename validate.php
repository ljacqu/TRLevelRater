<?php

require 'levels.php';

$seenNames = [];
foreach ($data_levels as $aliases) {
  foreach ($aliases as $alias) {
    if (array_search($alias, $seenNames, true) !== false) {
      die('Alias "' . htmlspecialchars($alias) .'" has already been used!');
    }
    array_push($seenNames, $aliases);
  }
}

echo 'Validated ' . count($seenNames) . ' level identifiers';
