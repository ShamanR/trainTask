<?php
const GET_EMAILS_FOR_VAILDATE = <<<EMAILS
SELECT emails.email FROM emails 
    RIGHT JOIN
        users ON users.email = emails.email
    WHERE emails.checked = 0 AND 
        users.validts <= :validts AND
        users.confirmed = 0 and 
        users.lastEmailSend <= users.validts-:ttl
    LIMIT :limit
    OFFSET :offset;
EMAILS;

function getEmailsForValidate(PDO $pdo, int $subscriptionTtl): Generator
{
    static $getEmailsForValidate;
    if ($getEmailsForValidate == null) {
        $getEmailsForValidate = $pdo->prepare(GET_EMAILS_FOR_VAILDATE);
        if (!$getEmailsForValidate) {
            error_log("error creating statement `UPDATE_EMAIL_VALIDITY`");
            exit(1);
        }
    }
    $validts = time() + $subscriptionTtl;
    $offset = 0;
    $getEmailsForValidate->bindValue(':limit', 10000, PDO::PARAM_INT);
    $getEmailsForValidate->bindValue(':offset', 0, PDO::PARAM_INT);
    $getEmailsForValidate->bindValue(':validts', $validts, PDO::PARAM_INT);
    $getEmailsForValidate->bindValue(':ttl', $subscriptionTtl, PDO::PARAM_INT);
    $getEmailsForValidate->execute();
    while ($getEmailsForValidate->rowCount() > 0) {
        $result = $getEmailsForValidate->fetchAll();
        foreach ($result as $row) {
            $offset++;
            yield $row['email'];
        }
        $getEmailsForValidate->bindValue(':limit', 200, PDO::PARAM_INT);
        $getEmailsForValidate->bindValue(':offset', $offset, PDO::PARAM_INT);
        $getEmailsForValidate->bindValue(':validts', $validts, PDO::PARAM_INT);
        $getEmailsForValidate->execute();
    }
}

const UPDATE_EMAIL_VALIDITY = <<<EMAILS
UPDATE emails SET
    checked = 1,
    valid = :valid
WHERE email=:email
EMAILS;

function updateEmailValidity(PDO $pdo, string $email, bool $isValid)
{
    static $updateEmailValidity;
    if ($updateEmailValidity == null) {
        $updateEmailValidity = $pdo->prepare(UPDATE_EMAIL_VALIDITY);
        if (!$updateEmailValidity) {
            error_log("error creating statement `UPDATE_EMAIL_VALIDITY`");
            exit(1);
        }
    }

    if (!$updateEmailValidity->execute(['valid' => (int)$isValid, 'email' => $email])) {
        error_log("error in execute statement `UPDATE_EMAIL_VALIDITY`" . $updateEmailValidity->errorInfo()[2] ?? "unknown");
        exit(1);
    }
    $updateEmailValidity->closeCursor();
}