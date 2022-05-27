<?php
namespace Admin\Controller;
use Think\Controller;
class PrinterController extends CommonController{
	public function index(){
		$printer=M('Printer');
		//$info=$printer->field('g.name gname,p.*')->table('hg_group g,hg_printer p')->where('g.group_id=p.group_id  and g.shop_id='.$_SESSION['admin']['shop_id'])->select();
		// $info=$printer->field('g.name gname,p.*')->table('hg_printer p')->join('hg_group g on p.group_id=g.group_id','left')->where('p.shop_id='.$_SESSION['admin']['shop_id'])->select();
		$info = $printer->where('shop_id='.$_SESSION['admin']['shop_id'])->select();
		foreach($info as $k => $val){
			if($val['group_id']){
				$group_ids = explode('|',$val['group_id']);
				foreach($group_ids as $v){
					$group_name[] = M('Group')->where('group_id='.$v)->getField('name');
				}
				$info[$k]['gname'] = implode(',',$group_name);
				unset($group_name);
			}else{
				continue;
			}
		}
		$this->assign('printer',$info);
		$this->display();
	}
	
	// 删除打印机
	public function del(){
		$printer=M('Printer');
		//$data['id']=I('id');
		//$data['shop_id']=$_SESSION['admin']['shop_id'];
		if($printer->delete(I('id'))){
			$this->redirect('Printer/index');
		}else{
			$this->error('删除失败');
		}
	}
	
	// 添加打印机
	public function add(){
		$printer=M('Printer');
		$group=M('Group');
		$data['shop_id']=$_SESSION['admin']['shop_id'];
		if(IS_POST){
			$data['name']=I('name');
			$data['partner']=I('partner');
			$data['apikey']=I('apikey');
			$data['machine_code']=I('machine_code');
			$data['msign']=$_POST['msign'];
			$data['group_id']=implode('|',I('groups'));
			$data['master']=I('is_print');
			
			if(I('is_print')==''){
				$data['master']=0;
			}else{
				$data['master']=1;
			}
			if($printer->add($data)){
				$this->redirect('Printer/index');
			}else{
				$this->error('添加失败');
			}
		}
		$this->assign('group',$group->where($data)->select());
		$this->display();
	}
	
	// 编辑打印机
	public function edit(){
		$printer=M('Printer');
		if(IS_POST){
			$data['id']=I('printer_id');
			$data['name']=I('name');
			$data['partner']=I('partner');
			$data['apikey']=I('apikey');
			$data['machine_code']=I('machine_code');
			$data['msign']=I('msign');
			
			$data['group_id'] = implode('|',I('groups'));
			
			if(I('is_print') == ''){
				$data['master']=0;
			}else{
				$data['master']=I('is_print');
			}
			if($printer->save($data)!==false){
				$this->redirect('Printer/index');
			}else{
				$this->error('修改失败');
			}
		}
		$map['id']=I('id');
		$data['shop_id']=$_SESSION['admin']['shop_id'];
		$this->assign('printer',$printer->where($map)->find());
		$this->assign('group',M('Group')->where($data)->select());
		$this->assign('select_group',explode('|',M('Printer')->where('id='.$map['id'])->getField('group_id')));
		$this->display();
	}
}
?>