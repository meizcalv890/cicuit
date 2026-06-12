<?php
require_once __DIR__ . '/bootstrap.php';

if (Auth::check()) {
    redirect(Auth::dashboardUrl());
}
redirect(APP_URL . '/login.php');
