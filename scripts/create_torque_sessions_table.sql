USE `torque`;

SELECT Count(*)
INTO @exists
FROM information_schema.tables 
WHERE table_schema = [DATABASE_NAME]
    AND table_type = 'BASE TABLE'
    AND table_name = 'sessions';

SET @query = If(@exists>0,
    'RENAME TABLE sessions TO sessions_old',
    'SELECT \'nothing to rename\' status');

PREPARE stmt FROM @query;

EXECUTE stmt;
#DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `v` varchar(1) NOT NULL,
  `id` varchar(32) NOT NULL,
  `session` varchar(15) NOT NULL,
  `time` varchar(15) NOT NULL,
  `eml` varchar(255) NOT NULL DEFAULT '0',
  `profileName` varchar(255) NOT NULL DEFAULT 'Not Specified',
  `profileFuelType` varchar(255) NOT NULL DEFAULT 'Not Specified',
  `profileWeight` float NOT NULL DEFAULT '0',
  `profileVe` float NOT NULL DEFAULT '0',
  `profileFuelCost` float NOT NULL DEFAULT '0',
  `timestart` varchar(15) NOT NULL,
  `timeend` varchar(15) NOT NULL,
  `sessionsize` varchar(15) NOT NULL DEFAULT '0',
  UNIQUE KEY `session` (`session`,`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

