<?php
namespace Admin\Controller;
use Think\Controller;
class TableController extends CommonController{
	public function index(){
		$data['shop_id']=$_SESSION['admin']['shop_id'];
		$this->assign('table',M('Table')->where($data)->order('sort asc,tab_num asc')->select());
		$this->display();
	}
	
	// 删除桌号
	public function del(){
		$table=M('Table');
		//$data['id']=I('id');
		//$data['shop_id']=$_SESSION['admin']['shop_id'];
		if($table->delete(I('id'))){
			$this->redirect('Table/index');
		}else{
			$this->error('删除失败');
		}
	}
	
	// 添加桌号
	public function add(){
		$table=M('Table');
		if(IS_POST){
			$data['tab_num']=I('tab_num');
			$data['remark']=I('remark');
			//$data['sort']=I('sort');
			$data['shop_id']=$_SESSION['admin']['shop_id'];
			if($table->add($data)){
				$this->redirect('Table/index');
			}else{
				$this->error('添加失败');
			}
		}
		$this->display();
	}
	
	// 排序
	public function sorts(){
		$table=M('Table');
		//dump($_POST);
		unset($_POST['id']);
		if(IS_POST){
			foreach($_POST as $id=>$sort){
				$table->where('id='.$id)->setField('sort',$sort);
			}
		}
		$this->redirect('Table/index');
	}
	
	
	// 修改分类名称
	public function edit(){
		$table=M('Table');
		//$map['shop_id']=$data['shop_id']=$_SESSION['admin']['shop_id'];
		if(IS_POST){
			$data['id']=$_POST['id'];
			$data['tab_num']=$_POST['tab_num'];
			$data['remark']=$_POST['remark'];
			if($table->save($data)!==false){
				$this->redirect('Table/index');
			}else{
				$this->error('修改失败');
			}
		}
		$map['id']=I('id');
		$this->assign('tab',$table->where($map)->find());
		$this->display();
	}
	
	// 修改状态
	public function state(){
		$table=M('Table');
		$data['shop_id']=$_SESSION['admin']['shop_id'];
		$data['id']=I('id');
		$data['state']=I('state');
		$table->save($data);
		$this->redirect('Table/index');
	}
	
	// 循环添加桌号
	public function adds(){
		$tab=M('Table');
		if(IS_POST){
			foreach(I('tab_num') as $vo){
				$data['shop_id']=$_SESSION['admin']['shop_id'];
				$data['tab_num']=$vo;
				$tab->add($data);
			}
			$this->redirect('Table/index');
		}
		$this->display();
	}
	
	// 拖动排序
	public function sortss(){
		$tab=M('Table'); 
		//$sorts=explode(",",I('ids'));
		$sort=1;
		foreach($_POST['id'] as $val){
			$tab->where('id='.$val)->setField('sort',$sort);
			$sort++;
		}
		$this->redirect('Table/index');
	}
}
?>