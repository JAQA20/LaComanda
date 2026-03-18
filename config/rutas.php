<?php
define("BASE_URL", getenv('BASE_URL') ?: '/');
define('ROOT_PATH', dirname(__DIR__));
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'America/Costa_Rica');
