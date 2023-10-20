<?php

require 'LevelHolder.php';

$seenNames = [];
foreach (LevelHolder::getLevels() as $level) {
  if (in_array(strtolower($level->name), $seenNames)) {
    die('Alias "' . htmlspecialchars($alias) . '" has already been used!');
  }

  foreach ($level->aliases as $alias) {
    if (in_array($alias, $seenNames)) {
      die('Alias "' . htmlspecialchars($alias) .'" has already been used!');
    }
    $seenNames[] = $level->aliases;
  }
  $seenNames[] = strtolower($level->name);
}

echo 'Validated ' . count($seenNames) . ' level identifiers';
