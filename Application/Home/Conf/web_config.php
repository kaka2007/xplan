<?php
return array(
	//容联 云通讯短信
	'RL_AccountSID'=>'8a48b5514f4fc588014f71e10aa44a45',	//主账号
	'RL_AccountToken'=>'88a57bb2dbfb4603a060589a5fe956d0',	//主账号Token
	'RL_AppID'=>'8a48b5514f4fc588014f71e3b41f4a57',			//应用ID
	'RL_ServerIP'=>'app.cloopen.com',				//请求地址，格式如下，不需要写https://
	'RL_ServerPort'=>'8883',								//请求端口
	'RL_SoftVersion'=>'2013-12-26',							//REST版本号
	'RL_SMS_TEMPLATE_ID' => '49560',						//短信模板ID
    'RL_SMS_ORDER_ID'   => '58184' ,                        //教练新订单通知

	//每次最多返回的记录的条数
	'T_COUNT'=>20,

	//每个教练最多多少个客户
	'T_MAX_USERS'=> 10,

	//表名
	'T_ACTION'		=> 'action',		//动作表
	'T_COMMENT'		=> 'comment',		//评论表
	'T_COURSE_RECORD'	=> 'course_record',//课程记录
	'T_EVALUATE'	=> 'evaluate',		//评估
	'T_MY_PLAN'		=> 'my_plan',		//我参与的计划
	'T_PLAN' 	  	=> 'plan',			//计划表
	'T_PLAN_COACH'	=> 'plan_coach', 	//计划教练表
	'T_PLAN_INTRO'	=> 'plan_intro',	//计划介绍表
	'T_USER_INFO' 	=> 'user_info',		//用户表
	'T_VERIFY_TMP'	=> 'verify_tmp',	//登录用的临时表
	'T_XORDER'		=> 'xorder',		//订单表
	);


?>
