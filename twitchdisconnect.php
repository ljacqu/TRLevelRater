<?php
session_start();

require './assets/Page.php';

if (!isset($_SESSION['twitch_name'])) {
  Page::outputStart('No Twitch account associated');
  echo <<<HTML
    <h1>Disconnect Twitch account</h1>
    No account is associated with your current session. You can connect a Twitch account <a href="twitchconnect.php">here</a>.
    <br /><a href="index.php">Back to the ratings</a>
HTML;
  Page::outputEnd();
  exit;
}


if (!isset($_POST['logout'])) {
  Page::outputStart('Disconnect Twitch account');
  echo <<<HTML
 <h1>Disconnect account</h1>
 <p>Click the button below to make this page forget about your Twitch account.
    You can also remove the connection from your
       <a href="https://www.twitch.tv/settings/connections" target="_blank">Twitch profile</a> if you want.</p>
 <form method="post">
   <input type="submit" value="Disconnect account" />
   <input type="hidden" name="logout" value="1" />
  </form>
  <p><a href="index.php">Back to the ratings</a></p>
HTML;
} else {
  session_destroy();
  unset($_SESSION['twitch_name']);
  unset($_SESSION['twitch_image']);

  Page::outputStart('Disconnect Twitch account');
  echo <<<HTML
<h1>Account disconnected</h1>
<p>The association to your Twitch account has been removed. Thanks for providing some TR ratings!
You can also remove the connection from your
       <a href="https://www.twitch.tv/settings/connections" target="_blank">Twitch profile</a> if you want.</p>
<p><a href="index.php">Back to the ratings</a></p>
HTML;
}

Page::outputEnd(false);
