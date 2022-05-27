<?php
namespace Admin\Controller;
use Think\Controller;
class GroupController extends CommonController{
	// 所有分类
	public function index(){
		$group=M('Group');
		$data['shop_id']=$_SESSION['admin']['shop_id'];
		$this->assign('group',$group->where($data)->order('sort asc')->select());
		$this->display();
	}
	
	// 排序
	public function sorts(){
		$group=M('Group');
		dump($_POST);die;
		unset($_POST['group_id']);
		foreach($_POST as $id=>$sort){
			$group->where('group_id='.$id)->setField('sort',$sort);
		}
		$this->redirect('Group/index');
	}
	
	// 删除分类
	public function del(){
		$group=M('Group');
		//$data['group_id']=I('id');
		//$data['shop_id']=$_SESSION['admin']['shop_id'];
		if($group->delete(I('id'))){
			$this->redirect('group/index');
		}else{
			$this->error('删除失败');
		}
	}
	
	// 修改分类名称
	public function edit(){
		$group=M('Group');
		//$map['shop_id']=$data['shop_id']=$_SESSION['admin']['shop_id'];
		$map['group_id']=I('id');
		if(IS_POST){
			$data['name']=$_POST['name'];
			$data['group_id']=$_POST['group_id'];
			if($group->save($data)!==false){
				$this->redirect('Group/index');
			}else{
				$this->error('修改失败');
			// dump($group->getLastsql());
			}
		}
		$this->assign('group',$group->where($map)->find());
		$this->display();
	}
	
	// 添加分类
	public function add(){
		$group=M('Group');
		if(IS_POST){
			$data['shop_id']=$_SESSION['admin']['shop_id'];
			$data['sort']=I('sort');
			$data['name']=$_POST['name'];
			if($group->add($data)){
				$this->redirect('Group/index');
			}else{
				$this->error('添加失败');
			}
		}
		$this->display();
	}
	
	// 拖动排序
	public function sortss(){
		$group=M('Group'); 
		$sort=1;
		foreach($_POST['group_id'] as $val){
			$group->where('group_id='.$val)->setField('sort',$sort);
			$sort++;
		}
		$this->redirect('Group/index');
	}
}
?>