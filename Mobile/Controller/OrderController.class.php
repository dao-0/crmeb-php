<?php
namespace Mobile\Controller;
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
		->order('o.order_time desc')
		->group('o.order_id')
		->select();

		foreach ($lists as $key => $value) {
			//订单里面的菜
			$menu=M('OrderMenu')->field('menu_name as name,price,num,(price*num) as total')->where("order_id={$value['order_id']}")->select();
			$lists[$key]['menu']=$menu;
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



	public function printer()
	{
		printZD(I('order_id'),I('paid'),I('remark'));
	}

}
?>