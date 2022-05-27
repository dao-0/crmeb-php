<?php
namespace Manage\Controller;
use Think\Controller;
class ShopController extends CommonController {
    public function index(){
		$info=M()->field('s.*,a.id aid')->table('hg_shop s')->join('hg_admin a on s.shop_id=a.shop_id','left')->where('a.level=2')->select();
		$this->assign('shop',$info);
		$this->display();
    }
	
	//删除
	public function del(){
		if(M('Shop')->delete(I('shop_id')) && M('Admin')->delete(I('aid'))){
			$this->redirect('Shop/index');
		}else{
			$this->error('删除失败');
		}
	}
	
	// 添加
	public function add(){
		if(IS_POST){
			$data['name']=I('name');
			$map['shop_id']=M('Shop')->add($data);//返回店铺id
			if($map['shop_id']){
				$map['username']=I('username');
				$map['nickname']=I('nickname');
				$map['password']=md5(I('password'));
				$map['level']=2;
				$map['auth']='["0"]';
				if(M('Admin')->add($map)){
					$this->redirect('Shop/index');
				}else{
					$this->error('添加失败');
				}
			}
		}
		$this->display();
	}
	
	// 编辑管理员帐号
	public function editAdmin(){
		$admin=M('Admin');
		if(IS_POST){
			$map['id']=I('id');
			$data['username']=I('username');
			$data['nickname']=I('nickname');
			$data['password']=md5(I('password'));
			$data['level']=2;
			if($admin->data($data)->where($map)->save()!==false){
				$this->redirect('Shop/index');
			}else{
				$this->error('修改失败');
			}
		}
		$this->assign('admins',$admin->where('id='.I('aid'))->find());
		$this->display('edit');
	}
}