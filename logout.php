<?php
require_once __DIR__ . '/bootstrap.php';
Auth::logout();
redirect(APP_URL . '/login.php');
