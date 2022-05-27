<?php
namespace Api\Controller;
use Think\Controller;
class StopController extends Controller{
	//关桌
	public function index(){
		$store = I('post.store');									//店铺id
		$account = I('post.account');								//账号名称
		$order = I('post.order');									//订单号
		$digit = I('post.digit');									//餐桌号
		// echo json_encode(I('post.'));die;
		if(M('admin')->where('shop_id = '.$store)){
			if(M('admin')->where('username = "'.$account.'"')->find()){
				if(M('order')->where('order_num = "'.$order.'"')->find()){
					if(M('table')->where('tab_num = "'.$digit.'" and shop_id ='.$store)->find()){
						M('table')->state=0;
						$aa = M('table')->where('tab_num = "'.$digit.'" and shop_id ='.$store)->save();
						M('order')->state=-1;
						$bb = M('order')->where('order_num="'.$order.'"')->save();
						$arr['flag'] = 'true';
						$arr['data']['state'] = 'true';

					}else{
						$arr['flag'] = 'false';
						$arr['errorCode'] = 103;
						$arr['errorString'] = '桌位不存在';
					}
				}else{
					$arr['flag'] = 'false';
					$arr['errorCode'] = 104;
					$arr['errorString'] = '订单不存在';
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
