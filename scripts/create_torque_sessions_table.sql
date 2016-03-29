USE `torque`;

DROP TABLE IF EXISTS `sessions`;
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
  KEY `session` (`session`,`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

