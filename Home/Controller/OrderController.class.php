<?php
namespace Home\Controller;
use Think\Controller;
use Think\Yprint;
class OrderController extends CommonController{
	// 遍历所有桌号
	public function index(){
		$tab=M('Table');
		$map['shop_id']=$data['shop_id']=$_SESSION['user']['shop_id'];
		$map['state']=array(array('lt',2),array('gt',-1));
		$this->assign('order',M('Order')->where($map)->select());
		
		$this->assign('tab',$tab->where($data)->order('sort asc,tab_num asc')->select());
		
		$this->assign('group',M('Group')->where($data)->select());
		// 获取当天营业信息
		$index=A('Order');
		$index->getInfo(); 
		
		$this->display('table');
	}
	
	// 开台
	public function kaitai(){
		$order=M('Order');
		$tab=M('Table');
		
		$data['tab_id']=I('tab_id');//桌号id
		if($data['tab_id']>1){
			$table=$tab->where('id='.I('tab_id'))->find();
			if($table['state']==1){
				$this->error('该桌已开过桌');
			}
			$data['shop_id']=$_SESSION['user']['shop_id'];
			$data['order_time']=date("Y-m-d H:i:s");
			$data['waiter_num']=$_SESSION['user']['username'];
			$data['state']=0;
			//$data['order_num']=date('Ymd').substr(implode(NULL,array_map('ord',str_split( substr(uniqid(), 7, 13) , 1))), 0, 8);

			$current_time=date('Y-m-d',time());  //格式化当前时间,具体到天数
			$order_count=$order->where(array('order_time'=>array('like',$current_time.'%'), 'shop_id'=>$_SESSION['user']['shop_id']))->count();
			$new_order_count=sprintf("%03d", $order_count+1);
			$data['order_num']=date('Ymd',time()).$new_order_count;

			//$order_time=$order->order('order_time desc')->limit(1)->getField('order_time'); //最后一条数据的时间
			//$last_order_time=substr($order_time,0,10);  //截去最后一条数据的时间,具体到天
	//		if($last_order_time == $current_time){
	//			$last_order_num=$order->order('order_num desc')->limit(1)->getField('order_num');
	//			$data['order_num']=$last_order_num+1;
	//		}else{
	//			$data['order_num']=date('Ymd',time()).'001';
	//		}
			
			$data['nums']=I('nums'); //人数
			$id=$order->add($data); //生成订单并返回订单id
		}else{
			$this->error('该桌不存在');
		}
		//$tab_num=$tab->where('id='.I('tab_id'))->getField('tab_num');
		
		// 开台打印小票
		// $yprint=new Yprint();
		// foreach(M('Printer')->select() as $vo_print){
			// if($vo_print['master'] == 1){
				// $partner=$vo_print['partner'];
				// $apiKey=$vo_print['apikey'];
				// $machine_code=$vo_print['machine_code'];
				// $msign=$vo_print['msign'];
					
				// $content='<center><FH><FB><FS>'.'桌号:'.$tab_num.'</FS></FB></FH></center>'."\n\n";
				// $content.='<center><FH><FB><FS>'.'人数:'.$data['nums'].'</FS></FB></FH></center>';
				
				// $yprint->action_print($partner,$machine_code,$content,$apiKey,$msign);
			// }
		// }
		
		$tab->where('shop_id='.$_SESSION['user']['shop_id'].' and id='.I('tab_id').'')->setField('state',1); //设置桌子为开台
		
		$this->redirect('Order/index',array('t'=>time()));
	}
	
