<?php
/**
 * 
 * @authors Liujinbi (857053791@QQ.com)
 * @date    2017-05-27 18:19:39
 * @version $Id$
 */
namespace Admin\Controller;
use Think\Controller;
class MgcountController extends CommonController {
    
    public function index()
    {
        ini_set("memory_limit","2048M");

    	$start_time=I('start_time')?strtotime(I('start_time')):'';
    	$end_time=I('end_time')?strtotime(I('end_time')):'';

    	if($end_time){
    		$end_time=getOneDay($end_time);
    		$end_time=$end_time['et'];
    	}else{
    		$end_time=getOneDay();
    		$end_time=$end_time['et'];
    	}

    	if ($start_time) {
    		$start_time=getOneDay($start_time);
    		$start_time=$start_time['st'];
    	}else{
    		$start_time=getDayRegion($end_time);
    		$start_time=$start_time['st'];
    	}
      //统计核销量
      $code = M('kj_code',null,otherdb());
      $list = $code->where('status=0')->select();
      $num = 0;
      foreach($list as $val){
        if(strtotime($val['use_time'])>=$start_time && strtotime($val['use_time'])<=$end_time){
          $num++;
        }
      }  

      $this->assign('num',$num); 

    	$orders=$this->getOrdersByTime($start_time,$end_time);
    	
        $payWayCount=$this->payWayCount($orders);
        unset($payWayCount[0]);
        foreach ($payWayCount as $key => $value) {
            $paywaymap.='"'.$value['name'].'",';
            // $paywayval[]=array('name'=>$value['name'],'value'=>count($value['value']));
            $tmp_pwc=0;
            foreach ($value['value'] as $k => $v) {
                $tmp_pwc+=$v['paid'];
            }
            $paywayval[]=array('name'=>$value['name'],'value'=>$tmp_pwc);
            $pwc[$value['name']]=$tmp_pwc;
        }
        $paywaymap='['.rtrim($paywaymap,',').']';

        $iw=I();
        $iw['start_time']=date('Y-m-d',$start_time);
        $iw['end_time']=date('Y-m-d',$end_time);
        
        if ($iw['selt']!=='0') {
            $iw['selt']=$iw['selt']?:9;
        }

		$order = M('order')
		->where(['order_time' => ['between',[date('Y-m-d H:i:s',$start_time),date('Y-m-d H:i:s',$end_time)]],'state' => 2,'shop_id' => session('admin.shop_id')])
		->select();

		foreach($order as $val){
			$tmp_time = intval((strtotime($val['pay_time'])-strtotime($val['order_time']))/60);
			if($tmp_time >= 20){
				$used_time[] = $tmp_time;
			}else{
				continue;
			}
		}
		foreach($used_time as $v){
			$sum_time += $v;
		}

        $this->assign('Pwc',$pwc);
        $this->assign('CatePC',$this->catePrcieCount($start_time,$end_time));
        $this->assign('Payway',array('map'=>$paywaymap,'val'=>json_encode($paywayval))); //支付方式
        $this->assign('CpCount',$this->cpCount($start_time,$end_time)); //菜品统计
        $this->assign('Phmap',$this->SalesPh($start_time,$end_time)); //菜排行
        $this->assign('Hmap',$this->hoursVag($orders,$start_time,$end_time)); //小时统计
        $this->assign('ETRmap',$this->countETRS($orders,$start_time,$end_time)); //营业额 台数 人数统计
        $this->assign('Tcount',$this->countETRD($orders)); //顶部统计
		$this->assign('avg_time',intval($sum_time/sizeof($used_time)));
        // $this->assign('w',array('start_time'=>date('Y-m-d',$start_time),'end_time'=>date('Y-m-d',$end_time)));
        $this->assign('w',$iw);
        $this->display();
    }

