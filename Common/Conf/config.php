<?php
return array(
	//'配置项'=>'配置值'
	'DB_TYPE'   => 'mysql',
	'DB_HOST'   => 'localhost', //5563f5bb495de.gz.cdb.myqcloud.com
	'DB_NAME'   => 'hg_pos',
	'DB_USER'   => 'root',
	'DB_PWD'    => 'root', //mima914586488
	'DB_PORT'   => 3306,
	'DB_PREFIX' => 'hg_',
	'DB_CHARSET'=> 'utf8', 
    'TMPL_CACHE_ON' => false,//禁止模板编译缓存
    'HTML_CACHE_ON' => false,//禁止静态缓存
    'SESSION_OPTIONS' => array('use_only_cookies'=>0,'use_trans_sid'=>1),
    // 'COOKIE_DOMAIN' => 'pos.heyguo.com',

    'WXWEB' => array(
    	// $appId='wxefed1309844fcd46';
		// $appSecret='d4624c36b6795d1d99dcf0547af5443d';
    	'appid'=>'wx9369152aa7957f19',
    	'appsecret'=>'4d1d1dba192c7998775ff531ce45364e',
    ),
    'DEFAULT_CHARSET'       =>  'utf-8', // 默认输出编码

    //支付方式
	'PAYWAY'=>array(
		'0'=>array('name'=>' '),
		'1'=>array('name'=>'现金'),
		'2'=>array('name'=>'刷卡'),
		// '3'=>array('name'=>'微信'),
		// '4'=>array('name'=>'支付宝'),
		'5'=>array('name'=>'扫码')
	)
);

//连接另一个数据库
function otherdb()
{
    $db = "mysql://cdb_outerroot:mima914586488@5563f5bb495de.gz.cdb.myqcloud.com:10124/kj_plan";    
    return $db;
}