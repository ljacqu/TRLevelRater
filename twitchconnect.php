<?php

require 'Configuration.php';

session_start();

if (isset($_SESSION['twitch_name'])) {
  echo '<h1>You are already connected as ' . htmlspecialchars($_SESSION['twitch_name']) . '</h1>';
  echo 'You can remove your account association from this page by <a href="twitchdisconnect.php">disconnecting</a>.';
} else if (empty(Configuration::TWITCH_CLIENT_ID) || empty(Configuration::TWITCH_CLIENT_SECRET)) {
  echo '<p>Twitch app information is not available.
    <br />Please contact the website administrator.</p>';
} else if (isset($_GET['code'])) {
  echo retrieveToken();
} else {
  outputConnectLinkAndInfo();
}

echo '</body></html>';

// -------------
// FUNCTIONS
// -------------

// Docs: https://dev.twitch.tv/docs/authentication/getting-tokens-oauth/#authorization-code-grant-flow
function retrieveToken(): string {
  $code = filter_input(INPUT_GET, 'code', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
  if (empty($code)) {
    return '<b>Error:</b> Invalid code value.';
  }

  $data = [
    'client_id' => Configuration::TWITCH_CLIENT_ID,
    'client_secret' => Configuration::TWITCH_CLIENT_SECRET,
    'code' => $code,
    'grant_type' => 'authorization_code',
    'redirect_uri' => obtainSelfLink()
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://id.twitch.tv/oauth2/token');
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

  $response = curl_exec($ch);

  // Debugging? Uncomment the line below to see what was returned.
  // var_dump($response);

  if (curl_errno($ch)) {
    return '<b>Error:</b> Could not get token. cURL error: ' . curl_error($ch);
  } else {
    return handleSuccessfulTokenCurlResponse($response);
  }
}

function handleSuccessfulTokenCurlResponse(string $response): string {
  /*
   * The $response should look something like this:
   * {
   *   "access_token": "rfx2uswqe8l4g1mkagrvg5tv0ks3",
   *   "expires_in": 14124,
   *   "refresh_token": "5b93chm6hdve3mycz05zfzatkfdenfspp1h1ar2xxdalen01",
   *   "scope": [
   *     "channel:moderate",
   *     "chat:edit",
   *     "chat:read"
   *   ],
   *   "token_type": "bearer"
   * }
   */
  $token = json_decode($response, true);
  if (!isset($token['access_token'])) {
    return '<b>Error:</b> The response did not include the token';
  }

  $twitchUserInfo = getTwitchUserInfo($token['access_token']);
  $_SESSION['twitch_name']  = $twitchUserInfo['name'];
  $_SESSION['twitch_image'] = $twitchUserInfo['image'];

  return '<p><b style="color: green">&check;</b> Success! You can now submit ratings as <b>'
    . htmlspecialchars($twitchUserInfo['name']) . '</b>. Go to <a href="rating.php">Ratings overview</a>.</p>';
}

// https://dev.twitch.tv/docs/api/reference/#get-users
function getTwitchUserInfo(string $twitchToken): array {

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://api.twitch.tv/helix/users');
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $twitchToken,
    'Client-Id: ' . Configuration::TWITCH_CLIENT_ID
  ]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  if (curl_errno($ch)) {
    die('Error getting user: ' . curl_error($ch));
  }
  curl_close($ch);

  $userResponse = json_decode($response);
  if (empty($userResponse->data)) {
    die('Error: The data in the user response was empty');
  }

  // Use 'display_name' instead of 'login' if all-lowercase is not desired; the user from Nightbot is also extracted
  // as all-lowercase, so this is kept for consistency
  return [
    'name' => $userResponse->data[0]->login,
    'image' => $userResponse->data[0]->profile_image_url
  ];
}

function outputConnectLinkAndInfo(): void {
  echo '<h1>Connect to Twitch</h1>
  <p>You can connect your Twitch account to this page to provide ratings on this web page.
  The connection only serves as purpose of identifying you—nothing will be done with your Twitch account.</p>';

  $redirectUrl = obtainSelfLink();
  $url = "https://id.twitch.tv/oauth2/authorize?response_type=code&client_id=" . Configuration::TWITCH_CLIENT_ID
    . "&redirect_uri=" . urlencode($redirectUrl)
    . "&scope=";
  echo '<p><a href="' . htmlspecialchars($url) . '">Click here to connect with Twitch</a></p>';

  echo '<p>Alternatively, you can provide ratings via the <code>!rate</code> command in chat</p>';
}

function obtainSelfLink(): string {
  return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
}
