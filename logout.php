<?php
require_once __DIR__ . '/../includes/auth.php';
logout();
header('Location: ' . url_site('index.php'));
exit;
