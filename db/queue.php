<?php
const ADD_TO_QUEUE = <<<QUEUE_ADD
INSERT IGNORE into queue (idKey, type, arg1) 
values
	(:idKey, :type, :arg1);
QUEUE_ADD;

/**
 * @throws Exception
 */
function addJobToQueue(PDO $pdo, string $idKey, int $type, string $arg1): void
{
    static $addToQueue;
    if ($addToQueue == null) {
        $addToQueue = $pdo->prepare(ADD_TO_QUEUE);
        if (!$addToQueue) {
            throw newException($addToQueue);
        }
    }
    if (!$addToQueue->execute(['idKey' => $idKey, 'type' => $type, 'arg1' => $arg1])) {
        throw newException($addToQueue);
    }
}

const JOB_SELECT_FOR_UPDATE = <<<JOB_SELECT_FOR_UPDATE
SELECT id, arg1
    FROM queue 
    WHERE
        done=0 AND
        type=:type AND
        lockedTill < :now
    ORDER BY ID
    LIMIT 1 FOR UPDATE;
JOB_SELECT_FOR_UPDATE;

/**
 * @throws Exception
 */
function jobSelectForUpdate(PDO $pdo, string $type, int $now)
{
    static $jobSelectForUpdate;
    if ($jobSelectForUpdate == null) {
        $jobSelectForUpdate = $pdo->prepare(JOB_SELECT_FOR_UPDATE);
        if (!$jobSelectForUpdate) {
            throw newException($jobSelectForUpdate);
        }
    }
    $jobSelectForUpdate->execute(['type' => $type, 'now' => $now]);
}

const JOB_LOCK = <<<JOB_LOCK
UPDATE queue 
    SET lockedTill=:now + :ttl, 
        id=LAST_INSERT_ID(id)
    WHERE
        done=0 AND
        type=:type AND
        lockedTill < :now
    ORDER BY ID
    LIMIT 1;
JOB_LOCK;

/**
 * @throws Exception
 */
function jobLock(PDO $pdo, string $type, int $now, int $jobTtl)
{
    static $jobLock;
    if ($jobLock == null) {
        $jobLock = $pdo->prepare(JOB_LOCK);
        if (!$jobLock) {
            throw newException($jobLock);
        }
    }
    $jobLock->execute(['type' => $type, 'now' => $now, 'ttl' => $jobTtl]);
    return $pdo->lastInsertId();
}

const JOB_GET = <<<JOB_GET
SELECT id, idKey, type, arg1, lockedTill, done 
    FROM queue 
    WHERE id=:id;
JOB_GET;

/**
 * @throws Exception
 */
function jobGet(PDO $pdo, int $id)
{
    static $jobGet;
    if ($jobGet == null) {
        $jobGet = $pdo->prepare(JOB_GET);
        if (!$jobGet) {
            throw newException($jobGet);
        }
    }
    $jobGet->execute(['id' => $id]);
    $rows = $jobGet->fetchAll();
    return $rows[0] ?? null;
}

/**
 * @throws Exception
 */
function getJobFromQueue(PDO $pdo, string $type, int $now, int $jobTtl): ?array
{
    $pdo->beginTransaction();
    jobSelectForUpdate($pdo, $type, $now);
    $jobId = jobLock($pdo, $type, $now, $jobTtl);
    $result = jobGet($pdo, $jobId);
    $pdo->commit();
    return $result;
}

const MARK_QUEUE_DONE = <<<QUEUE_DONE
UPDATE queue
    SET done=1
    WHERE 
    id=:id
QUEUE_DONE;

/**
 * @throws Exception
 */
function jobMarkDone(PDO $pdo, $jobId)
{
    static $jobMarkDone;
    if ($jobMarkDone == null) {
        $jobMarkDone = $pdo->prepare(MARK_QUEUE_DONE);
        if (!$jobMarkDone) {
            throw newException($jobMarkDone);
        }
    }
    $jobMarkDone->execute(['id' => $jobId]);
}