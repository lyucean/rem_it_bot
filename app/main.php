<?php

require_once __DIR__ . '/vendor/autoload.php';

use RIB\model\Processing;

// Reply to all messages, once per second
$processing = new Processing();
$processing->check();
