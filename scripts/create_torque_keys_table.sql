USE `torque`;

SELECT Count(*)
INTO @exists
FROM information_schema.tables 
WHERE table_schema = 'torque'
    AND table_type = 'BASE TABLE'
    AND table_name = 'torque_keys';

SET @query = If(@exists>0,
    'RENAME TABLE torque_keys TO torque_keys_old',
    'SELECT \'nothing to rename\' status');

PREPARE stmt FROM @query;

EXECUTE stmt;
#DROP TABLE IF EXISTS `torque_keys`;
CREATE TABLE `torque_keys` (
  `id` varchar(255) NOT NULL,
  `description` varchar(255) COMMENT 'Description',
  `type` varchar(255) NOT NULL DEFAULT 'varchar(255)' COMMENT 'Variable Type',
  `units` varchar(255) COMMENT 'Units',
  `populated` boolean NOT NULL DEFAULT '0' COMMENT 'Is This Variable Populated?',
  `min` float COMMENT 'Minimum Value',
  `max` float COMMENT 'Maximum Value',
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff122e','0-100kph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff124f','0-200kph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1277','0-30mph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff122d','0-60mph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff122f','1/4 mile Time','float','s',1,0,30);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1230','1/8 mile Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1264','100-0kph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1260','40-60mph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1265','60-0mph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff125e','60-120mph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1276','60-130mph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff125f','60-80mph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1261','80-100mph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1275','80-120kph Time','float','s',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k47','Absolute Throttle Position B','float','%',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1223','Acceleration Sensor (Total)','float','g',1,-1,1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1220','Acceleration Sensor (X Axis)','float','g',1,-1,1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1221','Acceleration Sensor (Y Axis)','float','g',1,-1,1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1222','Acceleration Sensor (Z Axis)','float','g',1,-1,1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k49','Accelerator Pedal Position D','float','%',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k4a','Accelerator Pedal Position E','float','%',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k4b','Accelerator Pedal Position F','float','%',1,0,100);
INSERT INTO torque_keys (id, description, type, populated, min, max) VALUES ('kff124d','Air Fuel Ratio (Commanded)','float',1,0,30);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('kff1249','Air Fuel Ratio (Measured)','float',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k12','Air Status','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k46','Ambient Air Temp','float','&deg;C',1,-40,50);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1263','Average Trip Speed (Whilst Moving Only)','float','km/h',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1272','Average Trip Speed (Whilst Stopped or Moving)','float','km/h',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1270','Barometer (On Android device)','float','mb',1,800,1100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k33','Barometric Pressure (From Vehicle)','float','kPa',1,0,255);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k3c','Catalyst Temperature (Bank 1 Sensor 1)','float','&deg;C',1,0,60);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k3e','Catalyst Temperature (Bank 1 Sensor 2)','float','&deg;C',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k3d','Catalyst Temperature (Bank 2 Sensor 1)','float','&deg;C',1,0,60);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k3f','Catalyst Temperature (Bank 2 Sensor 2)','float','&deg;C',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1258','CO2 (Average)','float','g/km',1,0,120);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1257','CO2 (Instantaneous)','float','g/km',1,0,120);
INSERT INTO torque_keys (id, description, type, populated, min, max) VALUES ('k44','Commanded Equivalence Ratio (lambda)','float',1,0,2);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff126d','Cost per mile/km (Instant)','float','$/km',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff126e','Cost per mile/km (Trip)','float','$/km',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff126a','Distance to empty (Estimated)','float','km',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k31','Distance Travelled Since Codes Cleared','float','km',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k21','Distance Travelled With MIL/CEL Lit','float','km',1,0,100);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k2c','EGR Commanded','float',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k2d','EGR Error','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k5','Engine Coolant Temperature','float','&deg;C',1,-40,120);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1273','Engine kW (At the Wheels)','float','kW',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k4','Engine Load','float','%',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k43','Engine Load (Absolute)','float','%',1,0,20000);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k5c','Engine Oil Temperature','float','&deg;C',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kc','Engine RPM','float','rpm',1,0,10000);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k52','Ethanol Fuel %','float','%',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k32','Evap System Vapor Pressure','float','Pa',1,0,1000);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k78','Exhaust Gas Temperature 1','float','&deg;C',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k79','Exhaust Gas Temperature 2','float','&deg;C',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff125c','Fuel Cost (Trip)','float','$',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff125d','Fuel Flow Rate/Hour','float','l/hr',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff125a','Fuel Flow Rate/Minute','float','cc/min',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k2f','Fuel Level (From Engine ECU)','float','%',1,0,100);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('ka','Fuel Pressure','float',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k23','Fuel Rail Pressure','float',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k22','Fuel Rail Pressure (Relative to Manifold Vacuum)','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff126b','Fuel Remaining (Calculated From Vehicle Profile)','float','%',1,0,100);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k3','Fuel Status','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k7','Fuel Trim Bank 1 Long Term','float','%',1,-25,25);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k14','Fuel Trim Bank 1 Sensor 1','float','%',1,-100,100);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k15','Fuel Trim Bank 1 Sensor 2','float','%',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k16','Fuel Trim Bank 1 Sensor 3','float','%',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k17','Fuel Trim Bank 1 Sensor 4','float','%',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k6','Fuel Trim Bank 1 Short Term','float','%',1,-25,25);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k9','Fuel Trim Bank 2 Long Term','float','%',1,-25,25);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k18','Fuel Trim Bank 2 Sensor 1','float','%',1,-100,100);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k19','Fuel Trim Bank 2 Sensor 2','float','%',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k1a','Fuel Trim Bank 2 Sensor 3','float','%',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k1b','Fuel Trim Bank 2 Sensor 4','float','%',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k8','Fuel Trim Bank 2 Short Term','float','%',1,-25,25);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1271','Fuel Used (Trip)','float','l',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1239','GPS Accuracy','float','m',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1010','GPS Altitude','float','m',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff123b','GPS Bearing','float','&deg;',1,0,360);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1006','GPS Latitude','float','&deg;',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1005','GPS Longitude','float','&deg;',1,0,100);
INSERT INTO torque_keys (id, description, type, populated, min, max) VALUES ('kff123a','GPS Satellites','float',1,0,10);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1237','GPS vs OBD Speed Difference','float','km/h',1,0,10);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1226','Horsepower (At the Wheels)','float','hp',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kf','Intake Air Temperature','float','&deg;C',1,-40,60);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kb','Intake Manifold Pressure','float','kPa',1,0,255);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1203','Kilometers Per Litre (Instant)','float','kpl',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff5202','Kilometers Per Litre (Long Term Average)','float','kpl',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1207','Litres Per 100 Kilometer (Instant)','float','l/100km',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff5203','Litres Per 100 Kilometer (Long Term Average)','float','l/100km',1,0,100);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k10','Mass Air Flow Rate','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1201','Miles Per Gallon (Instant)','float','mpg',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff5201','Miles Per Gallon (Long Term Average)','float','mpg',1,0,100);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k24','O2 Sensor1 Equivalence Ratio','float',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k34','O2 Sensor1 Equivalence Ratio (Alternate)','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1240','O2 Sensor1 Wide-range Voltage','float','V',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k25','O2 Sensor2 Equivalence Ratio','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1241','O2 Sensor2 Wide-range Voltage','float','V',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k26','O2 Sensor3 Equivalence Ratio','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1242','O2 Sensor3 Wide-range Voltage','float','V',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k27','O2 Sensor4 Equivalence Ratio','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1243','O2 Sensor4 Wide-range Voltage','float','V',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k28','O2 Sensor5 Equivalence Ratio','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1244','O2 Sensor5 Wide-range Voltage','float','V',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k29','O2 Sensor6 Equivalence Ratio','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1245','O2 Sensor6 Wide-range Voltage','float','V',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k2a','O2 Sensor7 Equivalence Ratio','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1246','O2 Sensor7 Wide-range Voltage','float','V',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('k2b','O2 Sensor8 Equivalence Ratio','float',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1247','O2 Sensor8 Wide-range Voltage','float','V',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1214','O2 Volts Bank 1 Sensor 1','float','V',1,0,1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1215','O2 Volts Bank 1 Sensor 2','float','V',1,0,1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1216','O2 Volts Bank 1 Sensor 3','float','V',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1217','O2 Volts Bank 1 Sensor 4','float','V',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1218','O2 Volts Bank 2 Sensor 1','float','V',1,0,1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1219','O2 Volts Bank 2 Sensor 2','float','V',1,0,1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff121a','O2 Volts Bank 2 Sensor 3','float','V',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff121b','O2 Volts Bank 2 Sensor 4','float','V',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('k5a','Relative Accelerator Pedal Position','float','%',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k45','Relative Throttle Position','float','%',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k1f','Run Time Since Engine Start','float','s',1,0,100);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('session','Session ID','varchar(255)',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1001','Speed (GPS)','float','km/h',1,0,160);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kd','Speed (OBD)','float','km/h',1,0,160);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k11','Throttle Position (Manifold)','float','%',1,0,100);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('kff124a','Tilt (x)','float',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('kff124b','Tilt (y)','float',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('kff124c','Tilt (z)','float',1);
INSERT INTO torque_keys (id, description, type, populated) VALUES ('time','Timestamp','varchar(255)',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('ke','Timing Advance','float','&deg;',1,-64,63);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1225','Torque','float','ft-lb',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kfe1805','Transmission Temperature (Method 1)','float','&deg;C',1);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kb4','Transmission Temperature (Method 2)','float','&deg;C',1);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1206','Trip Average KPL','float','kpl',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1208','Trip Average Litres/100 KM','float','l/100km',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1205','Trip Average MPG','float','mpg',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1204','Trip Distance','float','km',1,0,200);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff120c','Trip Distance (Stored in Vehicle Profile)','float','km',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1266','Trip Time (Since Journey Start)','float','s',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1268','Trip Time (Whilst Moving)','float','s',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1267','Trip Time (Whilst Stationary)','float','s',1,0,100);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1202','Turbo Boost & Vacuum Gauge','float','psi',1,-20,20);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('k42','Voltage (Control Module)','float','V',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated, min, max) VALUES ('kff1238','Voltage (OBD Adapter)','float','V',1,0,16);
INSERT INTO torque_keys (id, description, type,  units, populated) VALUES ('kff1269','Volumetric Efficiency (Calculated)','float','%',1);
INSERT INTO torque_keys (id, type, populated) VALUES ('kff1007','float', 0);
