<?php
declare(strict_types=1);

$host = getenv('SZKOLA_DB_HOST') ?: '127.0.0.1';
$user = getenv('SZKOLA_DB_USER') ?: 'root';
$pass = getenv('SZKOLA_DB_PASS');
$pass = $pass === false ? '' : $pass;
$db = getenv('SZKOLA_DB_NAME') ?: 'szkola';
$port = (int) (getenv('SZKOLA_DB_PORT') ?: 3306);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
