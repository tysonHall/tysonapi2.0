<?php
//入口文件，加载部分配置信息，获取关键内容

define('APP', 'app/');
define('EXTEND', 'extend/');
define('MODEL', 'model/');

define('ROOT', '');

define('PHPSUFFIX', '.php');
//禁用错误报告
// error_reporting(0);
//报告运行时错误
// error_reporting(E_ERROR | E_WARNING | E_PARSE);
//报告所有错误
// error_reporting(E_ALL);
require ROOT.'start.php';
?>