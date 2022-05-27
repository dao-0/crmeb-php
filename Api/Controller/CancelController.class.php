<?php
namespace Api\Controller;
use Think\Controller;
use Think\Yprint;
class CancelController extends Controller{
	//退菜
	public function index(){
		$store = I('post.store');										//店铺号
		$digit = I('post.digit');										//桌位号
		$order = I('post.order');										//订单号
		$list  = str_replace('&quot;','"',I('post.foodbean'));			//退菜的集合 	json  将&quot;转回双引号
		$order_list = json_decode($list,true);							//退菜的集合	array 
		if(M('admin')->where('shop_id = '.$store)->find()){
			if(M('table')->where('shop_id = '.$store.' and tab_num = "'.$digit.'"')->find()){
				$res = M('order')->where('order_num = "'.$order.'"')->find();
				if($res){
					$is_true = 0;
					foreach ($order_list as $key => $value) {
						$data = M('order_menu')->where('id = '.$value['code'])->find();
						if(($data['num']-$value['num'])<0){
							$arr['flag'] = 'false';
							$arr['errorCode'] = 302;
							$arr['errorString'] = '退菜数量出错';
						}else{
							$yprint=new Yprint();
							$drop = $data['num']-$value['num'];
							$result = M('order_menu')->where('id = '.$value['code'])->setDec('num',$value['num']);							
							if($result){
								$is_true+=1;
							}
							$result_find = M('order_menu')->where('id = '.$value['code'])->find();
							$map['beta']=date('Y-m-d H:i:s').'退：'.$result_find['num'].' 改为 '.$drop.'';
							M('order_menu')->where('id = '.$value['code'])->save($map);

							$count=M('order_menu')->field('sum(m.price*o.num) count')->table('hg_order_menu o,hg_menu m')->where('m.shop_id='.$store.' and o.menu_id=m.fruits_id and order_id="'.$res['order_id'].'"')->find();
							$save['price'] = $count['count'];
							M('order')->where('`order_num` = "'.$order.'"')->save($save);  			//更改总价
		                    $menu=M()->field('p.*,m.name mname')->table('hg_menu m,hg_printer p,hg_order_menu o')->where('o.menu_id=m.fruits_id and  m.group_id=p.group_id and m.fruits_id='.$result_find['group_id'].' and o.order_id='.$res['order_id'].' and o.id='.$value['code'])->find();
							
							$partner=$menu['partner'];
							$apiKey=$menu['apikey'];
							$machine_code=$menu['machine_code'];
							$msign=$menu['msign'];
							
							$content='<FB><FS>退菜</FS></FB>'."\n";
							$content.=date('Y-m-d H:i:s')."\n";
							$content.='<FB><FS2><FW2><FH2>桌号: '.$digit.'</FH2></FW2></FS2></FB>'."\n\n";
							$content.='<FB><FS2>'.$menu['mname'].'  <FW2><FH2>'.$result_find['num'].'</FH2></FW2> 改为 <FW2><FH2>'.$drop.'</FH2></FW2></FS2></FB>'."\n";
							$content.='------------------------------\n------------------------------';

							$printarr = array(
							'config' => array(
								'partner'=>$menu['partner'],
								'apikey'=>$menu['apikey'],
								'machine_code'=>$menu['machine_code'],
								'msign'=>$menu['msign']
								), 
							'data' => array(
								'mname' => $menu['mname'], 
								'menu_count_o' => $result_find['num'], 
								'cur_num' => $drop,
								'tablenum' => $digit
							));
							$pinfo=$yprint->action_print($partner,$machine_code,$content,$apiKey,$msign);
							$array = json_decode($pinfo,true);
							if($array['state']==1){
								printLog(array('params'=>$printarr,'desc'=>'打印成功','content'=>$content,'status'=>2));
							}else{
								printLog(array('params'=>$printarr,'desc'=>'打印失败','content'=>$content,'status'=>1));
							}

						}
					}

					
					if($is_true){
						$arr['flag'] = 'true';
						$arr['data']['state'] = 'true';
					}else{
						$arr['flag'] = 'false';
						$arr['errorCode'] = 301;
						$arr['errorString'] = '数量更新出错';
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
