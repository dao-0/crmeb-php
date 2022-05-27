<?php
namespace Api\Controller;
use Think\Controller;
class OrderController extends Controller{
	//用餐情况
	public function index(){	
		$store = I('post.store');									//店铺号        int
		$digit = I('post.digit');									//餐桌号		String
		$order = I('post.order');									//订单号		String
		$time = date("Y-m-d H:i:s",time());							//点菜时间		String
		if(M('table')->where('shop_id ='.$store.' and tab_num = "'.$digit.'"')->find()){
			$res = M('order')->where('order_num = "'.$order.'"')->find();
			// 菜品数
			$menu_count=M('order_menu')->field('count(distinct menu_id) menu_count')->where(array('order_id'=>$res['order_id']))->find();
			// 份数
			$nums=M('order_menu')->field('sum(num) num')->where('order_id='.$res['order_id'])->find();
			if($res){
				$arr['flag'] = 'true';
				$arr['data']['orderid'] = $order;							//订单号
				$arr['data']['digit'] = $digit;								//桌号
				$arr['data']['peoplenum'] = $res['nums'];					//人数
				$arr['data']['counts'] = $nums['num'];						//份数
				$arr['data']['foodtypes'] = $menu_count['menu_count'];		//菜品数
				$arr['data']['nowtime'] = $time;							//订单时间
				$arr['data']['price'] = (double)$res['price'];				//合计
				$result = M('order_menu')->where('order_id = '.$res['order_id'])->select();

				foreach ($result as $k=>$v){
					$temp[]=$v['addtime'];
				}
				$temp=array_unique($temp); 		//去重

				$a = 0;
				foreach ($temp as $key => $value) {
					$arr['data']['list'][$a]['time'] = $value;				
					$b = 0;
					foreach ($result as $k => $val) {
						if($value == $val['addtime']){
							$arr['data']['list'][$a]['food'][$b]['name'] = $val['menu_name'];							//菜名
							$arr['data']['list'][$a]['food'][$b]['num'] = $val['num'];									//数量
							$arr['data']['list'][$a]['food'][$b]['unitprice'] = (double)$val['price'];					//单价
							$arr['data']['list'][$a]['food'][$b]['allprice'] = $val['num']*(double)$val['price'];		//金额
							$arr['data']['list'][$a]['food'][$b]['state'] = $val['state'];								//次数		
							$arr['data']['list'][$a]['food'][$b]['code'] = $val['id'];									//菜的id
							$b++;
						}
					}
					$a++;
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
