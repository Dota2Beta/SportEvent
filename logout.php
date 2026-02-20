<?php
require __DIR__ . '/includes/auth.php';

$_SESSION = [];
session_destroy();

redirectTo('index.php');
exit;
