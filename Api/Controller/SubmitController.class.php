<?php
namespace Api\Controller;
use Think\Controller;
use Think\Yprint;
class SubmitController extends Controller{
	//结账
	public function index(){
		$edit = I('post.edit');										//判断结账还是打印小票     1--结账（改变订单状态）  2--打印小票（不改变转台）
		$store = I('post.store');									//店铺号     	 int
		$username = I('post.username');								//账号名	 	 String
		$digin = I('post.digit');									//桌位号		 String
		$order = I('post.order');									//订单号		 String
		$OriginalCost = I('post.OriginalCost');						//菜品原价		 double
		$PaidPrice = I('post.PaidPrice');							//实收价格		 double
		$change = I('post.change');									//找零			 double
		$PayMentType = I('post.PayMentType');						//支付类型	 	 int    1--现金 2--刷卡 5--扫码
		$remarks = I('post.remarks');								//备注			 String		
		$print = I('post.print');									//是否打印小票   int 	1--打印  2--不打印
		$time = date("Y-m-d H:i:s",time());							//结账时间  	 date
		$res = M('order')->where('order_num = "'.$order.'"')->find();
		if($res){
			if(M('table')->where('tab_num = "'.$digin.'" and shop_id ='.$store)->find()){
				M('table')->where('tab_num="'.$digin.'" and shop_id ='.$store)->setField('state',0);
				$admin = M('admin')->where('username = "'.$username.'" and shop_id ='.$store);
				$data['paid'] = $PaidPrice;
				$data['price'] = $OriginalCost;
				$data['change'] = $change;
				$data['pay_way'] = $PayMentType;
				$data['state'] = 2;
				$data['remark'] = $remarks;
				$data['pay_time'] = $time;
				$data['cashier_num'] = $username;

				if($print==1){
					// 打印小票
					$yprint=new Yprint();
					$printer=M('Printer');
					
					// 总价格
					$sum=M('order')->field('sum(m.price*o.num) count')->table('hg_order_menu o,hg_menu m')->where('m.shop_id='.$store.' and o.menu_id=m.fruits_id and order_id='.$res['order_id'].'')->find();
					// 桌号
					//$tab_num=M('order')->field('t.tab_num tab_num')->table('hg_table t,hg_order o')->where('t.id=o.tab_id and o.order_id='.I('order_id').'')->find();
					// 菜品数
					$menu_count=M('Order_menu')->field('count(distinct menu_id) menu_count')->where(array('order_id'=>$res['order_id']))->find();
					// 份数
					$nums=M('Order_menu')->field('sum(num) num')->where('order_id='.$res['order_id'])->find();
					// 店铺信息
					$shop=M('Shop')->where('shop_id='.$store)->find();
					// 订单号
					//$info=M('order')->where('order_id='.$res['order_id'])->find();
					
					$menu=M('order')->field('g.group_id gid,m.name mname,m.price,o.num,o.state state')->table('hg_group g,hg_menu m,hg_order_menu o')->where('g.shop_id='.$store.' and o.menu_id=m.fruits_id and m.group_id=g.group_id and  o.order_id='.$res['order_id'].'')->select();
					
					// foreach($menu as $vo){
						// $menu_state[$vo['state']][]=$vo;
					// }

					// 打印总单
					foreach($printer->where('shop_id='.$store)->select() as $vo_print){
						if($vo_print['master'] == 1){
							$partner=$vo_print['partner'];
							$apiKey=$vo_print['apikey'];
							$machine_code=$vo_print['machine_code'];
							$msign=$vo_print['msign'];

							//$content='<center>'.'  '.'</center>'."\n";//店铺logo	
							$content='<FB><FS2><FH2>'.$shop['name'].'</FH2></FS2></FB>'."\n\n";
							$content.='------------------------'."\n";
							$content.='<FB><FS>订单号:'.$order.'</FS></FB>'."\n";
							$content.='<FB><FS>桌号:'.$digin.'</FS></FB> ';
							$content.=' <FB><FS>人数:'.$res['nums'].'</FS></FB>'."\n";
							$content.='<FB><FS>份数:'.$nums['num'].'</FS></FB>'.'   '.'<FB><FS>'.'菜品数:'.$menu_count['menu_count'].'</FS></FB>'."\n";
							$content.='<FB><FS>'.date('Y-m-d H:i:s').'</FS></FB>'."\n";
							$content.='------------------------'."\n";
							$content.='<table><tr><td>菜名</td><td>单价</td><td>总价</td></tr>';
							foreach($menu as $v){
								$content.='<tr><td>'.$v['mname'].' x'.$v['num'].'</td><td>'.$v['price'].'</td><td>'.number_format($v['num']*$v['price'],2).'</td></tr>';
							}
							$content.='</table>'."\n\n";
							$content.='------------------------'."\n";
							$content.='<FB><FS>'.'合计金额'.'  ￥'.$sum['count'].'</FS></FB>'."\n";
							$content.='<FB><FS>'.'支付金额'.'  ￥'.$PaidPrice.'</FS></FB>'."\n\n";
							
							$content.='备注:'.$remarks."\n\n";
							$content.='订餐电话:'.$shop['phone']."\n地址:".$shop['address']."\n\n";
							$content.='<QR>'.$shop['qr'].'</QR>\n';
							$content.='<center>欢迎有空常来！</center>'."\n\n";
							$content.='<center>本店信息技术服务由嘿果提供</center>'."\n";
							$content.='<center>HeyGuo.com</center>';
							
							$return_info=$yprint->action_print($partner,$machine_code,$content,$apiKey,$msign);

							$printarr = array(
							'config' => $vo_print, 
							'data' => array(
								'shopname' => $shop['name'], 
								'ordernum' => $order, 
								'tablenum' => $digin, 
								'cnum'     => $nums['num'],
								'mnum'     => $menu_count['menu_count'],
								'menus'    => $menu,
								'price'	   => $OriginalCost,
								'paid'     => $PaidPrice, 
								'remark'   => $remarks,
								'phone'    => $shop['phone'],
								'address'  => $shop['address'],
								'qrcode'   => $shop['qr']
							));


							$array = json_decode($return_info,true);
							if($array['state']==1){
								$print_num=M('order')->where('order_id='.$res['order_id'])->getField('print_num');
								$print_num+=1;
								M('order')->data('print_num='.$print_num)->where('order_id='.$res['order_id'])->save();
								printLog(array('params'=>$printarr,'desc'=>'打印成功----','content'=>$content,'status'=>2));
								//$this->ajaxReturn(1);
							}else{
								printLog(array('params'=>$printarr,'desc'=>'打印失败----'.var_export($array,true),'content'=>$content,'status'=>1));
								//$this->ajaxReturn('错误代码:'.$array['state']);
							}
						}
					}
				}
				if($edit==1){
					$result = M('order')->where('order_num = "'.$order.'"')->save($data);
					if($result){
						$arr['flag'] = 'true';
						$arr['data']['state'] = 'true';
					}else{
						$arr['flag'] = 'false';
						$arr['errorCode'] = 200;
						$arr['errorString'] = '订单出错';
					}
				}else{
					$arr['flag'] = 'true';
					$arr['data']['state'] = 'true';
				}
				
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







		echo json_encode($arr); 
		return json_encode($arr);
	}
}
?>
