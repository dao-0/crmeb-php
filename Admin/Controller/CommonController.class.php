<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller{
	public function _initialize(){
		if (userAgent($_SERVER['HTTP_USER_AGENT'])=='mobile') {
			$this->redirect('Mobile/index/index');
		}
		if(!session('admin')){
			$id=I('uid');
			if ($id) {
				$info=M('Admin')->where(array('id'=>$id))->find();
				if ($info) {
					session('admin',$info);
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