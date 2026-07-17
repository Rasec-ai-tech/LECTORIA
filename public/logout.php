<?php
require_once __DIR__ . '/../src/Services/AuthService.php';

AuthService::logout();
header('Location: index.php', true, 302);
exit;
