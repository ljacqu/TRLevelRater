<?php

class Configuration {

  const API_SECRET = 'secret';

  // ------
  // Database connection details
  // ------

  /** Database host. */
  const DB_HOST = 'localhost';
  /** Database name. */
  const DB_NAME = 'tr_level_ratings';
  /** Database user. */
  const DB_USER = 'root';
  /** Database password. */
  const DB_PASS = '';


  // ------
  // Twitch application details
  // ------
  // Register a client at https://dev.twitch.tv/console/apps

  const TWITCH_CLIENT_ID = '';
  const TWITCH_CLIENT_SECRET = '';


  private function __construct() {
  }
}
