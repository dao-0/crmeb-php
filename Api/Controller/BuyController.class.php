<?php
namespace Api\Controller;
use Think\Controller;
use Think\Yprint;
class BuyController extends Controller{
	//点菜
	public function index(){	
		$store = I('post.store');									//店铺号        int
		$digit = I('post.digit');									//餐桌id		String
		$order = I('post.order');									//订单号		String
		$num = I('post.num');										//用餐人数		int
		$time = date("Y-m-d H:i:s",time());							//点菜时间		String
		$list  = str_replace('&quot;','"',I('post.foodbean'));		//所有菜的集合 	json  将&quot;转回双引号
		$order_list = json_decode($list,true);						//所有菜的集合	array 

		$result = M('table')->where('tab_num = "'.$digit.'" and shop_id = '.$store)->find();						//查找桌位
		if($result){
			$order_num = M('order')->where('order_num = "'.$order.'"')->find(); 									//查找订单
			if($order_num){
				M('table')->where('tab_num = "'.$digit.'" and shop_id = '.$store)->setField('state',2); 			//设置桌子状态为已点菜
			
				$res = M('order_menu')->where('order_id = "'.$order_num['order_id'].'"')->order('state desc')->find();		//查找订单状态
		 		if($res){
		 			$state = $res['state']+1;
		 		}else{
		 			$state = 0;
		 		}
		 		$sum = (double)$order_num['price'];
		 		$aa = 0;
		 		$bb = 0;
		 		foreach($order_list as $key=>$val){
		 			$data['addtime'] = $time;												//点菜的时间
		 			$data['num'] = $val['shownum'];											//菜的数目
		 			$data['order_id'] = $order_num['order_id'];								//订单id
		 			$data['menu_id'] = $val['code'];										//食物id
		 			$data['state'] = $state;												//第几次加菜
		 			$menu = M('menu')->where('fruits_id = '.$val['code'])->find();
		 			$data['price'] = $menu['price'];										//食物价格
		 			$data['menu_name'] = $val['name'];										//食物名称
		 			$data['group_id'] = $menu['group_id'];									//菜类id
		 			$group = M('group')->where('group_id = '.$menu['group_id'])->find();
		 			$data['group_name'] = $group['name'];									//菜类类名
		 			$sum+=(int)$val['shownum']*(double)$menu['price'];
		 			$msg['price'] = $sum;

		 			$edit1 = M('order')->where('order_num = "'.$order.'"')->save($msg);
		 			if(!$edit1){
		 				$aa+=1;																//没有成功修改则加一
		 			}
		 			$res_add = M('order_menu')->add($data);
		 			$map['fruits_id'] = $val['code'];
					$map['stock'] = $menu['stock']-$data['num'];
					if($map['stock']<0){
						$map['stock']=0;
					}
					$edit2 = M('menu')->save($map);	
					if(!$edit2){
						$bb+=1;																//没有成功修改则加一
					}
		 		}	
		 		
		 		if($aa || $bb){																//全部修改成功则正确
		 			$arr['flag'] = 'false';
					$arr['errorCode'] = 300;
					$arr['errorString'] = '订单更新状态出错';
		 		}else{
		 			// 打印小票
					$yprint=new Yprint();
					$printer=M('printer');
					
					// 桌号
					// $tab_num=$order->field('t.tab_num tab_num')->table('hg_table t,hg_order o')->where('t.id=o.tab_id and o.order_id='.$map['order_id'].'')->find();
					// 订单号
					//$order_num=$order->where('order_id='.$map['order_id'])->getField('order_num');

					// 第几次加菜或点菜
					$menu_list=M('order')->field('g.group_id gid,g.name gname,p.id pid,p.master,m.name mname,m.price,o.num num')->table('hg_group g,hg_printer p,hg_menu m,hg_order_menu o')->where('g.shop_id='.$store.' and g.group_id=p.group_id and o.menu_id=m.fruits_id and m.group_id=g.group_id and o.state='.$state.' and o.order_id='.$order_num['order_id'].'')->select();
					foreach($menu_list as $vo){
						$group_menu[$vo['gid']][]=$vo;
						$group_print[$vo['gid']]=$vo['pid'];
					}

					// 分别打印
					foreach($group_menu as $group_id=>$vo_list){
						$info=$printer->where('id='.$group_print[$group_id])->find();
						
						$partner=$info['partner'];
						$apiKey=$info['apikey'];
						$machine_code=$info['machine_code'];
						$msign=$info['msign'];
						
						//单个菜数量
						//$num=$order_menu->field('sum(num) num')->table('hg_menu m,hg_order_menu o')->where('m.fruits_id=o.menu_id and m.group_id='.$vo['gid'].' and o.order_id='.$id.'')->find();
						$nums=0;
						foreach($vo_list as $vo){
							$nums+=intval($vo['num']);
						}
						
						$content='<FB><FS2>桌号:<FH2>'.$digit.'</FH2></FS2></FB>'."\t".'<FB><FS>份数:<FH2>'.$nums.'</FH2></FS></FB>'."\n\n";
						$content.='订单号:'.$order."\n\n";
						$content.=date('Y-m-d H:i:s')."\n";
						$content.='<FB>------------------------</FB>'."\n\n";
						$content.='菜名'."\t".'数量'."\n";
						//$content.='<FB><FS><table><tr><td>菜名</td><td>数量</td></tr>';
						foreach($vo_list as $vo){
							$content.='<FB><FS>'.$vo['mname'].'</FS></FB>'."\t".'<FB><FS><FW2><FH2>'.$vo['num'].'</FH2></FW2></FS></FB>'."\n\n";
							//$content.='<tr><td>'.$vo['mname'].'</td><td>'.$vo['num'].'</td></tr>';
						}
						//$content.='</table></FS></FB>'."\n";
						$content.='<FW2><FB><FS2><FH2>=============================</FH2></FS2></FB></FW2>';

						$printarr = array(
						'config' => array(
							'partner'=>$info['partner'],
							'apikey'=>$info['apikey'],
							'machine_code'=>$info['machine_code'],
							'msign'=>$info['msign']
						),
						'data' => array(
							'ordernum' => $order, 
							'tablenum' => $digit, 
							'cnum'     => $nums,
							'menus'    => $vo_list
						));

						$pinfo=$yprint->action_print($partner,$machine_code,$content,$apiKey,$msign);
						$arr_new = json_decode($pinfo,true);
						if($arr_new['state']==1){
							printLog(array('params'=>$printarr,'desc'=>'打印成功','content'=>$content,'status'=>2));
						}else{
							printLog(array('params'=>$printarr,'desc'=>'打印失败','content'=>$content,'status'=>1));
						}
					}
		 			$arr['flag'] = 'true';
		 			$arr['data']['state'] = 'true';
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