    public function catePrcieCount($st,$et)
    {
        $st=date('Y-m-d H:i:s',$st);
        $et=date('Y-m-d H:i:s',$et);
        // $w=array('o.order_time'=>array('between',array($st,$et)),'o.state'=>2,'o.shop_id'=>session('admin.shop_id'));
        // $lists=M()->table('hg_order o, hg_order_menu om, hg_menu m, hg_group g')
        // ->field("o.order_id,om.menu_id,om.num,m.price,m.name,g.name as gname")
        // ->where($w)
        // ->where("o.order_id=om.order_id and om.menu_id=m.fruits_id and m.group_id=g.group_id")
        // ->order('g.sort asc')
        // ->select();
        // $tmp=array();
        // foreach ($lists as $key => $value) {
        //     if (empty($value['gname'])) {
        //       $tmp['其他']+=$value['num']*$value['price'];
        //     }else{
        //       $tmp[$value['gname']]+=$value['num']*$value['price'];
        //     }
        // }
        $w=array('o.order_time'=>array('between',array($st,$et)),'o.state'=>2,'o.shop_id'=>session('admin.shop_id'));
        $lists=M()
        ->table('hg_order o')
        ->field("o.order_id,om.menu_id,om.num,om.price,om.menu_name,om.group_id")
        ->join('hg_order_menu om on o.order_id=om.order_id','left')
        ->where($w)
        ->select();

        $groups=M('Group')->field('group_id,name')->where(array('shop_id'=>session('admin.shop_id')))->order('sort asc')->select();
        $groupIds = array_field($groups, 'group_id');
        $tmp=array();
        foreach ($lists as $key => $value) {
            if (in_array($value['group_id'], $groupIds)) {
              $tmp[$value['group_id']]+=$value['num']*$value['price'];
            }else{
              $tmp[0]+=$value['num']*$value['price'];
            }
        }
        $retmp=array();
        // foreach ($groups as $key => $value) {
        //   if (isset($tmp[$value['group_id']])) {
        //     $retmp[$value['name']]=round($tmp[$value['group_id']],2);
        //   }else{
        //     $retmp[$value['name']]=0;
        //   }
        // }
        // if (isset($tmp[0])) {
        //   $retmp['其他']=$tmp[0];
        // }
        foreach ($groups as $key => $value) {
          if (isset($tmp[$value['group_id']])) {
            $retmp[$value['name']]=array('id'=>$value['group_id'],'value'=>round($tmp[$value['group_id']],2));
          }else{
            $retmp[$value['name']]=array('id'=>$value['group_id'],'value'=>0);
          }
        }
        if (isset($tmp[0])) {
          $retmp['其他']=array('id'=>0,'value'=>round($tmp[0],2));
        }
        return $retmp;
    }

    /**
     * [菜品统计]
     * @param  [type] $st [开始时间]
     * @param  [type] $et [结束时间]
     * @return [type]     [description]
     */
    public function cpCount($st,$et)
    {
    // 	$st=date('Y-m-d H:i:s',$st);
    // 	$et=date('Y-m-d H:i:s',$et);
		  // $w=array('om.addtime'=>array('between',array($st,$et)),'o.state'=>2,'o.shop_id'=>session('admin.shop_id'));
  		// $order_lists=M()
  		// ->table('hg_order_menu om,hg_menu m,hg_group g,hg_order o')
  		// ->field("sum(om.num) as num,g.name")
  		// ->where('om.menu_id=m.fruits_id and g.group_id=m.group_id and o.order_id=om.order_id')
  		// ->group('g.group_id')
  		// ->where($w)->select();

  		// foreach ($order_lists as $key => $value) {
    //   		$cpmap.='"'.$value['name'].'",';
    //   		$cpmapval[$key]['name']=$value['name'];
    //   		$cpmapval[$key]['value']=$value['num'];
    //   	}
    //   	$cpmap='['.rtrim($cpmap,',').']';
  		// return array('map'=>$cpmap,'val'=>json_encode($cpmapval));
      $st=date('Y-m-d H:i:s',$st);
      $et=date('Y-m-d H:i:s',$et);
      $w=array('o.order_time'=>array('between',array($st,$et)),'o.state'=>2,'o.shop_id'=>session('admin.shop_id'));
      $lists=M()
      ->table('hg_order o')
      ->field("o.order_id,om.menu_id,om.num,om.price,om.menu_name,om.group_id")
      ->join('hg_order_menu om on o.order_id=om.order_id','left')
      ->where($w)
      ->select();

      $groups=M('Group')->field('group_id,name')->where(array('shop_id'=>session('admin.shop_id')))->order('sort asc')->select();
      $groupIds = array_field($groups, 'group_id');
      $tmp=array();
      foreach ($lists as $key => $value) {
          if (in_array($value['group_id'], $groupIds)) {
            $tmp[$value['group_id']]+=$value['num']*$value['price'];
          }else{
            $tmp[0]+=$value['num']*$value['price'];
          }
      }
      $retmp=array();
      foreach ($groups as $key => $value) {
        if (isset($tmp[$value['group_id']])) {
          $retmp[$value['name']]=round($tmp[$value['group_id']],2);
        }else{
          $retmp[$value['name']]=0;
        }
      }
      if (isset($tmp[0])) {
        $retmp['其他']=$tmp[0];
      }
      $i=0;
      foreach ($retmp as $key => $value) {
          $cpmap.='"'.$key.'",';
          $cpmapval[$i]['name']=$key;
          $cpmapval[$i]['value']=$value;
          $i++;
        }
        $cpmap='['.rtrim($cpmap,',').']';
      return array('map'=>$cpmap,'val'=>json_encode($cpmapval));
    }

