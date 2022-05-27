<?php
namespace Manage\Controller;
use Think\Controller;
class LoginController extends Controller{
	public function login_out(){
		session('[destroy]');
		$this->redirect('Login/index');
	}


	public function index()
    {
        if (session('manage')) {
            $this->redirect('Index/index');
        }
        if (IS_POST) {
            $data['shop_id']  = I('shop_id');
            $data['username'] = I('username');
            $data['password'] = md5(I('password'));
            $info             = M('Admin')->where($data)->find();
            if ($info) {
                session('manage', $info);
                redirect(U('Index/index', array('uid' => $info['id'])));
            } else {
                $data['password']=I('password');
                $this->error('登录失败');
            }
        } else {
            $this->display();
        }
    }
}
	
?>