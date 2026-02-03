<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

session_destroy();
redirect('login.php');
