<?php
namespace Manage\Controller;
use Think\Controller;
class CommonController extends Controller{
	public function _initialize(){
		if(!session('manage')){
			$id=I('uid');
			if ($id) {
				$info=M('Admin')->where(array('id'=>$id))->find();
				if ($info) {
					session('manage',$info);
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