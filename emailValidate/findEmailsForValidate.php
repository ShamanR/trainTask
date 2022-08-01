<?php
require_once ROOT_DIR . '/db/init.php';
require_once ROOT_DIR . '/externalApi.php';
$config = require ROOT_DIR . '/etc/config.php';

$pdo = new PDO($config['dbConnectString'], $config['dbUsername'], $config['dbPassword']);

$addJobs = 0;
foreach (getEmailsForValidate($pdo, 60 * 30) as $email) {
    $idKey = $email;
    addJobToQueue($pdo, $idKey, QUEUE_TYPE_EMAIL_VALIDATE, $email);
    $addJobs++;
}
return sprintf('%d emails add to queue for validate', $addJobs);