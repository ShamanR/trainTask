<?php
require_once ROOT_DIR . '/db/init.php';
require_once ROOT_DIR . '/externalApi.php';
$config = require ROOT_DIR . '/etc/config.php';
$pdo = new PDO($config['dbConnectString'], $config['dbUsername'], $config['dbPassword']);

$now = time();
$ttl = 30 * 60;

$jobsDone = 0;
while ($job = getJobFromQueue($pdo, QUEUE_TYPE_EMAIL_VALIDATE, $now, $ttl)) {
    $email = $job['arg1'];
    $result = check_email($email);
    updateEmailValidity($pdo, $email, $result);
    jobMarkDone($pdo, $job['id']);
    $jobsDone++;
}
return sprintf('%d emails validated', $jobsDone);