<?php

require 'Configuration.php';
require 'DatabaseHandler.php';

$db = new DatabaseHandler();
$db->initTables();
