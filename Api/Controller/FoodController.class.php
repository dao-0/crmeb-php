<?php
namespace Api\Controller;
use Think\Controller;
class FoodController extends Controller{
	//结账情况
	public function index(){	
		$store = I('post.store');									//店铺号        int
		$digit = I('post.digit');									//餐桌号		String
		$order = I('post.order');									//订单号		String
		$shop = M('shop')->where('shop_id='.$store)->find();
		if(M('table')->where('shop_id ='.$store.' and tab_num = "'.$digit.'"')->find()){
			$res = M('order')->where('order_num = "'.$order.'"')->find();
			// 菜品数
			$menu_count=M('order_menu')->field('count(distinct menu_id) menu_count')->where(array('order_id'=>$res['order_id']))->find();
			// 份数
			$nums=M('order_menu')->field('sum(num) num')->where('order_id='.$res['order_id'])->find();
			if($res){
				$arr['flag'] = 'true';
				$arr['data']['price'] = (double)$res['price'];				//合计
				$arr['data']['shop_name'] = $shop['name'];					//店铺名称
				$arr['data']['phone'] = $shop['phone'];						//店铺电话
				$arr['data']['address'] = $shop['address'];					//店铺地址
				$arr['data']['menu_count'] = $menu_count['menu_count'];		//订单菜品数
				$arr['data']['nums'] = $nums['num'];						//订单份数
				$arr['data']['p_num'] = $res['nums'];						//订单人数

				$result = M('order_menu')->where('order_id = '.$res['order_id'])->select();			

				foreach ($result as $k => $val) {
					$arr['data']['food'][$k]['name'] = $val['menu_name'];							//菜名
					$arr['data']['food'][$k]['num'] = $val['num'];									//数量
					$arr['data']['food'][$k]['unitprice'] = (double)$val['price'];					//单价
					$arr['data']['food'][$k]['allprice'] = $val['num']*(double)$val['price'];		//金额
					$arr['data']['food'][$k]['state'] = $val['state'];								//次数		
					$arr['data']['food'][$k]['code'] = $val['id'];									//菜的id
				}
			}else{
				$arr['flag'] = 'false';
				$arr['errorCode'] = 104;
				$arr['errorString'] = '订单不存在';
			}
		}else{
			$arr['flag'] = 'false';
			$arr['errorCode'] = 103;
			$arr['errorString'] = '桌位不存在';
		}


		echo json_encode($arr); 
		return json_encode($arr);
	}
}
?>
