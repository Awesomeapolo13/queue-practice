<?php

declare(strict_types=1);

use Alogachev\Homework\App;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app = new App();

try {
    $app->runConsole();
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