    /**
     * [支付方式统计]
     * @param  [type] $order [订单数据]
     * @return [type]        [description]
     */
    public function payWayCount($order)
    {
        //支付方式
        $Dict=getPayway();
        foreach ($order as $k => $v) {
            if (isset($Dict[$v['pay_way']])) {
                $Dict[$v['pay_way']]['value'][]=$v;
            }else{
                $Dict[0]['value'][]=$v;
            }
        }

        return $Dict;
    }

    /**
     * [菜品排行]
     * @param [type] $st [开始时间]
     * @param [type] $et [结束时间]
     */
    public function SalesPh($st,$et)
    {
    	$st=date('Y-m-d H:i:s',$st);
    	$et=date('Y-m-d H:i:s',$et);
		$w=array('om.addtime'=>array('between',array($st,$et)),'o.state'=>2,'o.shop_id'=>session('admin.shop_id'));
		$order_lists=M()
		->table('hg_order_menu om,hg_menu m,hg_order o')
		->field("sum(om.num) as num,m.name")
		->where('om.menu_id=m.fruits_id and o.order_id=om.order_id')
		->group('om.menu_id')
		->where($w)->order('num asc')
		->select();
		
		foreach ($order_lists as $key => $value) {
			$phmap.='"'.$value['name'].'",';
    		$phmapval.='"'.$value['num'].'",';
		}
		$phmap='['.rtrim($phmap,',').']';
		$phmapval='['.rtrim($phmapval,',').']';
		return array('map'=>$phmap,'val'=>$phmapval,'count'=>count($order_lists)?:0);
    }

    /**
     * [营业额，实收额，台数，人数 页面顶部统计]
     * @param  [Array] $order [订单数据]
     * @return [type]        [description]
     */
    public function countETRD($order)
    {
    	$Tc=count($order);//台数
    	foreach ($order as $key => $value) {
    		$Ec+=$value['price']; //营业额
    		$Rc+=$value['nums'];//人数
    		$ESc+=$value['paid']; //实收额
    	}
    	$Dc=$Ec/$Rc;//客单价
    	return array('Ec'=>$Ec?:0,'ESc'=>$ESc?:0,'Rc'=>$Rc?:0,'Dc'=>$Dc?round($Dc,2):0,'Tc'=>$Tc?:0);
    }

