CREATE TABLE if NOT EXISTS `dx_pclog_--tablenamedate--` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`hardwareid` CHAR(12) NOT NULL COMMENT '硬件ID',
	`outletsnum` VARCHAR(20) NOT NULL COMMENT '渠道号',
	`instime` INT(12) NOT NULL,
	`insdate` INT(8) NOT NULL,
	`drvvsion` VARCHAR(60) NOT NULL,
	`systemvsion` VARCHAR(60) NOT NULL,
	`antivirus` VARCHAR(60) NOT NULL,
	`antivirusid` BIGINT(20) NOT NULL DEFAULT '0',
	`ip` BIGINT(20) NULL DEFAULT NULL,
	`ntfilever` VARCHAR(50) NULL DEFAULT NULL,
	`cidllver` VARCHAR(50) NULL DEFAULT NULL,
	`curtime` INT(12) NOT NULL,
	`curdate` INT(8) NOT NULL DEFAULT '0',
	`curhour` INT(4) NOT NULL DEFAULT '0',
	`reqcount` INT(12) NOT NULL DEFAULT '0',
	`addtime` INT(12) NOT NULL DEFAULT '0',
	`carrystate` TINYINT(1) NOT NULL DEFAULT '0',
	`newflag` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 老用户 1 新用户',
	PRIMARY KEY (`id`, `curdate`),
	INDEX `index_hardwareid` (`hardwareid`),
	INDEX `index_antivirusid` (`antivirusid`),
	INDEX `index_outletsnum` (`outletsnum`),
	INDEX `index_drvvsion` (`drvvsion`),
	INDEX `index_ip` (`ip`),
	INDEX `index_curtime` (`curtime`),
	INDEX `index_curhour` (`curhour`),
	INDEX `index_newflag` (`newflag`)
)
COLLATE='utf8_general_ci'