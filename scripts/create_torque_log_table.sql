USE `torque`;

DROP TABLE IF EXISTS `raw_logs`;
CREATE TABLE `raw_logs` (
  `v` varchar(1) NOT NULL,
  `session` varchar(15) NOT NULL,
  `id` varchar(32) NOT NULL,
  `time` varchar(15) NOT NULL,
  `kff1005` float NOT NULL DEFAULT '0',
  `kff1006` float NOT NULL DEFAULT '0',
  `kff1001` float NOT NULL DEFAULT '0' COMMENT 'Speed (GPS)',
  `kff1007` float NOT NULL DEFAULT '0' COMMENT 'GPS Bearing',
  `k4` float NOT NULL DEFAULT '0' COMMENT 'Engine Load',
  `k2f` float NOT NULL DEFAULT '0' COMMENT 'Fuel Level',
  `k11` float NOT NULL DEFAULT '0' COMMENT 'Throttle Position',
  `k5` float NOT NULL DEFAULT '0' COMMENT 'Engine Coolant Temp',
  `kc` float NOT NULL DEFAULT '0' COMMENT 'Engine RPM',
  `kd` float NOT NULL DEFAULT '0' COMMENT 'Speed (OBD)',
  `kf` float NOT NULL DEFAULT '0' COMMENT 'Intake Air Temp',
  `kff1226` float NOT NULL DEFAULT '0' COMMENT 'Horsepower',
  `kff1220` float NOT NULL DEFAULT '0' COMMENT 'Accel (X)',
  `kff1221` float NOT NULL DEFAULT '0' COMMENT 'Accel (Y)',
  `k46` float NOT NULL DEFAULT '0' COMMENT 'Ambiant Air Temp',
  KEY `session` (`session`,`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
