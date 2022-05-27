<?php
namespace Admin\Controller;
use Think\Controller;
class DiscountController extends CommonController{
	public function index(){
		$this->assign('discount',M('Discount')->where(array('shop_id'=>session('admin.shop_id')))->order('sort asc')->select());
		$this->display();
	}
	
	// 删除分类
	public function del(){
		$discount=M('Discount');
		if($discount->delete(I('id'))){
			$this->redirect('Discount/index');
		}else{
			$this->error('删除失败');
		}
	}
	
	// 修改分类名称
	public function edit(){
		$discount=M('Discount');
		$map['id']=$data['id']=I('id');
		if(IS_POST){
			$data['name']=$_POST['name'];
			$data['discount']=$_POST['discount'];
			if($discount->save($data)!==false){
				$this->redirect('Discount/index');
			}else{
				$this->error('修改失败');
			}
		}
		$this->assign('discount',$discount->where($map)->find());
		$this->display();
	}
	
	// 添加分类
	public function add(){
		$discount=M('Discount');
		if(IS_POST){
			$data['shop_id']=$_SESSION['admin']['shop_id'];
			$data['discount']=I('discount');
			$data['name']=$_POST['name'];
			$data['type']=I('type');
			if($discount->add($data)){
				$this->redirect('Discount/index');
			}else{
				$this->error('添加失败');
			}
		}
		$this->display();
	}
	
	// 拖动排序
	public function sorts(){
		$discount=M('Discount'); 
		$sort=1;
		foreach($_POST['id'] as $val){
			$discount->where('id='.$val)->setField('sort',$sort);
			$sort++;
		}
		$this->redirect('Discount/index');
	}
	
	// 是否启用该折扣
	public function state(){
		$discount=M('Discount');
		$data['id']=I('id');
		if(I('state')==0){
			$data['state']=1;
		}else{
			$data['state']=0;
		}
		$discount->save($data);
	}
}
?>