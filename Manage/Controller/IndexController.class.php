<?php
namespace Manage\Controller;
use Think\Controller;
class IndexController extends CommonController {
    public function index(){
		$this->display();
    }

    public function groupStat(){
        $group_id=I('group_id');
        $info=M()->field('m.menu_name,sum(m.num) as menu_num,SUM(m.price*m.num) as menu_price')->table('hg_order_menu m')->join('hg_order o on m.order_id=o.order_id','left')->where('m.group_id='.$group_id.' and m.addtime>\''.date('Y-m-d').' 00:00:00\' and addtime <\''.date('Y-m-d').' 23:59:59\' and o.state=2')->group('m.menu_id')->select();
        echo '<pre>';
        $total_price=0;
        foreach ($info as $item){
            echo $item['menu_name']."\t X".$item['menu_num']."\t".$item['menu_price']."\n";
            $total_price+=$item['menu_price'];
        }
        echo '总营业额：'.$total_price;
    }

}