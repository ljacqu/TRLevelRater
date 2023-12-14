<?php

final class Page {

  private function __construct() {
  }

  static function outputStart(string $title): void {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
 <head>
   <title>$title</title>
   <link rel="stylesheet" href="./assets/style.css" />
 </head>
 <body>
HTML;
    if (isset($_SESSION['twitch_image'])) {
      echo '<a href="webrate.php">'
        . '<img id="twitchpic" src="' . $_SESSION['twitch_image'] . '" alt="Twitch profile" title="You are connected as '
        . htmlspecialchars($_SESSION['twitch_name'], ENT_QUOTES) . '" /></a>';
    }
  }

  static function outputEnd(bool $includeDisconnectLink=true): void {
    if (isset($_SESSION['twitch_name'])) {
      echo "<div class='footer'>You are connected as <b>" . htmlspecialchars($_SESSION['twitch_name']) . "</b>";
      if ($includeDisconnectLink) {
        echo " &middot; <a href='twitchdisconnect.php'>Disconnect</a>";
      }
      echo '</div>';
    }
    echo '</body></html>';
  }
}
