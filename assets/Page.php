<?php

final class Page {

  private function __construct() {
  }

  static function outputStart($title): void {
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

  static function outputEnd(): void {
    echo '</body></html>';
  }
}
