CREATE TABLE if NOT EXISTS `dx_process_--tablenamedate--` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`mac` CHAR(12) NULL DEFAULT NULL,
	`channel` VARCHAR(50) NULL DEFAULT NULL,
	`process` TEXT NULL DEFAULT NULL,
	`curtime` VARCHAR(50) NULL DEFAULT NULL,
	`addtime` INT(11) NULL DEFAULT NULL,
	`curdate` INT(8) NOT NULL,
	PRIMARY KEY (`id`, `curdate`),
	INDEX `mac` (`mac`),
	INDEX `channel` (`channel`)
)
COLLATE='utf8_general_ci'