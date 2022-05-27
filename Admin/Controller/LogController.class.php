<?php
/**
 * 
 * @authors Liujinbi (857053791@QQ.com)
 * @date    2017-06-09 13:25:57
 * @version $Id$
 */
namespace Admin\Controller;
use Think\Controller;
class LogController extends CommonController {
    
   public function index()
   {
   		$shop_id=session('admin.shop_id');

		$start_time=I('start_time')?strtotime(I('start_time')):'';
    	$end_time=I('end_time')?strtotime(I('end_time')):'';
    	$type=I('type')?:0;

    	if($end_time){
    		$end_time=getOneDay($end_time);
    	}else{
    		$end_time=getOneDay();
    	}
    	$end_time=$end_time['et'];

    	if ($start_time) {
    		$start_time=getOneDay($start_time);
    	}else{
    		$start_time=getOneDay();
    	}
    	$start_time=$start_time['st'];

    	
		
		$w=" shop_id={$shop_id} and time between '{$start_time}' and '{$end_time}'";
		if ($type) {
			$w.=" and type=$type";
		}

   		$lists=M('Log')->where($w)->select();
   		foreach ($lists as $key => $value) {
   			if ($value['type']==1) {
   				$lists[$key]['type']='登录日志';
   			}else if ($value['type']==2) {
   				$lists[$key]['type']='打印日志';
   			}else{
   				$lists[$key]['type']='日志';
   			}

   			$lists[$key]['time']=date('Y-m-d H:i:s',$value['time']);
   			$lists[$key]['url']=$value['model'].'/'.$value['controller'].'/'.$value['action'];
   			if ($value['platform']=='desktop') {
   				$lists[$key]['platform']='电脑';
   			}else if ($value['platform']=='mobile') {
   				$lists[$key]['platform']='手机';
   			}else if ($value['platform']=='tablet') {
   				$lists[$key]['platform']='平板';
   			}else{
   				$lists[$key]['platform']='未知';
   			}
   		}
   		$iw=I();
		$iw['start_time']=date('Y-m-d',$start_time);
		$iw['end_time']=date('Y-m-d',$end_time);

		$this->assign('lists',$lists);
		$this->assign('w',$iw);
		$this->display();

   }

   public function ajax_details()
   {
   		$id=I('log_id');
   		$info=M('Log')->where(array('id'=>$id))->find();
   		if ($info['type']==1) {
			$info['type']='登录日志';
		}else if ($info['type']==2) {
			$info['type']='打印日志';
		}else{
			$info['type']='日志';
		}

		$info['time']=date('Y-m-d H:i:s',$info['time']);
		$info['url']=$info['module'].'/'.$info['controller'].'/'.$info['action'];
		if ($info['platform']=='desktop') {
			$info['platform']='电脑';
		}else if ($info['platform']=='mobile') {
			$info['platform']='手机';
		}else if ($info['platform']=='tablet') {
			$info['platform']='平板';
		}else{
			$info['platform']='未知';
		}
		$info['content']=$info['content']?:$info['params'];
		$this->ajaxReturn($info);
   }
}