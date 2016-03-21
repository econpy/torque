USE `torque`;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(32) NOT NULL,
  `session` varchar(15) NOT NULL,
  `time` varchar(15) NOT NULL,
  `eml` varchar(255) NOT NULL DEFAULT '0',
  `profile` varchar(255) NOT NULL DEFAULT 'Not Specified'
  `profileName` varchar(255) NOT NULL DEFAULT 'Not Specified'
  `notice` varchar(255) NOT NULL DEFAULT '0'
  `noticeClass` varchar(255) NOT NULL DEFAULT '0'
  `timestart` varchar(15) NOT NULL,
  `timeend` varchar(15) NOT NULL,
  KEY `session` (`session`,`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

