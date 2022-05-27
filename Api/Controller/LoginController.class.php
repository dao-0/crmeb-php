<?php
namespace Api\Controller;
use Think\Controller;
class LoginController extends Controller{
	//登陆
	public function login(){
		$data['shop_id']  = I('post.store');
        $data['username'] = I('post.account');
        $data['password'] = md5(I('post.password'));
		$res = M('admin')->where($data)->find();
		$arr = array();
		if($res){
			$arr['flag'] = 'true';
			$arr['data']['power'] = (int)$res['level'];			//用户等级
			$tab = M('table')->where('shop_id = '.$data['shop_id'])->select();						//对应商铺的桌位

			$map['state']=array(array('lt',2),array('gt',-1));
			$map['shop_id'] = $data['shop_id'];
			$order = M('Order')->where($map)->select();												//对应商铺的订单

			$discount = M('discount')->where('shop_id = '.$data['shop_id'])->order('sort asc')->select();
			foreach ($discount as $key => $value) {
				$arr['data']['discount'][$key]['id'] = (int)$value['id'];								//折扣id
				$arr['data']['discount'][$key]['name'] = $value['name'];								//折扣名称
				$arr['data']['discount'][$key]['rate'] = (double)$value['discount']/10;					//折扣率
				$arr['data']['discount'][$key]['sort'] = (int)$value['sort'];							//排序
				$arr['data']['discount'][$key]['state'] = (int)$value['state'];							//折扣的状态
				$arr['data']['discount'][$key]['type'] = (int)$value['type'];							//类型 0--抹零 1--固定折扣 2--手动输入
			}

			for($i=0;$i<count($tab);$i++){
				$arr['data']['table'][$i]['digit'] = $tab[$i]['tab_num'];							//座位号
				$arr['data']['table'][$i]['state'] = (int)$tab[$i]['state'];						//餐座状态
				for($m=0;$m<count($order);$m++){		
					if($tab[$i]['id'] == $order[$m]['tab_id']){	
						$arr['data']['table'][$i]['num'] = (int)$order[$m]['nums'];					//就餐人数
						$arr['data']['table'][$i]['price'] = (double)$order[$m]['price'];			//就餐价格
						$arr['data']['table'][$i]['time'] = intval((time()-strtotime($order[$m]['order_time']))/60);		//就餐时间
					}
				}
			}

			$group = M('group')->where('shop_id = '.$data['shop_id'])->select();			//菜品分类
			$menu = M('menu ')->where('shop_id = '.$data['shop_id'])->select();				//对应商铺的菜名
			for($a=0;$a<count($group);$a++){
				$c = 0;
				for($b=0;$b<count($menu);$b++){	
					$arr['data']['food'][$a]['category'] = $group[$a]['name'];									//菜类
					if($group[$a]['group_id'] == $menu[$b]['group_id']){
						$arr['data']['food'][$a]['kind'][$c]['code'] = (int)$menu[$b]['fruits_id'];				//食物id
						$arr['data']['food'][$a]['kind'][$c]['name'] = $menu[$b]['name'];						//菜名
						$arr['data']['food'][$a]['kind'][$c]['unitprice'] = (double)$menu[$b]['price'];			//价格
						$arr['data']['food'][$a]['kind'][$c]['amount'] = (double)$menu[$b]['stock'];			//库存量
						$arr['data']['food'][$a]['kind'][$c]['shownum'] = 0;									//数目
						$c++;
					}
					
				}
			}
			// echo json_encode($arr);die;
		}else if(M('admin')->where('password != "'.$data['password'].'" and username = "'.$data['username'].'"')->find()){
			$arr['flag'] = 'false';
			$arr['errorCode'] = 100;
			$arr['errorString'] = '用户密码错误';
		}else if(M('admin')->where('username != "'.$data['username'].'" and password = "'.$data['password'].'"')->find()){
			$arr['flag'] = 'false';
			$arr['errorCode'] = 101;
			$arr['errorString'] = '用户不存在';
		}else{
			$arr['flag'] = 'false';
			$arr['errorCode'] = 101;
			$arr['errorString'] = '用户不存在';
		}
		echo json_encode($arr);
		return json_encode($arr);
	}
}
?>