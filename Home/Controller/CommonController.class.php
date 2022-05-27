<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller{
	public function _initialize(){
		 if(!session('user')){
			$id=I('uid');
			if ($id) {
				$info=M('Admin')->where(array('id'=>$id))->find();
				if ($info) {
					session('user',$info);
				}else{
					$this->redirect('Login/index');
				}
			}else{
				$this->redirect('Login/index');
			}
		 }
	}
}
?>