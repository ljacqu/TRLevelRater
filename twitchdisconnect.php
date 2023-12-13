<?php

session_start();

if (!isset($_SESSION['twitch_name'])) {
  echo <<<HTML
No account is associated with your current session. You can connect a Twitch account <a href="twitchconnect.php">here</a>.
HTML;

}

if (!isset($_POST['logout'])) {
  echo <<<HTML
 <h1>Disconnect account</h1>
 <p>Please click the button below to disconnect your account.
    This will make this page forget about your Twitch account.
    You can also remove the connection from your
       <a href="https://www.twitch.tv/settings/connections" target="_blank">Twitch profile</a> if you want.</p>
 <form method="post">
   <input type="submit" value="Disconnect account" />
  </form>
 
HTML;
} else {
  session_destroy();
  echo <<<HTML
<h1>Account disconnected</h1>
The association to your Twitch account has been removed. Thanks for providing some TR ratings!
You can also remove the connection from your
       <a href="https://www.twitch.tv/settings/connections" target="_blank">Twitch profile</a> if you want.
HTML;
}
