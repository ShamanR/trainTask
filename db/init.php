<?php
require_once "emails.php";
require_once "queue.php";

function newException(PDOStatement $pdoSt): \Exception {
    return new \Exception($pdoSt->errorInfo()[2], $pdoSt->errorCode());
}