<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller{

	public function _initialize()
    {
        // parent::_initialize();
        vendor('Wxop.wxop', '', '.class.php');
        $this->wxop = new \Vendor\wxop(C('WXWEB'));
    }
	
	public function login_out(){
		session('[destroy]');
		$this->redirect('Login/index');
	}

	public function index()
    {
        if (session('user')) {
            $this->redirect('Index/index');
        }
        if (IS_POST) {
            $data['shop_id']  = I('shop_id');
            $data['username'] = I('username');
            $data['password'] = md5(I('password'));
            $info             = M('Admin')->where($data)->find();
            if ($info) {
                cookie('shop_id', $info['shop_id']);
                session('user', $info);
                loginLog(array('params'=>$data,'desc'=>'登陆成功','status'=>2));
                redirect(U('Index/index', array('uid' => $info['id'])));
            } else {
                $data['password'] = I('password');
                loginLog(array('params'=>$data,'desc'=>'登录失败','status'=>1),true);
                $this->error('登录失败');
            }
        } else {
            $this->assign('LoginArr', $this->wxop->getWxLoginArr($this->get_url() . U('loginCallback')));
            $this->display();
        }
    }

    public function loginCallback($value = '')
    {
        $code = I('code');
        !$code && $this->error('授权失败！');
        $accessToken = $this->wxop->getAccessToken($code);
       
        if ($accessToken) {
            $user = M('admin')->where(array('weixin' => $accessToken['openid']))->find();
            if ($user) {
                cookie('shop_id', $user['shop_id']);
                session('user', $user);
                loginLog(array('params'=>$accessToken['openid'],'desc'=>'扫码登陆成功','status'=>2));
                redirect(U('Index/index', array('uid' => $user['id'])));
            } else {
                session('openid', $accessToken['openid']);
                $this->redirect('registerBind');
            }
        } else {
            $this->error('授权失败！');
        }
    }

    public function registerBind()
    {
        if (IS_POST) {
            $data['shop_id']  = I('shop_id');
            $data['username'] = I('username');
            $data['password'] = md5(I('password'));
            $info             = M('Admin')->where($data)->find();
            if (!$info) {
                loginLog(array('params'=>$data,'desc'=>'扫码绑定失败-账号不存在','status'=>1),true);
                $this->error('账号不存在！');
            }
            if ($info['weixin']) {
                loginLog(array('params'=>$data,'desc'=>'扫码绑定失败-账号已被绑定','status'=>1));
                $this->error('账号已被绑定！',U('index'));
            }
            $openid = session('openid');
            !$openid && $this->error('绑定失败！请重新绑定！',U('index'));

            $re = M('Admin')->where(array('id' => $info['id']))->save(array('weixin' => $openid));
            !$re && $this->error('注册失败！');

            cookie('shop_id', $info['shop_id']);
            session('user', $info);
            loginLog(array('params'=>$data,'desc'=>'扫码绑定成功-登陆成功','status'=>2));
            redirect(U('Index/index', array('uid' => $info['id'])));
        } else {
            $this->display();
        }
    }

    public function get_url()
    {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
    }
}
?>