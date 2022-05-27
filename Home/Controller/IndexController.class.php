<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {
    public function index(){
		$shop=M('Shop');
		$data['shop_id']=$_SESSION['user']['shop_id'];
		session('shop',$shop->where($data)->find());
		// A('Index')->get_sn();die;
		$this->redirect('Order/index');
    }
	
	// Î¨Ò»¶©µ¥ºÅ
	function get_sn() {
		echo date('Ymd').substr(implode(NULL,array_map('ord',str_split( substr(uniqid(), 7, 13) , 1))), 0, 8);
	}
}