CREATE TABLE `users`
(
    `id`            int unsigned NOT NULL AUTO_INCREMENT,
    `name`          char(100) NOT NULL DEFAULT '',
    `email`         char(100) NOT NULL DEFAULT '',
    `validts`       tinyint(1) NOT NULL,
    `confirmed`     tinyint(1) NOT NULL,
    `lastEmailSend` int       NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY             `validts-confirmed` (`validts`,`confirmed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `emails`
(
    `id`      int unsigned NOT NULL AUTO_INCREMENT,
    `email`   char(100) NOT NULL DEFAULT '',
    `checked` tinyint(1) NOT NULL,
    `valid`   tinyint(1) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY       `checked-valid` (`checked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `queue`
(
    `id`         int unsigned NOT NULL AUTO_INCREMENT,
    `idKey`      char(100) NOT NULL DEFAULT '',
    `type`       int       NOT NULL,
    `arg1`       char(100) NOT NULL DEFAULT '',
    `lockedTill` int       NOT NULL,
    `done`       tinyint(1) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `type-idkey` (`type`,`idKey`),
    KEY          `type-done` (`type`,`done`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;