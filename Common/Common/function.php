<?php
/**
 * 
 * @authors Liujinbi (857053791@QQ.com)
 * @date    2017-05-27 15:32:20
 * @version $Id$
 */

/**
 * 获取给定时间的前几天或者后几天
 * @param  integer $num   天数
 * @param  string  $start 时间
 * @param  string  $fig   时间相加减
 * @return [type]         二维数组，键名为当天时间，包括当天时间的开始和结束
 */
function getDayMap($num=30,$start='',$fig='-')
{
	$start=$start?:time();
	$arr=array();
	for ($i=0; $i < $num; $i++) {
		$tmp=strtotime($fig.$i." day",$start);
		$year=date("Y",$tmp);$month=date("m",$tmp);$day=date("d",$tmp);
		$st=mktime(0,0,0,$month,$day,$year);
		$et=mktime(23,59,59,$month,$day,$year);
		$arr[$st]=array('st'=>$st,'et'=>$et);
	}
	ksort($arr);
	return $arr;
}

/**
 * 获取给定时间的中间时间
 * @param  [type] $stt 开始时间
 * @param  [type] $ett 结束时间
 * @return [type]      二维数组，键名为当天时间，包括当天时间的开始和结束
 */
function getDayMapSE($stt,$ett)
{
	$stt=getOneDay($stt);$stt=$stt['st'];
	$ett=getOneDay($ett);$ett=$ett['et'];
	$diff_day=intval(($ett-$stt)/(24*3600));
	$diff_arr=range(0,$diff_day);
	// array_shift($diff_arr);
	foreach ($diff_arr as $key => $value) {
		$tmp=strtotime($value." day",$stt);
		$year=date("Y",$tmp);$month=date("m",$tmp);$day=date("d",$tmp);
		$st=mktime(0,0,0,$month,$day,$year);
		$et=mktime(23,59,59,$month,$day,$year);
		// $arr[date('Y-m-d H:i:s',$st)]=array('st'=>date('Y-m-d H:i:s',$st),'et'=>date('Y-m-d H:i:s',$et));
		$arr[$st]=array('st'=>$st,'et'=>$et);
	}
	ksort($arr);
	return $arr;
}

/**
 * 获取给定时间当天的开始和结束时间
 * @param  string $d [时间]
 * @return [type]    [description]
 */
function getOneDay($d='')
{
	$d=$d?:time();
	$year=date("Y",$d);$month=date("m",$d);$day=date("d",$d);
	$st=mktime(0,0,0,$month,$day,$year);
	$et=mktime(23,59,59,$month,$day,$year);
	return array('st'=>$st,'et'=>$et);
}

/**
 * 通过给定时间和数量算出 开始时间和结束时间
 * @param  string  $time [时间]
 * @param  integer $num  [相差数量]
 * @param  string  $unit [单位，(day,)]
 * @param  string  $fig  [时间加减]
 * @return [type]        [数组 st开始时间 et结束时间]
 */
function getDayRegion($time='', $num=30, $unit='', $fig='-')
{
	$d=$d?:time();$unit=$unit?:'day';
	$tmp=strtotime($fig.$num." ".$unit,$d);
	$t1=getOneDay($tmp);$t2=getOneDay($d);
	if ($fig=='+') {
		$st=$t2['st'];$et=$t1['et'];
	}else{
		$st=$t1['st'];$et=$t2['et'];
	}
	return array('st'=>$st,'et'=>$et);
}

/**
 * 获取支付方式
 * @param  [type] $id [description]
 * @param  [type] $f  [description]
 * @return [type]     [description]
 */
function getPayway($id,$f=false)
{
	$payway=C('PAYWAY');
	if ($id||$id>-1) {
		$tmp=isset($payway[$id])?$payway[$id]:$payway[0];
		return $f?$tmp['name']:$tmp;
	}else{
		return $payway;
	}
}

/**
 * 取二维数组中一列
 * @param  [array] $arr [二维数据]
 * @param  [string] $f   [列名]
 * @return [array]      [选定列以为数组]
 */
function array_field($arr,$f)
{
    $brr=array();
    if(!is_array($arr)||!is_string($f)){return false;}
    foreach($arr as $k=>$v){array_push($brr,$v[$f]);}
    return count($brr)>0?$brr:false;
}

/**
 * 检测客户端是否手机
 */
function isMobile()
{
    static $mobilebrowser_list='Mobile|iPhone|Android|WAP|NetFront|JAVA|OperasMini|UCWEB|WindowssCE|Symbian|Series|webOS|SonyEricsson|Sony|BlackBerry|Cellphone|dopod|Nokia|samsung|PalmSource|Xphone|Xda|Smartphone|PIEPlus|MEIZU|MIDP|CLDC';
    return !preg_match("/$mobilebrowser_list/i",$_SERVER['HTTP_USER_AGENT'])?
    (!preg_match('/(mozilla|chrome|safari|opera|m3gate|winwap|openwave)/i',$_SERVER['HTTP_USER_AGENT'])?($_GET['mobile']==='yes'?true:false):false):true;
}

