<?php
$sys_config = array(
	//'配置项'=>'配置值'


	// 更改默认的/Public 替换规则
	'TMPL_PARSE_STRING'=>array(
		'__PUBLIC__' 	=>'/public',
		'__CSS__'		=>'/public/css',
		'__JS__'		=>'/public/js',
		'__IMG__'		=>'/public/images',
	) ,


	'URL_MODEL' => '1',   		//修改URL模式
	// 'SHOW_PAGE_TRACE'=>true,	//开启页面Trace

	'DB_TYPE'=>'mysql',   			//设置数据库类型
	'DB_HOST'=>'123.57.135.102',	//设置主机
	'DB_NAME'=>'xplan',				//设置数据库名
	'DB_USER'=>'jiulu',    			//设置用户名
	'DB_PWD'=>'mysql_jiulu',		//设置密码
	'DB_PORT'=>'3306',   			//设置端口号
	'DB_PREFIX'=>'',  				//设置表前缀
	
);


$siteconfig = require 'web_config.php';

return array_merge($sys_config,$siteconfig);

?>