<?php
namespace Mobile\Controller;
use Think\Controller;
class LoginController extends Controller{
	
	public function login_out(){
		session('[destroy]');
		$this->redirect('Login/index');
	}


	public function index()
    {
        if (session('admin')) {
            $this->redirect('Index/index');
        }
        if (IS_POST) {
            $data['shop_id']  = I('shop_id');
            $data['username'] = I('username');
            $data['password'] = md5(I('password'));
            $info             = M('Admin')->where($data)->find();
            if ($info) {
                cookie('shop_id', $info['shop_id']);
                session('admin', $info);
                loginLog(array('params'=>$data,'desc'=>'登陆成功','status'=>2));
                redirect(U('Index/index', array('uid' => $info['id'])));
            } else {
                $data['password'] = I('password');
                loginLog(array('params'=>$data,'desc'=>'登录失败','status'=>1),true);
                $this->error('登录失败');
            }
        } else {
            $this->display();
        }
    }

}
	
?>