function userAgent($ua){
 
    $iphone = strstr(strtolower($ua), 'mobile'); //Search for 'mobile' in user-agent (iPhone have that)
    $android = strstr(strtolower($ua), 'android'); //Search for 'android' in user-agent
    $windowsPhone = strstr(strtolower($ua), 'phone'); //Search for 'phone' in user-agent (Windows Phone uses that)
      
    $androidTB = androidTablet($ua); //Do androidTablet function
    $ipad = strstr(strtolower($ua), 'ipad'); //Search for iPad in user-agent
      
    if($androidTB || $ipad){ //If it's a tablet (iPad / Android)
        return 'tablet';
    }
    elseif($iphone && !$ipad || $android && !$androidTB || $windowsPhone){ //If it's a phone and NOT a tablet
        return 'mobile';
    }
    else{ //If it's not a mobile device
        return 'desktop';
    }    
}

function androidTablet($ua){ //Find out if it is a tablet
    if(strstr(strtolower($ua), 'android') ){//Search for android in user-agent
        if(!strstr(strtolower($ua), 'mobile')){ //If there is no ''mobile' in user-agent (Android have that on their phones, but not tablets)
            return true;
        }
    }
}

function printLog($params=array())
{
	$sn='ss';
	if (strtolower(MODULE_NAME) =='admin') {$sn='admin';}
	if (strtolower(MODULE_NAME)=='home') {$sn='user';}
	$data=array(
		'type'     => 2,
		'uid'      => session($sn.'.id')?:'',
		'username' => session($sn.'.username')?:'',
		'shop_id'  => session($sn.'.shop_id')?:'',
	);
	addLog(array_merge($data,$params));
}

function loginLog($params=array(),$psw=false)
{
	// if (!$psw) {
	// 	if(isset($params['params']['password'])){
	// 		unset($params['params']['password']);
	// 	}
	// }
	$sn='ss';
	if (strtolower(MODULE_NAME) =='admin' || strtolower(MODULE_NAME) =='mobile') {$sn='admin';}
	if (strtolower(MODULE_NAME)=='home') {$sn='user';}
	$data=array(
		'uid'      => session($sn.'.id')?:'',
		'username' => session($sn.'.username')?:'',
		'shop_id'  => session($sn.'.shop_id')?:'',
	);
	addLog(array_merge($data,$params));
}

function addLog($params=array())
{
	$data=array(
		'type'       => 1,
		'uid'        => '',
		'username'      => '',
		'model'      => MODULE_NAME,
		'controller' => CONTROLLER_NAME,
		'action'     => ACTION_NAME,
		'params'     => '',
		'ip'         => get_client_ip(),
		'desc'       => '',
		'time'       => time(),
		'platform'   => userAgent($_SERVER['HTTP_USER_AGENT']),
		'content'    => '',
		'shop_id'	 => ''
	);

	$marr=array_merge($data,$params);
	$marr['params']=!is_array($marr['params'])?:json_encode($marr['params']);

	return M('Log')->add($marr);
}


/**
 * 总单打印
 * @param  [type] $order_id [description]
 * @param  string $paid     实收金额
 * @param  string $remark   [description]
 * @return [type]           [description]
 */
function printZD($order_id,$paid='',$remark='')
{
	if (!$order_id) {
		return false;
	}
	$shop_id=session('admin.shop_id');
	
	$order=M('Order');
	// 打印小票
	$printer=M('Printer')->where("shop_id={$shop_id} and master=1")->select();
	// 菜品数
	$cnum=M('Order_menu')->field('count(distinct menu_id) menu_count')->where(array('order_id'=>$order_id))->find();
	
	// 份数
	$mnum=M('Order_menu')->field('sum(num) num')->where("order_id={$order_id}")->find();

	$info=M()->table('hg_order o,hg_shop s, hg_table t')
	->field('o.order_id, o.order_num as ordernum, o.price, o.paid, t.tab_num as tablenum, s.name as shopname,s.phone,s.address,s.qr')
	->where("o.shop_id=s.shop_id and o.tab_id=t.id and t.shop_id=s.shop_id and o.order_id={$order_id}")
	->find();

	$info['paid']=$paid?:$info['paid'];

	$menu=$order->field('g.group_id gid,m.name,m.price,o.num,o.state os')->table('hg_group g,hg_menu m,hg_order_menu o')->where("g.shop_id={$shop_id} and o.menu_id=m.fruits_id and m.group_id=g.group_id and  o.order_id={$order_id}")->select();
	$printarr = array(
	'config' => array(),
	'data' => array(
		'shopname' => $info['shopname'], 
		'ordernum' => $info['ordernum'], 
		'tablenum' => $info['tablenum'], 
		'cnum'     => $cnum['menu_count'],
		'mnum'     => $mnum['num'],
		'menus'    => $menu,
		'price'	   => $info['price'],
		'paid'     => $info['paid'], 
		'remark'   => $remark,
		'phone'    => $info['phone'],
		'address'  => $info['address'],
		'qrcode'   => $info['qr']
	));
	
	foreach ($printer as $key => $value) {
		$printarr['config']=$value;
		if (_printZD($printarr)) {
			M('Order')->where(array('order_id'=>$info['order_id']))->setInc('print_num');
		}
	}
}
/**总单打印
 * $arrayName = array(
	'config' => array(
		'partner'=>'',
		'apikey'=>'',
		'machine_code'=>,
		'msign'=>
	), 
	'data' => array(
		'shopname' => , 
		'tablenum' =>,
		'ordernum' =>,
		'cnum'     =>,
		'mnum'     =>,
		'time'     =>,
		'menus'    =>array(
			'name'     => '',
			'num'      =>,
			'price'    =>
			'total'    =>
		)
		'paid'     =>,
		'remark'   =>,
		'phone'    =>,
		'address'  =>,
		'qrcode'   =>,
	));
 * @param  array  $op [description]
 * @return [type]     [description]
 */
