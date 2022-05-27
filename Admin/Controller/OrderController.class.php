<?php
namespace Admin\Controller;
use Think\Controller;
class OrderController extends CommonController{
	public function index(){

		$shop_id=session('admin.shop_id');

		$start_time=I('start_time')?strtotime(I('start_time')):'';
    	$end_time=I('end_time')?strtotime(I('end_time')):'';

    	if($end_time){
    		$end_time=getOneDay($end_time);
    		$end_time=$end_time['et'];
    	}else{
    		$end_time=getOneDay();
    		$end_time=$end_time['et'];
    	}

    	if ($start_time) {
    		$start_time=getOneDay($start_time);
    		$start_time=$start_time['st'];
    	}else{
    		$start_time=getOneDay();
    		$start_time=$start_time['st'];
    	}
    	$et=date('Y-m-d H:i:s',$end_time);$st=date('Y-m-d H:i:s',$start_time);
		
		$w=" o.shop_id={$shop_id} and o.order_time between '{$st}' and '{$et}' and o.state>-1";

		$lists=M()->table('hg_order o')
		->join('hg_table t on o.tab_id=t.id','left')
		->join('hg_order_menu om on o.order_id=om.order_id','left')
		->field("o.*,ifnull(t.tab_num,'桌号已删除') as tab_num,count(distinct om.menu_id) as cps,sum(om.num) as cs")
		->where($w)
		->group('o.order_id')
		->select();

		foreach ($lists as $key => $value) {
			$lists[$key]['payway']=getPayway($value['pay_way'],true);
			$lists[$key]['state']=$lists[$key]['state']==-1?'取消':$lists[$key]['state']==2?'已完成':'待付款';
			$lists[$key]['pay_time']=$value['state']==2?$lists[$key]['pay_time']:'未结账';
			$cprice+=$lists[$key]['price'];
			$cpaid+=$lists[$key]['paid'];
			$ccps+=$lists[$key]['cps'];
			$ccs+=$lists[$key]['cs'];
		}
		$iw=I();
		$iw['start_time']=date('Y-m-d',$start_time);
		$iw['end_time']=date('Y-m-d',$end_time);
		
		if ($iw['selt']!=='0') {
			$iw['selt']=$iw['selt']?:1;
		}
		

		$countarr=array('cprice'=>$cprice,'cpaid'=>$cpaid,'ccps'=>$ccps,'ccs'=>$ccs);
		$this->assign('count',$countarr);
		$this->assign('w',$iw);
		$this->assign('orders',$lists);
		$this->display();
	}

	// 查看订单详情
	public function ajax_details(){
		$shop_id=session('admin.shop_id');
		$order_id=I('order_id');
		$order=M('Order');
		// 总价
		$sum=$order->field('sum(price*num) count')->table('hg_order_menu o')->where("order_id={$order_id}")->find();

		// 桌号
		$orderinfo=$order->field("ifnull(t.tab_num,'桌号已删除') as tab_num,o.order_time,o.pay_time,o.price,o.paid")
		->table('hg_order o')
		->join('hg_table t on t.id=o.tab_id','left')
		->where('o.order_id='.$order_id.'')
		->find();

		// 菜品数
		$menu_count=M('OrderMenu')->field('count(distinct menu_id) menu_count')->where("order_id={$order_id}")->find();

		// 分数
		$nums=M('OrderMenu')->field('sum(num) num')->where("order_id={$order_id}")->find();
		
		$menu=M('OrderMenu')->field('menu_name as mname,price,num,beta')
		->where("order_id={$order_id}")
		->select();

		
		$content='桌号:'.$orderinfo['tab_num']."\n";
		$content.='份数:'.$nums['num'].'   '.'菜品数:'.$menu_count['menu_count']."\n";
		$content.='下单时间：'.$orderinfo['order_time']."\n";
		$content.='结账时间：'.($orderinfo['pay_time']?:date('Y-m-d H:i:s'))."\n";
		$content.='------------------------'."\n";
		$content.='<table class="table table-bordered"><tr><th>菜名</th><th>数量</th><th>价格</th><th>金额</th><th></th></tr>'."\n";
		foreach($menu as $vo){
			$content.='<tr><td>'.$vo['mname'].'</td><td>'.$vo['num'].'</td><td>'.$vo['price'].'</td><td>'.$vo['num']*$vo['price'].'</td><td>'.$vo['beta'].'</td><tr>';
		}
		$content.='</table>'; 	
		$content.='------------------------'."\n";
		$content.='营业额'.'  '.$orderinfo['price'].'  ';
		$content.='实收额'.'  '.$orderinfo['paid'];
		$this->ajaxReturn($content);
	}

	public function printer()
	{
		printZD(I('order_id'),I('paid'),I('remark'));
	}

	public function resetMysql()
	{
		$lists=M()
		->field('om.id,m.price,m.name,g.group_id,g.name as gname')
		->table('hg_order_menu om')
		->join('hg_menu m on om.menu_id=m.fruits_id','left')
		->join('hg_group g on m.group_id=g.group_id','left')
		->select();

		foreach ($lists as $k => $v) {
			M('OrderMenu')->where(array('id'=>$v['id']))->save(array('menu_name'=>$v['name'],'price'=>$v['price'],'group_id'=>$v['group_id'],'group_name'=>$v['gname']));
		}
		echo "<pre />";
		print_r($lists);
	}
}
?>