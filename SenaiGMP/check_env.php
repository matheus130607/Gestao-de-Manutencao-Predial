<?php
require __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
}
echo 'ENV exists: ' . (file_exists(__DIR__ . '/.env') ? 'yes' : 'no') . PHP_EOL;
echo "--- .env (first 10 lines) ---\n";
$env = @file(__DIR__ . '/.env');
if ($env) {
    $lines = array_slice($env, 0, 10);
    echo implode('', $lines) . PHP_EOL;
} else {
    echo "(cannot read .env)\n\n";
}
echo 'APP_KEY=' . (getenv('APP_KEY') ?: '(none)') . PHP_EOL;
echo '$_ENV["APP_KEY"]=' . ($_ENV['APP_KEY'] ?? '(none)') . PHP_EOL;
echo '$_SERVER["APP_KEY"]=' . ($_SERVER['APP_KEY'] ?? '(none)') . PHP_EOL;