	// 点菜并遍历分类和菜品
	public function diancai(){
		$menu=M('Menu');
		$group=M('Group');
		$tab=M('Table');
		$order_menu=M('Order_menu');
		$order=M('Order');
		$order_menu=M('Order_menu');
		
		$map['shop_id']=$_SESSION['user']['shop_id'];
		
		// 获取当天营业信息
		$index=A('Order');
		$index->getInfo(); 
		
		$this->assign('order_id',I('order_id'));
		$this->assign('table',$tab->select());
		$this->assign('tab',$tab->where('id='.I('id'))->find());
		$this->assign('fruits',$menu->where(array('shop_id'=>$_SESSION['user']['shop_id'],'sell'=>1))->order('sort asc')->select());
		$this->assign('group',$group->where($map)->order('sort asc')->select());
		$this->display();
	}

	
	// 添加订单或加菜
	public function addorder(){
		$order_menu=M('Order_menu');
		$order=M('Order');
		M('Table')->where('id='.I('table_id'))->setField('state',2); //设置桌子状态为已点菜
		
		$map['order_id']=I('order_id');
		
		array_pop($_POST['menu_id']);
		array_pop($_POST['menu_count']);
		array_pop($_POST['menu_name']);
		array_pop($_POST['group_id']);
		array_pop($_POST['group_name']);
		array_pop($_POST['price']);
		
		// 当前订单菜品最后一条数据
		$curr_order_menu=$order_menu->where('order_id='.$map['order_id'])->order('state desc')->find();
		
		if($curr_order_menu){
			$state=$curr_order_menu['state']+1;
		}else{
			$state=0;
		}
		
		foreach($_POST['menu_id'] as $i=>$val){
			$data['fruits_id']=$map['menu_id']=$val;
			$map['state']=$state;  //第几次加菜变量
			$map['num']=$_POST['menu_count'][$i];
			$map['menu_name']=$_POST['menu_name'][$i];
			$map['group_id']=$_POST['group_id'][$i];
			$map['group_name']=$_POST['group_name'][$i];
			$map['price']=$_POST['price'][$i];
			$map['addtime']=date('Y-m-d H:i:s');
			$order_menu->add($map);
			$infos=M('Menu')->where('fruits_id='.$val)->find();
			$data['stock']=$infos['stock']-$map['num'];
			if($data['stock']<0){
				$data['stock']=0;
			}
			M('Menu')->save($data);	
		}
		
		// 总价
		$count=$order_menu->field('sum(m.price*o.num) count')->table('hg_order_menu o,hg_menu m')->where('m.shop_id='.$_SESSION['user']['shop_id'].' and o.menu_id=m.fruits_id and order_id='.$map['order_id'].'')->find();
		
		$order->where('order_id='.$map['order_id'])->setField('price',$count['count']);  //总价插入订单表
		$this->assign('price',$count['count']);
		
		// 打印小票
		$yprint=new Yprint();
		$printer=M('Printer');
		
		// 桌号
		$tab_num=$order->field('t.tab_num tab_num')->table('hg_table t,hg_order o')->where('t.id=o.tab_id and o.order_id='.$map['order_id'].'')->find();
		// 订单号
		$order_num=$order->where('order_id='.$map['order_id'])->getField('order_num');
		// 第几次加菜或点菜
		$menu=$order->field('o.menu_id oid,m.attach mattach,g.group_id gid,g.name gname,m.name mname,m.price,o.num num')->table('hg_group g,hg_menu m,hg_order_menu o')->where('g.shop_id='.$_SESSION['user']['shop_id'].' and o.menu_id=m.fruits_id and m.group_id=g.group_id and o.state='.$state.' and o.order_id='.$map['order_id'].'')->select();

		foreach($menu as $v){
			$tmp_menu = M('Menu')->where('fruits_id='.$v['oid'])->find();
			if($tmp_menu['attach']){
				$attach_menu_id = explode('|',$tmp_menu['attach']);
				foreach($attach_menu_id as $val){
					$attach_menu = M()->table('hg_group g,hg_menu m')
					->field('m.*,g.name gname')
					->where('m.group_id=g.group_id and fruits_id='.$val)
					->find();


					$map = array(
						'oid' => $attach_menu['fruits_id'],
						'mattach' => $attach_menu['attach'],
						'gid' => $attach_menu['group_id'],
						'gname' => $attach_menu['gname'],
						'mname' => $attach_menu['name'],
						'price' => $attach_menu['price'],
						'num' => $v['num']
					);
					$menu[] = $map;
				}
			}
		}
		//dump($menu);die;
		foreach($menu as $vo){
			$group_menu[$vo['gid']][]=$vo;
		}
		
		// 分别打印
		foreach($group_menu as $group_id=>$vo_list){
			$all_printer = $printer->where('shop_id='.$_SESSION['user']['shop_id'])->select();
			foreach($all_printer as $val){
				$printer_group = explode('|',$val['group_id']);
				if(in_array($group_id,$printer_group)){
					$info=$printer->where('id='.$val['id'])->find();
					
					$partner=$info['partner'];
					$apiKey=$info['apikey'];
					$machine_code=$info['machine_code'];
					$msign=$info['msign'];
					//单个菜数量
					//$num=$order_menu->field('sum(num) num')->table('hg_menu m,hg_order_menu o')->where('m.fruits_id=o.menu_id and m.group_id='.$vo['gid'].' and o.order_id='.$id.'')->find();
					$num=0;
					foreach($vo_list as $vo){
						$num+=intval($vo['num']);
					}
					
					$content='<FB><FS2>桌号:<FH2>'.$tab_num['tab_num'].'</FH2></FS2></FB>'."\t".'<FB><FS>份数:<FH2>'.$num.'</FH2></FS></FB>'."\n\n";
					$content.='订单号:'.$order_num."\n\n";
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
					'config' => $info, 
					'data' => array(
						// 'shopname' => $info['shopname'], 
						'ordernum' => $order_num, 
						'tablenum' => $tab_num['tab_num'], 
						'cnum'     => $num,
						// 'mnum'     => $mnum['num'],
						'menus'    => $vo_list,
						// 'price'	   => $info['price'],
						// 'paid'     => $info['paid'], 
						// 'remark'   => $remark,
						// 'phone'    => $info['phone'],
						// 'address'  => $info['address'],
						// 'qrcode'   => $info['qr']
					));

					$pinfo=$yprint->action_print($partner,$machine_code,$content,$apiKey,$msign);
					$arr = json_decode($pinfo,true);
					if($arr['state']==1){
						printLog(array('params'=>$printarr,'desc'=>'打印成功','content'=>$content,'status'=>2));
					}else{
						printLog(array('params'=>$printarr,'desc'=>'打印失败','content'=>$content,'status'=>1));
					}
					continue 2;
				}else{
					continue;
				}
			}
		}
		$this->redirect('Order/index',array('t'=>time()));
	}
	
	// 待结账
	public function precheck(){
		$order_id=I('order_id');
		M('Order')->where('order_id='.$order_id)->setField('state',1);
		$this->redirect('Order/index',array('order_id'=>$order_id));
	}
	
	// 结账
	public function check(){
		$table=M('Table');
		$order=M('Order');
		$data['id']=I('id');//桌号id
		$map['shop_id']=$data['shop_id']=$_SESSION['user']['shop_id'];
		$tab=$table->where($data)->find();
		$this->assign('tab',$tab);
		$order_id=I('order_id');
		$this->assign('order_id',$order_id);
		
		//$order->where('order_id='.$order_id)->setField('state',1);


		$info=$table->field('m.*,o.*')->table('hg_order_menu o,hg_menu m')->where('m.shop_id='.$_SESSION['user']['shop_id'].' and o.menu_id=m.fruits_id and order_id='.$order_id.'')->select();
		$this->assign('order_menu',$info);
		
		//总价
		$count=$table->field('sum(m.price*o.num) count')->table('hg_order_menu o,hg_menu m')->where('m.shop_id='.$_SESSION['user']['shop_id'].' and o.menu_id=m.fruits_id and order_id='.$order_id.'')->find();

		$round=M('Shop')->where('shop_id',$_SESSION['user']['shop_id'])->getField('round');
		$count=$round==1?intval($count['count']):$count['count'];

		$this->assign('count',$count);
		
		// 获取当天营业信息
		$index=A('Order');
		$index->getInfo();
		
		// 折扣信息
		$this->assign('discount',M('Discount')->where(array('shop_id'=>session('user.shop_id'),'state'=>1))->select());
		
		$this->display();
	}
	
	//验证兑换码
	public function check_code(){
		$code = M('kj_code',null,otherdb());
		$code_check = I('post.code');
		$list = $code->where("code='$code_check'")->select();

		if($list){
			$res = $code->where("code='$code_check' and status=0")->select();
			if($res){
				echo 0;die;					//代表已用过
			}else{
				$data['status'] = 0;
				$data['use_time'] = date('Y-m-d H:i:s');
				$result = $code->where("code='{$code_check}'")->save($data);
				if($result){
					echo 1;die;				//代表没用过
				}
			}
		}else{
			echo 2;die;						//代表没有
		}

	}

	// 确认结账
	public function subcheck(){
		if(IS_POST){
			$order=M('Order');
			$map['order_id']=I('order_id');
			$map['shop_id']=$_SESSION['user']['shop_id'];
			$data['pay_time']=date('Y-m-d H:i:s');
			$data['cashier_num']=$_SESSION['user']['username'];
			//$data['price']=$_POST['price'];
			$data['paid']=$_POST['paid'];
			$data['change']=$_POST['change'];
			$data['pay_way']=$_POST['pay_method'];
			$data['remark']=$_POST['remark'];
			$data['state']=2;
			$order->data($data)->where($map)->save();
			
			$tab_id=$order->where('order_id='.I('order_id'))->getField('tab_id');
			M('Table')->where('id='.$tab_id)->setField('state',0);
			
			$this->redirect('Order/index');
		}
	}
	
	
	// 取消订单

	public function del(){
		$order=M('Order');
		$data['order_id']=I('order_id');
		$data['state']=-1;
		if($order->save($data)){
			$this->success('取消成功',U('Order/index'));
		}
	}
	
	// 打印小票
	public function printer(){
		$order=M('Order');
		// 打印小票
		$yprint=new Yprint();
		$printer=M('Printer');
		
		// 总价格
		$sum=$order->field('sum(m.price*o.num) count')->table('hg_order_menu o,hg_menu m')->where('m.shop_id='.$_SESSION['user']['shop_id'].' and o.menu_id=m.fruits_id and order_id='.I('order_id').'')->find();
		// 桌号
		$tab_num=$order->field('t.tab_num tab_num')->table('hg_table t,hg_order o')->where('t.id=o.tab_id and o.order_id='.I('order_id').'')->find();
		// 菜品数
		$menu_count=M('Order_menu')->field('count(distinct menu_id) menu_count')->where(array('order_id'=>I('order_id')))->find();
		// 份数
		$nums=M('Order_menu')->field('sum(num) num')->where('order_id='.I('order_id'))->find();
		// 店铺信息
		$shop=M('Shop')->where('shop_id='.$_SESSION['user']['shop_id'])->find();
		// 订单号
		$info=$order->where('order_id='.I('order_id'))->find();
		
		$menu=$order->field('g.group_id gid,m.name mname,m.price,o.num,o.state state')->table('hg_group g,hg_menu m,hg_order_menu o')->where('g.shop_id='.$_SESSION['user']['shop_id'].' and o.menu_id=m.fruits_id and m.group_id=g.group_id and  o.order_id='.I('order_id').'')->select();
		
		// foreach($menu as $vo){
			// $menu_state[$vo['state']][]=$vo;
		// }

		// 打印总单
		foreach($printer->where('shop_id='.$_SESSION['user']['shop_id'])->select() as $vo_print){
			if($vo_print['master'] == 1){
				$partner=$vo_print['partner'];
				$apiKey=$vo_print['apikey'];
				$machine_code=$vo_print['machine_code'];
				$msign=$vo_print['msign'];

				//$content='<center>'.'  '.'</center>'."\n";//店铺logo	
				$content='<FB><FS2><FH2>'.$shop['name'].'</FH2></FS2></FB>'."\n\n";
				$content.='------------------------'."\n";
				$content.='<FB><FS>订单号:'.$info['order_num'].'</FS></FB>'."\n";
				$content.='<FB><FS>桌号:'.$tab_num['tab_num'].'</FS></FB> ';
				$content.=' <FB><FS>人数:'.$info['nums'].'</FS></FB>'."\n";
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
				$content.='<FB><FS>'.'支付金额'.'  ￥'.I('paid').'</FS></FB>'."\n\n";
				
				$content.='备注:'.I('remark')."\n\n";
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
					'ordernum' => $info['order_num'], 
					'tablenum' => $tab_num['tab_num'], 
					'cnum'     => $nums['num'],
					'mnum'     => $menu_count['menu_count'],
					'menus'    => $menu,
					'price'	   => $sum['count'],
					'paid'     => I('paid'), 
					'remark'   => I('remark'),
					'phone'    => $shop['phone'],
					'address'  => $shop['address'],
					'qrcode'   => $shop['qr']
				));


				$arr = json_decode($return_info,true);
				if($arr['state']==1){
					$print_num=$order->where('order_id='.I('order_id'))->getField('print_num');
					$print_num+=1;
					$order->data('print_num='.$print_num)->where('order_id='.I('order_id'))->save();
					printLog(array('params'=>$printarr,'desc'=>'打印成功----','content'=>$content,'status'=>2));
					$this->ajaxReturn(1);
				}else{
					printLog(array('params'=>$printarr,'desc'=>'打印失败----'.var_export($arr,true),'content'=>$content,'status'=>1));
					$this->ajaxReturn('错误代码:'.$arr['state']);
				}
			}
		}
	}
	
	// 查看订单详情
	public function ajax_details(){
		$order=M('Order');
		// 总价
		$sum=$order->field('sum(m.price*o.num) count')->table('hg_order_menu o,hg_menu m')->where('m.shop_id='.$_SESSION['user']['shop_id'].' and o.menu_id=m.fruits_id and order_id='.I('order_id').'')->find();
		// 桌号
		$tab_num=$order->field('t.tab_num tab_num')->table('hg_table t,hg_order o')->where('t.id=o.tab_id and o.order_id='.I('order_id').'')->find();
		// 菜品数
		$menu_count=M('Order_menu')->field('count(distinct menu_id) menu_count')->where(array('order_id'=>I('order_id')))->find();
		// 份数
		$nums=M('Order_menu')->field('sum(num) num')->where('order_id='.I('order_id'))->find();
		// 人数
		$people_num=$order->where('order_id='.I('order_id'))->getField('nums');
		// 订单号
		$order_num=$order->where('order_id='.I('order_id'))->getField('order_num');
		
		$menu=$order->field('m.name mname,m.price,o.num,o.state state')->table('hg_menu m,hg_order_menu o')->where('m.shop_id='.$_SESSION['user']['shop_id'].' and o.menu_id=m.fruits_id and o.order_id='.I('order_id').'')->select();
		
		foreach($menu as $vo){
			$menu_state[$vo['state']][]=$vo;
		}
		
		$content='<FB><FS>订单号:'.$order_num.'</FS></FB>'."\n";
		$content.='桌号:'.$tab_num['tab_num'].'  人数:'.$people_num."\n";
		$content.='份数:'.$nums['num'].'   '.'菜品数:'.$menu_count['menu_count']."\n";
		$content.=date('Y-m-d H:i:s')."\n";
		$content.='------------------------'."\n\n";
		foreach($menu_state as $key=>$vo){
			$addtime=M('Order_menu')->where(array('order_id'=>I('order_id'),'state'=>$key))->getField('addtime');
			if($key>0){
				$content.='第'.$key.'次加菜('.$addtime.')';
			}else if($key==0){
				$content.='正常点菜('.$addtime.')';
			}
			$content.='<table class="table table-bordered"><tr><th>菜名</th><th>数量</th><th>价格</th><th>金额</th></tr>';
			foreach($vo as $v){
				$content.='<tr><td>'.$v['mname'].'</td><td>'.$v['num'].'</td><td>'.$v['price'].'</td><td>'.$v['num']*$v['price'].'</td><tr>';
			}
			//$content.='<tr><td>'.$vo['mname'].'</td><td>'.$vo['num'].'</td><td>'.$vo['price'].'</td><td>'.$vo['num']*$vo['price'].'</td><tr>';
			$content.='</table>';
		}
		$content.='------------------------'."\n";
		$content.='合计'.'  '.$sum['count'];
		$this->ajaxReturn($content);
	}
	
	// 换桌
	public function change_tab(){
		$order=M('Order');
		$order->where('order_id='.I('order_id'))->setField('tab_id',I('tab_id'));
		$this->ajaxReturn(M('Table')->where('id='.I('tab_id'))->getField('tab_num'));
		//$this->redirect('Order/diancai',array('id'=>I('tab_id'),'order_id'=>I('order_id')));
	}
	
	// 关桌
	public function close_tab(){
		$info=M('Order_menu')->where('order_id='.I('order_id'))->select();
		if($info){
			$this->error('该桌已点菜');
		}else{
			M('Order')->where('order_id='.I('order_id'))->setField('state',-1);
			M('Table')->where('id='.I('tab_id'))->setField('state',0);
			$this->redirect('Order/index',array('t'=>time()));
		}
	}
	
	// 今日订单信息
	public function getInfo(){
		$order=M('Order');
		$data['order_time']=array('like',date('Y-m-d',time()).'%');
		$data['shop_id']=$_SESSION['user']['shop_id'];
		$data['state']=2;
		// 今日订单数
		$order_num=$order->where($data)->count();
		$this->assign('orders',$order_num);
		
		//echo $order_num;
		// 今日营业额
		$sum_price=$order->field('sum(price) sum_price')->where($data)->find();
		$this->assign('sum_price',$sum_price['sum_price']);
		
		// 今日客流
		$sum_num=$order->field('sum(nums) sum_num')->where($data)->find();
		$this->assign('sum_num',$sum_num['sum_num']);
		
		// 今日实收
		$sum_paid=$order->field('sum(paid) sum_paid')->where($data)->find();
		$this->assign('sum_paid',$sum_paid['sum_paid']);
	}
	
	// 退菜
	public function tuicai(){
		$order_id=I('order_id');
		$tab_id=I('tab_id');
		$table=M('Table')->where('id='.$tab_id)->find();
		if(IS_POST){
			$yprint=new Yprint();
			foreach($_POST['menu_id'] as $key=>$val){
				$data['num']=$_POST['menu_count_o'][$key]-$_POST['menu_count'][$key];
				$data['order_id']=$order_id;
				if($_POST['menu_count'][$key]>0){
                    $data['beta']=date('Y-m-d H:i:s').'退：'.$_POST['menu_count_o'][$key].' 改为 '.$data['num'].'';
                    // $menu=M()->field('p.*,m.name mname')->table('hg_menu m,hg_printer p,hg_order_menu o')->where('o.menu_id=m.fruits_id and  m.group_id=p.group_id and m.fruits_id='.$val.' and o.order_id='.I('order_id').' and o.id='.$_POST['id'][$key])->find();
					
					$menu = M('Order_menu')->where('id='.$_POST['id'][$key])->find();
					foreach(M('Printer')->where('shop_id='.$_SESSION['user']['shop_id'])->select() as $v){
						 if(in_array($menu['group_id'],explode('|',$v['group_id']))){
							$partner=$v['partner'];
							$apiKey=$v['apikey'];
							$machine_code=$v['machine_code'];
							$msign=$v['msign'];
							
							$content='<FB><FS>退菜</FS></FB>'."\n";
							$content.=date('Y-m-d H:i:s')."\n";
							$content.='<FB><FS2><FW2><FH2>桌号: '.$table['tab_num'].'</FH2></FW2></FS2></FB>'."\n\n";
							$content.='<FB><FS2>'.$menu['menu_name'].'  <FW2><FH2>'.$_POST['menu_count_o'][$key].'</FH2></FW2> 改为 <FW2><FH2>'.$data['num'].'</FH2></FW2></FS2></FB>'."\n";
							$content.='------------------------------\n------------------------------';

							$printarr = array(
							'config' => array(
								'partner'=>$v['partner'],
								'apikey'=>$v['apikey'],
								'machine_code'=>$v['machine_code'],
								'msign'=>$v['msign']
								), 
							'data' => array(
								'mname' => $menu['menu_name'], 
								'menu_count_o' => $_POST['menu_count_o'][$key], 
								'cur_num' => $data['num'],
								'tablenum' => $table['tab_num'], 
							));
							$pinfo=$yprint->action_print($partner,$machine_code,$content,$apiKey,$msign);
							$arr = json_decode($pinfo,true);
							if($arr['state']==1){
								printLog(array('params'=>$printarr,'desc'=>'打印成功','content'=>$content,'status'=>2));
							}else{
								printLog(array('params'=>$printarr,'desc'=>'打印失败','content'=>$content,'status'=>1));
							}
							M('order_menu')->data($data)->where('id='.$_POST['id'][$key])->save();
							$count=M('order_menu')->field('sum(m.price*o.num) count')->table('hg_order_menu o,hg_menu m')->where('m.shop_id='.$_SESSION['user']['shop_id'].' and o.menu_id=m.fruits_id and order_id='.$data['order_id'].'')->find();
							M('order')->where('order_id='.$data['order_id'])->setField('price',$count['count']);  //更改总价
							continue 2;
						 }else{
							 continue;
						 }
					}
				}
			}
			$this->redirect('Order/index');
		}
		$this->assign('tab',$table);
		$this->assign('order_id',I('order_id'));
		$this->assign('tab_id',I('tab_id'));
		$this->assign('order_menu',M('Order_menu')->field('o.*,m.name mname')->table('hg_menu m,hg_order_menu o')->where('m.fruits_id=o.menu_id and o.order_id='.$order_id)->select());
		$this->display();
	}
	
	// 补打
	public function reprint(){
		$order=M('Order');
		// 桌号
		$tab_num=$order->field('t.tab_num tab_num')->table('hg_table t,hg_order o')->where('t.id=o.tab_id and o.order_id='.I('order_id').'')->find();
		// 订单号
		$order_num=$order->where('order_id='.I('order_id'))->getField('order_num');
		//最后一条数据状态值
		$state=M('Order_menu')->where('order_id='.I('order_id'))->order('state desc')->getField('state');
		// 数量
		$num=M()->field('sum(num) nums')->table('hg_order_menu o,hg_menu m')->where('o.menu_id=m.fruits_id and order_id='.I('order_id').' and o.state='.$state.' and m.group_id='.I('group_id').'')->find();
		
		$menu=M()->field('m.name mname,o.num num')->table('hg_order_menu o,hg_menu m')->where('o.menu_id=m.fruits_id and o.order_id='.I('order_id').' and m.group_id='.I('group_id').' and o.state='.$state.'')->select();
		
		$yprint=new Yprint();
		
		$info=M('Printer')->where('group_id='.I('group_id'))->find();
		$partner=$info['partner'];
		$apiKey=$info['apikey'];
		$machine_code=$info['machine_code'];
		$msign=$info['msign'];
		
		$content='<FB><FS2><FH2>补打</FH2></FS2></FB>'."\n";
		$content.='<FB><FS2>桌号:<FH2>'.$tab_num['tab_num'].'</FH2></FS2></FB>'."\t".'<FB><FS>份数:<FH2>'.$num['nums'].'</FH2></FS></FB>'."\n\n";
		$content.='订单号:'.$order_num."\n\n";
		$content.=date('Y-m-d H:i:s')."\n";
		$content.='<FB>------------------------</FB>'."\n\n";
		$content.='菜名'."\t".'数量'."\n";
		
		foreach($menu as $vo){
			$content.='<FB><FS>'.$vo['mname'].'</FS></FB>'."\t".'<FB><FS><FW2><FH2>'.$vo['num'].'</FH2></FW2></FS></FB>'."\n\n";
		}
		$content.='<FW2><FB><FS2><FH2>=============================</FH2></FS2></FB></FW2>';

		$printarr = array(
		'config' => array(
			'partner'=>$info['partner'],
			'apikey'=>$info['apikey'],
			'machine_code'=>$info['machine_code'],
			'msign'=>$info['msign']
			), 
		'data' => array(
			// 'shopname' => $info['shopname'], 
			'ordernum' => $order_num, 
			'tablenum' => $tab_num['tab_num'], 
			'cnum'     => $num['nums'],
			// 'mnum'     => $mnum['num'],
			'menus'    => $menu
		));
		
		
		$info=$yprint->action_print($partner,$machine_code,$content,$apiKey,$msign);
		$arr = json_decode($info,true);
		if($arr['state']==1){
			printLog(array('params'=>$printarr,'desc'=>'打印成功','content'=>$content,'status'=>2));
			$this->ajaxReturn('打印成功');
		}else{
			printLog(array('params'=>$printarr,'desc'=>'打印失败','content'=>$content,'status'=>1));
			$this->ajaxReturn('打印失败,错误代码:'.$arr['state']);
		}
	}
}
?>