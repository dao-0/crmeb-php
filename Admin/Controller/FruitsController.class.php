<?php
namespace Admin\Controller;
use Think\Controller;
class FruitsController extends CommonController{
	// 显示所有果品
	public function index(){
		$fruits=M('Menu');
		//$info=$fruits->field('g.group_id gname,m.*')->table('hg_group g,hg_menu m')->where('g.group_id=m.group_id  and m.shop_id='.$_SESSION['admin']['shop_id'])->order('m.sort asc')->select();
		//$this->assign('fruits',$info);
		$this->assign('fruits',$fruits->where(array('shop_id'=>$_SESSION['admin']['shop_id'],'group_id'=>I('group_id')?:1))->order('sort asc')->select());
		$group=M('Group');
		$this->assign('group_id',I('group_id')?:1);
		$this->assign('group',$group->where(array('shop_id'=>$_SESSION['admin']['shop_id']))->order('sort asc')->select());
		$this->display();
	}
	
	// 删除果品
	public function del(){
		$fruits=M('Menu');
		//$data['fruits_id']=I('id');
		//$data['shop_id']=$_SESSION['admin']['shop_id'];
		if($fruits->delete(I('id'))){
			$this->redirect('Fruits/index');
		}else{
			$this->error('删除失败');
		}
	}
	
	// 编辑果品
	public function edit(){
		$fruits=M('Menu');
		$group=M('Group');
		if(IS_POST){
			$map['fruits_id']=$data['fruits_id']=I('fruits_id');
			$data['group_id']=I('groups');
			$data['name']=I('name');
			$data['price']=I('price');
			
			$data['attach'] = implode('|',I('menu'));
			
			if($_FILES['pic']['name']!=''){
				$upload = new \Think\Upload();
				$upload->maxSize   =     3145728 ;
				$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
				$upload->rootPath='./Uploads/';
				$upload->savePath='./';
				$info=$upload->uploadOne($_FILES['pic']);
				$data['pic']=$info['savepath'].$info['savename'];
			}else{
				$res=$fruits->where($map)->find();
				$data['pic']=$res['pic'];
			}
			
			if($fruits->save($data)!==false){
				$this->redirect('Fruits/index',array('group_id' => $data['group_id']));
			}else{
				$this->error('修改失败');
			}
		}
		//$info=$fruits->field('g.name group_name,m.*')->table('hg_group g,hg_menu m')->where('g.group_id=m.group_id  and m.fruits_id='.I('id').' and m.shop_id='.$_SESSION['admin']['shop_id'].'')->find();
		$data['fruits_id']=I('id');
		$map['shop_id']=$_SESSION['admin']['shop_id'];
		$this->assign('menu',M('Menu')->where('shop_id='.$_SESSION['admin']['shop_id'])->select());
		$this->assign('select_menu',explode('|',M('Menu')->where('fruits_id='.I('id'))->getField('attach')));
		$this->assign('fruits',$fruits->where($data)->find());
		$this->assign('group',$group->where($map)->select());
		$this->display();
	}
	
	// 添加果品
	public function add(){
		$fruits=M('Menu');
		$group=M('Group');
		$data['shop_id']=$_SESSION['admin']['shop_id'];
		if(IS_POST){
			$data['group_id']=I('groups');
			$data['name']=I('name');
			$data['price']=I('price');

			if($_FILES['pic']['name']!=''){
				$upload = new \Think\Upload();
				$upload->maxSize   =     3145728 ;
				$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
				$upload->rootPath='./Uploads/';
				$upload->savePath='./';
				$info=$upload->uploadOne($_FILES['pic']);
				$data['pic']=$info['savepath'].$info['savename'];
			}
				
			if($fruits->add($data)){
				$this->redirect('Fruits/index');
			}else{
				$this->error('添加失败');
			}
		}
		$this->assign('group',$group->where($data)->select());
		$this->display();
	}
	
	// 拖动排序
	public function sorts(){
		$fruits=M('Menu');
		$group_id = I('group_id');
		//$sorts=explode(",",I('ids'));
		$sort=1;
		foreach($_POST['fruits_id'] as $val){
			$fruits->where('fruits_id='.$val)->setField('sort',$sort);
			$sort++;
		}
		$this->redirect('Fruits/group_select?group_id='.$group_id);
	}
	
	// 是否出售
	public function sell(){
		$fruits=M('Menu');
		$data['fruits_id']=I('fruits_id');
		if(I('sell')==0){
			$data['sell']=1;
		}else{
			$data['sell']=0;
		}
		$fruits->save($data);
		$this->ajaxReturn(I('sell'));
	}
	
	// 修改库存
	public function update_stock(){
		$fruits=M('Menu');
		$data['fruits_id']=I('fruits_id');
		$data['stock']=I('stock');
		$fruits->save($data);
	}

	// 修改描述
	public function update_remark(){
		$fruits=M('Menu');
		$data['fruits_id']=I('fruits_id');
		$data['remark']=I('remark');
		$fruits->save($data);
	}
	
	// 分类查询
	public function group_select(){
		echo I('group_id');
		$group_id = I('group_id');
		$fruits=M('Menu')->where(array('shop_id'=>$_SESSION['admin']['shop_id'],'group_id'=>I('group_id')))->order('sell desc, sort asc')->select();
		$this->assign(array('fruits'=>$fruits,'group_id'=>$group_id));
		$group=M('Group');
		$this->assign('group',$group->where(array('shop_id'=>$_SESSION['admin']['shop_id']))->order('sort asc')->select());
		$this->display('index');
	}
}
?>