function _printZD($op=array())
{
	$yprint= new Think\Yprint();
	extract($op['config']);
	$data=$op['data'];
	$menus=$data['menus'];
	unset($data['menus']);
	extract($data);
	$time=$time?:date('Y-m-d H:i:s');
$header=<<<EOD
<FB><FS2><FH2>{$shopname}</FH2></FS2></FB>

------------------------
<FB><FS>订单号:<FH2>{$ordernum}</FH2></FS></FB>
<FB><FS>桌号:{$tablenum}</FS></FB>
<FB><FS>份数:{$cnum}</FS></FB>   <FB><FS>菜品数:{$mnum}</FS></FB>
<FB><FS>{$time}</FS></FB>
------------------------
<table><tr><td>菜名</td><td>单价</td><td>总价</td></tr>
EOD;
$tprice=0;
foreach($menus as $key => $v){
	$total=$v['num']*$v['price'];
	$tprice+=$total;
	$total=number_format($total,2);
	$tr .= "<tr><td>{$v['name']} x{$v['num']}</td><td>{$v['price']}</td><td>{$total}</td></tr>";
}
$price=$price?:$tprice;
$footer=<<<EOD
</table>
------------------------
<FB><FS>合计金额  ￥{$price}</FS></FB>
<FB><FS>支付金额  ￥{$paid}</FS></FB>

备注:{$remark}

订餐电话:{$phone}
地址:{$address}

<QR>{$qrcode}</QR>
<center>欢迎有空常来！</center>

<center>本店信息技术服务由嘿果提供</center>
<center>HeyGuo.com</center>
EOD;
	$str=$header.$tr.$footer;
	$re=$yprint->action_print($partner,$machine_code,$str,$apikey,$msign);

	if ($re) {
		$re=json_decode($re,true);
		if ($re['state']==1) {
			printLog(array('params'=>$op,'desc'=>'打印成功','content'=>$str,'status'=>2));
			return true;
		}else{
			printLog(array('params'=>$op,'desc'=>'打印失败','content'=>$str,'status'=>1));
			return false;
		}
	}else{
		printLog(array('params'=>$op,'desc'=>'打印失败','content'=>$str,'status'=>1));
		return false;
	}
}

/**分单打印
 * $arrayName = array(
	'config' => array(
		'partner'=>'',
		'apikey'=>'',
		'machine_code'=>,
		'msign'=>
	), 
	'data' => array(
		'ordernum' =>,
		'time'     =>,
		'menus'    =>array(
			'name'     => '',
			'num'      =>,
			'price'    =>
			'total'    =>
		)
	));
 * @param  array  $op [description]
 * @return [type]     [description]
 */
function printFD($op=array())
{
	$yprint= new Think\Yprint();
	extract($op['config']);
	$data=$op['data'];
	$menus=$data['menus'];
	unset($data['menus']);
	extract($data);
	$time=$time?:date('Y-m-d H:i:s');

$header=<<<EOD
<FB><FS>订单号:<FH2>{$ordernum}</FH2></FS></FB>
<FB><FS2>桌号:<FH2>{$tablenum}</FH2></FS2></FB>\t<FB><FS>份数:<FH2>{$num}</FH2></FS></FB>'

<FB><FS>{$time}</FS></FB>
菜名\t数量
EOD;

foreach ($menus as $k => $v) {
	$body.="<FB><FS>{$v['name']}</FS></FB>\t<FB><FS><FW2><FH2>{$v['num']}</FH2></FW2></FS></FB>\n\n";
}

$footer=<<<EOD
<FW2><FB><FS2><FH2>=============================</FH2></FS2></FB></FW2>
EOD;

	$str=$header.$body.$footer;
	$re=$yprint->action_print($partner,$machine_code,$str,$apikey,$msign);
	if ($re) {
		$re=json_decode($re,true);
		if ($re['state']==1) {
			printLog(array('params'=>$op,'desc'=>'打印成功','content'=>$str,'status'=>2));
			return true;
		}else{
			printLog(array('params'=>$op,'desc'=>'打印失败','content'=>$str,'status'=>1));
			return false;
		}
	}else{
		printLog(array('params'=>$op,'desc'=>'打印失败','content'=>$str,'status'=>1));
		return false;
	}
}