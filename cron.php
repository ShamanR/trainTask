<?php
require 'init.php';
const TASKS = [
    'emailValidate.emailValidateWorker' => ROOT_DIR . '/emailValidate/emailValidateWorker.php',
    'emailValidate.findEmailsForValidate' => ROOT_DIR . '/emailValidate/findEmailsForValidate.php',
];
$opts = getopt('', ['task:', 'name:']);
$taskName = $opts['task'] ?? null;

function fatal(string $message)
{
    error_log($message);
    exit(1);
}

if (!$taskName) {
    fatal("no task param for cron");
}

if (!isset(TASKS[$taskName])) {
    fatal('unknown task ' . $taskName);
}
$start = time();
printf("cron `%s` started", $taskName);
$taskFunc = function () use ($taskName) {
    return require_once TASKS[$taskName];
};

$isError = false;
try {
    $output = $taskFunc();
} catch (\Throwable $e) {
    $isError = true;
    $result = "error: " . $e->getMessage();
}

printf("cron `%s` finished for [%d]sec with result: %s\n", $taskName,  time() - $start, $output);
exit((int) $isError);

