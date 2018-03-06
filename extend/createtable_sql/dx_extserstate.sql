CREATE TABLE if NOT EXISTS `dx_extserstate_--tablenamedate--` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`hardwareid` CHAR(12) NOT NULL COMMENT '硬件ID',
	`softui` VARCHAR(10) NOT NULL,
	`softserver` VARCHAR(10) NOT NULL,
	`softdriver` VARCHAR(10) NOT NULL,
	`ip` BIGINT(20) NULL DEFAULT NULL,
	`curtime` INT(12) NOT NULL,
	`curdate` INT(8) NOT NULL DEFAULT '0',
	`addtime` INT(12) NOT NULL DEFAULT '0',
	`newflag` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 老用户 1 新用户',
	PRIMARY KEY (`id`, `curdate`),
	INDEX `index_hardwareid` (`hardwareid`),
	INDEX `index_ip` (`ip`),
	INDEX `index_curtime` (`curtime`),
	INDEX `index_newflag` (`newflag`)
)
COLLATE='utf8_general_ci'