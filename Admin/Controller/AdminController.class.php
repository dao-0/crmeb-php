<?php
namespace Admin\Controller;
use Think\Controller;
class AdminController extends CommonController{
	public function index(){
		$data['shop_id']=$_SESSION['admin']['shop_id'];
		if($_SESSION['admin']['level']==2){
			$data['level']=array('lt',2);
		}else if($_SESSION['admin']['level']==1){
			$data['level']=array('lt',1);
		}
		$this->assign('admins',M('Admin')->where($data)->select());
		//dump(M('Admin')->getLastsql());
		$this->display();
	}
	
	//删除员工
	public function del(){
		$admin=M('Admin');
		//$data['id']=I('id');
		//$data['shop_id']=$_SESSION['admin']['shop_id'];
		if($admin->delete(I('id'))){
			$this->redirect('Admin/index');
		}else{
			$this->error('删除失败');
		}
	}
	
	// 添加员工
	public function add(){
		$admin=M('Admin');
		if(IS_POST){
			$map['shop_id']=$_SESSION['admin']['shop_id'];
			$map['username']=$data['username']=I('username');
			if($admin->where($map)->find()){
				$this->error('该工号已经存在');
			}else{
				$data['password']=md5(I('password'));
				$data['nickname']=I('nickname');
				$data['shop_id']=$_SESSION['admin']['shop_id'];
				$data['level']=I('level');
				if($admin->add($data)){
					$this->redirect('Admin/index');
				}else{
					$this->error('添加失败');
				}
			}
		}
		$this->display();
	}
	
	// 编辑员工
	public function edit(){
		$admin=M('Admin');
		if(IS_POST){
			$map['id']=I('id');
			//$data['username']=I('username');
			$data['nickname']=I('nickname');
			if(I('password')==''||I('password')==null){
				$data['password']=$admin->where($map)->getField('password');
			}else{
				$data['password']=md5(I('password'));
			}
			// $data['level']=I('level');
			if($admin->data($data)->where($map)->save()!==false){
				$this->redirect('Admin/index');
			}else{
				$this->error('修改失败');
			}
		}
		//$data['shop_id']=$_SESSION['admin']['shop_id'];
		$data['id']=I('id');
		$this->assign('admins',$admin->where($data)->find());
		$this->display();
	}
	
	// 编辑超级管理员
	public function editAdmin(){
		$admin=M('Admin');
		if(IS_POST){
			$map['id']=I('id');
			//$data['username']=I('username');
			$data['nickname']=I('nickname');
			$data['password']=md5(I('password'));
			if($admin->data($data)->where($map)->save()){
				$this->redirect('Admin/editAdmin');
			}else{
				$this->error('修改失败');
			}
		}
		$data['id']=$_SESSION['admin']['id'];
		$this->assign('admins',$admin->where($data)->find());
		$this->display('edit');
	}

	public function reBindWx()
	{
		$uid=I('uid');
		$re=M('Admin')->where(array('id'=>$uid))->save(array('weixin'=>''));
		$re!==false?$this->success('成功！'):$this->error('失败');
	}
	
	// 修改权限
	public function update(){
		$data['id']=I('id');
		if(IS_POST){
			$data['auth']=json_encode(I('auth'));
			if(M('Admin')->save($data)!==false){
				$this->redirect('Admin/index');
			}else{
				$this->error('修改失败');
			}
		}
		$this->assign('admins',M('Admin')->where('id='.$data['id'])->find());
		$this->display();
	}
}
?>