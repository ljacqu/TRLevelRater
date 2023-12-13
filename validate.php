<?php

require 'LevelHolder.php';

$seenNames = [];
foreach (LevelHolder::getLevels() as $level) {
  if (in_array(strtolower($level->name), $seenNames)) {
    die('Alias "' . htmlspecialchars($level->name) . '" has already been used!');
  }

  foreach ($level->aliases as $alias) {
    if (in_array($alias, $seenNames)) {
      die('Alias "' . htmlspecialchars($alias) .'" has already been used!');
    }
    $seenNames[] = $level->aliases;
  }
  $seenNames[] = strtolower($level->name);

  if (strlen($level->aliases[0]) > 10) {
    die('ID "' . $level->aliases[0] . '" is longer than 10 chars');
  }
}

echo 'Validated ' . count($seenNames) . ' level identifiers';
