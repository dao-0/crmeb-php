<?php
namespace Api\Controller;
use Think\Controller;
class StartController extends Controller{
	//开桌
	public function index(){
		$shop_id  = I('post.store');										//店铺号 	int
		$account  = I('post.account');										//用户账号 	String
		$digit  = I('post.digit');											//餐桌号 	String
		$num  = I('post.num');												//用餐人数	int
		$time  = time();													//开桌时间 	String

		if(M('admin')->where('shop_id = '.$shop_id)->find()){
			if(M('admin')->where('username = "'.$account.'"')->find()){
				$res = M('table')->where('tab_num = "'.$digit.'" and shop_id ='.$shop_id)->find();
				if($res){
					$arr['flag'] = 'true';
					M('table')->state = 1;
					$change = M('table')->where('tab_num = "'.$digit.'" and shop_id ='.$shop_id)->save();

					$data['tab_id'] = $res['id'];										//桌号id
					$data['shop_id'] = $shop_id;										//商铺号
					$data['order_time'] = date("Y-m-d H:i:s",$time);					//开桌时间
					$data['waiter_num'] = $account;										//用户账号
					$data['state'] = 0;													//订单状态
					$current_time = date('Y-m-d',time());  								//格式化当前时间,具体到天数
					$order_count = M('order')->where(array('order_time'=>array('like',$current_time.'%'), 'shop_id'=>$shop_id))->count();
					$new_order_count = sprintf("%03d", $order_count+1);
					$data['order_num'] = date('Ymd',time()).$new_order_count;	
					$data['nums'] = $num; 												//人数	
					$result = M('order')->add($data);
					$arr['data']['order'] = $data['order_num'];							//订单号
					$arr['data']['status'] = (bool)true;
				}else{	
					$arr['flag'] = 'false';
					$arr['errorCode'] = 103;
					$arr['errorString'] = '桌位不存在';
				}
			}else{
				$arr['flag'] = 'false';
				$arr['errorCode'] = 101;
				$arr['errorString'] = '用户不存在';
			}
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
