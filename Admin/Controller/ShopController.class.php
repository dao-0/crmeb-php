<?php
namespace Admin\Controller;
use Think\Controller;
class ShopController extends CommonController{
	public function index(){
		$shop=M('Shop');
		$map['shop_id']=$data['shop_id']=$_SESSION['admin']['shop_id'];
		
		if(IS_POST){
			$data['name']=$_POST['name'];
			$data['username']=$_POST['username'];
			$data['phone']=$_POST['phone'];
			$data['address']=$_POST['address'];
			$data['intro']=$_POST['intro'];
			$data['remark']=$_POST['remark'];
			$data['qr']=$_POST['qr'];
			$data['round']=$_POST['checkbox']==1?1:0;
			
			if($_FILES['logo']['name']!=''){
					$upload = new \Think\Upload();
					$upload->maxSize   =     3145728 ;
					$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
					$upload->rootPath='./Uploads/';
					$upload->savePath='./';
					$info=$upload->uploadOne($_FILES['logo']);
					$data['logo']=$info['savepath'].$info['savename'];
				}else{
					$res=$shop->where($map)->find();
					$data['logo']=$res['logo'];
				}
			$shop->save($data);
		}
		//生成店铺二维码
		$content = "http://".$_SERVER['SERVER_NAME']."/index.php/Weixin/Order/index.html?shop_id=".$_SESSION['admin']['shop_id'];
		$this->assign('img',urlencode($content));
		$this->assign('shop',$shop->where($data)->find());
		$this->display();
	}
}
?>