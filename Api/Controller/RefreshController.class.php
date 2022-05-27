<?php
namespace Api\Controller;
use Think\Controller;
class RefreshController extends Controller{
	//刷新
	public function index(){
		$store = I('post.store');
		
		if(M('admin')->where('shop_id = '.$store)->find()){
			$arr['flag'] = 'true';
			$arr['data']['RefreshState'] = 'true';
			$res = M('table')->where('shop_id = '.$store)->select();
			$map['shop_id'] = $store;
			$map['state'] = array(array('lt',2),array('gt',-1));
			$result = M('order')->where($map)->select();
			foreach ($res as $key => $value) {
				$arr['data']['table'][$key]['digit'] = $value['tab_num'];
				$arr['data']['table'][$key]['state'] = $value['state'];
				$c = 0;
				foreach ($result as $k => $val) {
					if($value['id'] == $val['tab_id']){
						$arr['data']['table'][$key]['num'] = $val['nums'];
						$arr['data']['table'][$key]['price'] = $val['price'];
						$arr['data']['table'][$key]['time'] = intval((time()-strtotime($val['order_time']))/60);
					}
				}
			}
			$order=M('Order');
			$data['order_time']=array('like',date('Y-m-d',time()).'%');
			$data['shop_id']=$store;
			$data['state']=2;
			// 台数
			$order_num=$order->where($data)->count();
			$arr['todayfrequency'] = $order_num;
			//echo $order_num;
			// 营业额
			$sum_price=$order->field('sum(price) sum_price')->where($data)->find();
			$arr['todaysales'] = $sum_price['sum_price'];
			// 人数
			$sum_num=$order->field('sum(nums) sum_num')->where($data)->find();		
			$arr['peoplenum'] = $sum_num['sum_num'];
			// 实收
			$sum_paid=$order->field('sum(paid) sum_paid')->where($data)->find();
			$arr['truesales'] = $sum_paid['sum_paid'];
			
		}else{
			$arr['flag'] = 'false';
			$arr['errorCode'] = 102;
			$arr['errorString'] = '商铺不存在';
		}
		echo json_encode($arr); 
		return json_encode($arr);
	}

	
}
?>
