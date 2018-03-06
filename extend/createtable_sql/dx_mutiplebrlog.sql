CREATE TABLE if NOT EXISTS `dx_mutiplebrlog_--tablenamedate--` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`hardwareid` CHAR(12) NOT NULL COMMENT '硬件ID',
	`outletsnum` VARCHAR(20) NOT NULL COMMENT '渠道号',
	`drvvsion` VARCHAR(60) NOT NULL,
	`browsers` TEXT NOT NULL COMMENT '浏览器名称',
	`browserids` VARCHAR(100) NOT NULL,
	`curdate` INT(8) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`, `curdate`),
	INDEX `index_hardwareid` (`hardwareid`),
	INDEX `index_outletsnum` (`outletsnum`),
	INDEX `index_browserids` (`browserids`)
)
COLLATE='utf8_general_ci'