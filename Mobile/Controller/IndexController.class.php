<?php
namespace Mobile\Controller;
use Think\Controller;
class IndexController extends CommonController{
	public function index(){
		
		$order=M('Order');
		$map1['shop_id']=$map['shop_id']=$data['shop_id']=$_SESSION['admin']['shop_id'];
		$data['order_time']=array('like',date('Y-m-d',time()).'%');
		$map1['state']=$data['state']=2;
		// 今日订单数
		$order_num=$order->where($data)->count();
		$this->assign('order',$order_num);
		
		// 店铺信息
		$shop=M('Shop');
		session('back_shop',$shop->where($map)->find());
		
		// 今日营业额
		$sum_price=$order->field('sum(price) sum_price')->where($data)->find();
		$this->assign('sum_price',$sum_price['sum_price']);

		// 今日实收金额
		$sum_paid=$order->field('sum(paid) sum_paid')->where($data)->find();
		$this->assign('sum_paid',$sum_paid['sum_paid']);
		
		// 今日客流
		$sum_num=$order->field('sum(nums) sum_num')->where($data)->find();
		$this->assign('sum_num',$sum_num['sum_num']);
		
		// 今日客单价
		$kdj=round($sum_price['sum_price']/$sum_num['sum_num'],2);
		$this->assign('kdj',$kdj);
		
		$map1['order_time']=array('like',date("Y-m-d",strtotime("-1 day")).'%');
		
		// 昨日订单数
		$yes_order_num=$order->where($map1)->count();
		$this->assign('yes_order',$yes_order_num);
		
		// 昨日营业额
		$yes_sum_price=$order->field('sum(price) sum_price')->where($map1)->find();
		$this->assign('yes_sum_price',$yes_sum_price['sum_price']);

		// 昨日实收金额
		$yes_sum_paid=$order->field('sum(paid) sum_paid')->where($map1)->find();
		$this->assign('yes_sum_paid',$yes_sum_paid['sum_paid']);
		
		// 昨日客流
		$yes_sum_num=$order->field('sum(nums) sum_num')->where($map1)->find();
		$this->assign('yes_sum_num',$yes_sum_num['sum_num']);
		
		// 昨日客单价
		$yes_kdj=round($yes_sum_price['sum_price']/$yes_sum_num['sum_num'],2);
		$this->assign('yes_kdj',$yes_kdj);

		$this->display();
	}
}
?>