    /**
     * [营业额，实收额，台数，人数]
     * @param  [type] $order      [订单数据]
     * @param  [type] $start_time [开始时间]
     * @param  [type] $end_time   [结束时间]
     * @return [type]             [description]
     */
    public function countETRS($order,$start_time,$end_time)
    {
        // dump($order);
        // exit;
    	$order_arr=array();
		foreach ($order as $k => $v) {
			$order_arr[date('Y-m-d',strtotime($v['order_time']))][]=$v;
		}
		$dateMap=getDayMapSE($start_time,$end_time);

		foreach ($dateMap as $key => $value) {
			if (isset($order_arr[date('Y-m-d',$key)])) {
    			$curOD=$order_arr[date('Y-m-d',$key)];
    			$curT.=count($curOD).',';//台数
    			$tmp_curE=0;$tmp_curS=0;$tmp_curR=0;
    			foreach ($curOD as $k => $v) {
    				$tmp_curE+=$v['price'];
    				$tmp_curS+=$v['paid'];
    				$tmp_curR+=$v['nums'];
    			}
    			$curE.=$tmp_curE.',';//营业额
    			$curS.=$tmp_curS.',';//实收额
    			$curR.=$tmp_curR.',';//人数
    		}else{
    			$curE.='0,';$curT.='0,';$curR.='0,';$curS.='0,';
    		}

    		$Hmap.='"'.date('Y-m-d',$key).'",';
		}
    	$Hmap='['.rtrim($Hmap,',').']';
    	$Emap='['.rtrim($curE,',').']';
    	$Tmap='['.rtrim($curT,',').']';
    	$Rmap='['.rtrim($curR,',').']';
    	$Smap='['.rtrim($curS,',').']';
    	return array('Hmap'=>$Hmap,'Emap'=>$Emap,'Tmap'=>$Tmap,'Rmap'=>$Rmap,'Smap'=>$Smap);
    }

    /**
     * 每小时平均台数统计
     * @param  [type] $order      [订单数据]
     * @param  [type] $start_time [开始时间]
     * @param  [type] $end_time   [结束时间]
     * @return [type]             [description]
     */
    public function hoursVag($orders,$start_time,$end_time)
    {
    	// $diff_day=($end_time-$start_time)/(24*3600);
    	$order_arr=array();
		foreach ($orders as $k => $v) {
			$order_arr[date('H',strtotime($v['order_time']))][]=$v;
		}
    	for ($i=0; $i < 24; $i++) { 
    		if (isset($order_arr[sprintf('%02d',$i)])) {
    			$curOD=$order_arr[sprintf('%02d',$i)];
    			// $curH.=(count($curOD)/$diff_day).',';
    			$curH.=round((count($curOD)/count($order_arr)),2).',';
    		}else{
    			$curH.='0,';
    		}
    		$Hmap.='"'.$i.'时",';
    	}
    	$Hmap='['.rtrim($Hmap,',').']';
    	$Hmapval='['.rtrim($curH,',').']';
    	return array('Hmap'=>$Hmap,'Hmapval'=>$Hmapval);
    }

    public function getGroupDetail()
    {
      $groupID=I('groupID');

      $start_time=I('start_time')?strtotime(I('start_time')):'';
      $end_time=I('end_time')?strtotime(I('end_time')):'';

      $end_time=$end_time?getOneDay($end_time):getOneDay();
      $et=date('Y-m-d H:i:s',$end_time['et']);

      $start_time=$start_time?getOneDay($start_time):getDayRegion($end_time);
      $st=date('Y-m-d H:i:s',$start_time['st']);  

      $w=array('o.order_time'=>array('between',array($st,$et)),'o.state'=>2,'o.shop_id'=>session('admin.shop_id'));
      if (0===intval($groupID)) {
        $group_ids=M('Group')->where(array('shop_id'=>session('admin.shop_id')))->getField('group_id',true);
        $w['om.group_id']=array('notin',$group_ids);
      }else{
        $w['om.group_id']=$groupID;
      }

      
      $lists=M()->table('hg_order o,hg_order_menu om')
      ->field('sum(om.num) as num,om.menu_name,sum(om.price*om.num) as total')
      ->where('o.order_id=om.order_id')->where($w)->group('menu_id')->order('total desc')->select();
      $totalnum=0;$totalprice=0;
      foreach ($lists as $k => $v) {
        $totalnum+=$v['num'];
        $totalprice+=$v['total'];
      }
      array_push($lists, array('num'=>$totalnum,'menu_name'=>'统计','total'=>round($totalprice,2)));
      $this->ajaxReturn($lists);
    }

    /**
     * 获取订单数据
     * @param  [type] $st [开始时间]
     * @param  [type] $et [结束时间]
     * @return [type]     [description]
     */
    public function getOrdersByTime($st,$et)
    {
    	$st=date('Y-m-d H:i:s',$st);
    	$et=date('Y-m-d H:i:s',$et);
		$w=array('order_time'=>array('between',array($st,$et)),'state'=>2,'shop_id'=>session('admin.shop_id'));
		$order_lists=M('Order')->where($w)->select();
		return $order_lists;
    }
}