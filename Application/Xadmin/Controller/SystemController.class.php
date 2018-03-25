<?php
namespace Xadmin\Controller;
use Think\Controller;
// 系统控制器
class SystemController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
    }

    /**
     * [setting 系统设置]
     * @author TF <[2281551151@qq.com]>
     */
    public function setting() {
    	if ( IS_POST ) {
    		$data   = array();
    		$post   = I('post.');
    		$config = M('config');
    		foreach ($post as $key => $value) {
    			$config->where(array('config_sign'=>$key))->data(array('config_value'=>$value))->save();
    		}
    		$this->success('保存成功！');
    	} else {
	    	$config 	= M('config');
	    	$configList = $config->where(array('status'=>1))->order('sort,id')->select();

	    	$this->assign('configList', $configList);
	    	$this->display('setting');
    	}
    }

    //分成配置
    public function agentRebateConfig(){
        if ( IS_POST ) {
            $data   = array();
            $post   = I('post.');
            // dump($post);die;
            $config = M('agent_rebate_config');
            foreach ($post as $key => $value) {
                $data_v = array();
                foreach ($value as $k2 => $v2) {
                    $data_v[trim($k2,"'")]=$v2;

                }
                // dump($data_v);die;
                $config->where(array('id'=>$key))->setField($data_v);
            }
            $this->success('保存成功！');
        } else {
            $config     = M('agent_rebate_config');
            $configList = $config->where('1=1')->field(true)->order('id ASC')->select();
            // dump($configList);DIE;
            $this->assign('configList', $configList);
            $this->display('agentRebateConfig');
        }
    }

    /*
     * 简介
     */
    public function introduction(){
            //当前用户代理agent_id
            $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB', 'WeiXinInfo');
            if(IS_POST){
                $FeedbaceMail   =   I('post.FeedbaceMail','');
                $where          =   array('1=1');
                $WeiXinInfo    = $sqlsrv_model->table('WeiXinInfo')->where($where)->find();
                if(!empty($WeiXinInfo)){
                    $res    =   $sqlsrv_model->table('WeiXinInfo')->where($where)->setField(array('FeedbaceMail'=>$FeedbaceMail));
                    $this->success('保存成功！');
                }  else {
                    $this->error('保存失败！');
                }
            }  else {
                $where          =   array('1=1');
                $FeedbaceMail    = $sqlsrv_model->table('WeiXinInfo')->where($where)->getField('FeedbaceMail');
    	    	$this->assign('FeedbaceMail', rtrim($FeedbaceMail));
    //                dump($FeedbaceMail);die;
    	    	$this->display();
            }
    }

    /*
     * 公告
     */
    public function notice(){
            //当前用户代理agent_id
            $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB', 'WeiXinInfo');
            if(IS_POST){
                $GameWeixin   =   I('post.GameWeixin','');
                $where          =   array('1=1');
                $WeiXinInfo    = $sqlsrv_model->table('WeiXinInfo')->where($where)->find();
                if(!empty($WeiXinInfo)){
                    $res    =   $sqlsrv_model->table('WeiXinInfo')->where($where)->setField(array('GameWeixin'=>$GameWeixin));
                    $this->success('保存成功！');
                }  else {
                    $this->error('保存失败！');
                }
            }  else {
                $where          =   array('1=1');
                $GameWeixin    = $sqlsrv_model->table('WeiXinInfo')->where($where)->getField('GameWeixin');
        	    	$this->assign('GameWeixin', rtrim($GameWeixin));
        //                dump($FeedbaceMail);die;
        	    	$this->display();
            }
    }


    /*
     * 分享配置
     */
    public function share(){
            //当前用户代理agent_id
            $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB', 'WeiXinInfo');
            if(IS_POST){
                $FangkaWeixin   =   I('post.FangkaWeixin','');
                $AdviceWeixin   =   I('post.AdviceWeixin','');
                $ProxyWeixin   =   I('post.ProxyWeixin','');
                $where          =   array('1=1');
                $WeiXinInfo    = $sqlsrv_model->table('WeiXinInfo')->where($where)->find();
                if(!empty($WeiXinInfo)){
                    $data   =   array();
                    $data['FangkaWeixin']   =   $FangkaWeixin;
                    $data['AdviceWeixin']   =   $AdviceWeixin;
                    $data['ProxyWeixin']    =   $ProxyWeixin;
                    $res    =   $sqlsrv_model->table('WeiXinInfo')->where($where)->setField($data);
                    $this->success('保存成功！');
                }  else {
                    $this->error('保存失败！');
                }
            }  else {
                $where          =   array('1=1');
                $WeiXinInfo    = $sqlsrv_model->table('WeiXinInfo')->where($where)->field('FangkaWeixin,AdviceWeixin,ProxyWeixin')->find();
                if(!empty($WeiXinInfo)){
                    foreach ($WeiXinInfo as $key => $value) {
                        $WeiXinInfo[$key]   = rtrim($value);
                    }
                }
//                dump($WeiXinInfo);die;
	    	$this->assign('WeiXinInfo', $WeiXinInfo);
//                dump($FeedbaceMail);die;
	    	$this->display();
            }
    }
    
    
    /*
     * 分享活动配置
     */
    public function shareActivity(){
        if ( IS_POST ) {
            $data   = array();
                $ShareAppReward     =   I('post.ShareAppReward',0,'intval');
                $ShareRewardCnt     =   I('post.ShareRewardCnt',0,'intval');
                $ShareRewardInsure     =   I('post.ShareRewardInsure',0,'intval');
                $ShareRewardScore     =   I('post.ShareRewardScore',0,'intval');

                $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB', 'SystemStatusInfo');

                // ShareAppReward    //用户微信分享游戏赠送金币功能    分享赠送    键值：1-开启，0-关闭
                // ShareRewardCnt   //用户微信分享游戏赠送金币每天次数  分享赠送    键值：每天赠送次数
                // ShareRewardInsure//用户微信分享游戏赠送房卡数量  分享赠送    键值：每次赠送房卡数量
                // ShareRewardScore //用户微信分享游戏赠送金币数量    分享赠送    键值：每次赠送金币数量

                $where['StatusName']    =   array('in',array('ShareAppReward','ShareRewardCnt','ShareRewardInsure','ShareRewardScore'));
                $SystemStatusInfo    = $sqlsrv_model->table('SystemStatusInfo')->where($where)->field(true)->select();
                if (!empty($SystemStatusInfo)) {
                    if (count($SystemStatusInfo) !=4) {
                        $this->error('保存失败！');
                    }

                    foreach ($SystemStatusInfo as $key => $value) {
                        // dump(array('a'=>$value['statusname'],'b'=>$$value['statusname']));die;
                        $sqlsrv_model->table('SystemStatusInfo')->where(array('StatusName'=>$value['statusname']))->setField(array('StatusValue'=>$$value['statusname']));
                    }
                    $this->success('保存成功！');
                }else{
                    $this->error('保存失败！');
                }
        } else {
            $where  =   array();
            $where['StatusName']    =   array('in',array('ShareAppReward','ShareRewardCnt','ShareRewardInsure','ShareRewardScore'));
            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB', 'SystemStatusInfo');
            $SystemStatusInfo    = $sqlsrv_model->table('SystemStatusInfo')->where($where)->field(true)->select();

            $share_activity_config =   array();
            if (!empty($SystemStatusInfo)) {
                # code...
                foreach ($SystemStatusInfo as $key => $value) {
                    # code...
                    $share_activity_config[$value['statusname']] = $value['statusvalue'];
                }
            }
            // dump(array('SystemStatusInfo'=>$SystemStatusInfo,'share_activity_config'=>$share_activity_config));die;
            $this->assign('info', $share_activity_config);
            $this->display();
        }
    }
    public function ccgc(){
            $connectiont = array(
                'db_type' => 'sqlsrv',
                'db_host' => $this->sqlsrv_config['DB_HOST'],
                'db_user' => $this->sqlsrv_config['DB_USER'],
                'db_pwd' => $this->sqlsrv_config['DB_PWD'],
                'db_port' => $this->sqlsrv_config['DB_PORT'],
                'db_name' => 'THPlatformDB',
                'db_charset' => 'utf8',
            );
            
//            dump($connectiont);die;
            $server_name    =   $connectiont['db_host'];
            $connectionInfo =   array(
                'UID'   =>  $connectiont['db_user'],
                'PWD'   =>  $connectiont['db_pwd'],
                'Database'  =>  $connectiont['db_name'],
            );
            
            $conn   =   sqlsrv_connect($server_name,$connectionInfo)  or die( print_r( sqlsrv_errors(), true));
            $params = array('hah');
            $tsql_callSP =  "{call GSP_GP_InsertSystemMessage (?)}";
//            var_dump($connectionInfo, $tsql_callSP, $params);
            $stmt = sqlsrv_query($connectionInfo, $tsql_callSP, $params);
//            var_dump($stmt);die;
            $data = array();
            $data2 = array();
            $result = '';
            if ($stmt === false){
//                echo "Error in executing statement 3.\n";
                die( print_r( sqlsrv_errors(), true));

                $next_result = sqlsrv_next_result($stmt);
                if($next_result){
                    while($row2 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
                        $data2[] =$row2;
                    }
                    foreach($data2 as $key=> $value){
                        $result .= $value['']."\n";
                    }
                    $result = rtrim($result);
                }
            }else{
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $data[] = $row;
                }
                foreach($data as $key=> $value){
                    $result .= $value['']."\n";
                }
                $result = trim($result);
                $result = $this->gbktoutf8($result);
            }
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
            //echo $result;
            dump($result);die;
    }

        //系统消息列表
    public function messageList(){
        
            //当前用户代理agent_id
            $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB','SystemMessage');
            $where    = array('1=1');
            $count    = $sqlsrv_model->table('SystemMessage')->where($where)->count();
            $page     = new \Think\Page($count, 10);

            if ($this->iswap()) {
                    $page->rollPage	= 5;
            }

            $show     = $page->show();

            $MessageList = $sqlsrv_model->table('SystemMessage')->where($where)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('ID DESC')->select();
    //        dump($MessageList);die;
            $this->assign('list', $MessageList);
            $this->assign('show', $show);
            $this->display();
    }

    //新增发布
    public function messageAdd(){
        if(IS_POST){
            $content    =   I('post.content','','trim');
            $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB','SystemMessage');
            $data   =   array(
                'StartTime'=> date('Y-m-d H:i:s'),
                'ConcludeTime'=> date('Y-m-d H:i:s'),
                'CreateDate'=> date('Y-m-d H:i:s'),
                'MessageString'=>$content,
                'MessageType'=>1,
                'TimeRate'=>1
            );
            $res    =   $sqlsrv_model->table('SystemMessage')->add($data);
//            dump($res);die;
            if($res){
                // $sqlsrv_model->table('SystemMessage')->where(array('ID'=>$res))->setField(array('MessageID'=>$res));
                $this->success('新增成功',U('messageList'));
            }else{
                $this->error('新增失败');
            }
           exit;
        }
        $this->display();
    }
    //编辑系统消息
      public function messageEdit(){
             $id=I('get.id');
             if(IS_POST){
                    $data=array(
                         'title'=>I('post.title'),
                         'content'=>I('post.gnosis'),
                         'description'=>I("post.introduction"),
                         'photo'=>I('post.photo'),
                         'update_time'=>NOW_TIME,
                         'status'=>I('post.status'),
                        );
                    // M('article')->data($data)->save();
                    // var_dump($id);
                    //  var_dump($data);
                    //  exit;
                    if(M('article')->where(array('id'=>$id))->data($data)->save()){
                      $this->success('编辑成功',U('messagelist'));
                    }else{
                        $this->error('编辑失败');
                    }
                   exit;
                }else{
                  $info=M('article')->where(array('id'=>$id))->find();
                  // dump($info);
                  // exit;
                  $this->assign('info',$info);
                }
               $this->display();
      }
    //删除系统消息
     public function messageDel(){
        $id=I('get.id');
        $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB','SystemMessage');
        $res = $sqlsrv_model->table('SystemMessage')->where(array('ID'=>$id))->delete();

        if($res){
                // $sqlsrv_model2   =   $this->sqlsrv_model('PlatformDB','UserMail');
                // $res2 = $sqlsrv_model->table('UserMail')->where(array('MailID'=>$id))->delete();
                $this->success('删除成功',U('messagelist'));
        }else{
            $this->error('删除失败');
        }
     }


     //金币兑换
     public function goldExchange(){
        $option =   I('option','list','trim');
        switch ($option) {
            case 'add':
                # code...
                $this->goldExchangeAdd();
                break;
            case 'edit':
                # code...
                $this->goldExchangeEdit();
                break;
            case 'del':
                # code...
                $this->goldExchangeDel();
                break;
            case 'list':
                # code...
                $this->goldExchangeList();
                break;
            default:
                # code...
                break;
        }
        die;
     }

     public function goldExchangeAdd(){
            if(IS_POST){

                $content    =   I('post.content','','trim');
                $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','ExchangeScoreCfg');
                $data   =   array(
                    'Insure'=> I('post.Insure'),
                    'Score'=>I('post.Score'),
                    'ExScore'=>I('post.ExScore')
                );
                $res    =   $sqlsrv_model->table('ExchangeScoreCfg')->add($data);
    //            dump($res);die;
                if($res){
                    $this->success('保存成功',U('goldExchange'));
                }else{
                    $this->error('保存失败');
                }
                exit;
            }else{
                $this->display('goldExchangeAdd');
            }
     }

     public function goldExchangeEdit(){
            if(IS_POST){
                $id=I('post.id',0,'intval');
                $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','ExchangeScoreCfg');
                $data   =   array(
                    'Insure'=> I('post.Insure'),
                    'Score'=>I('post.Score'),
                    'ExScore'=>I('post.ExScore')
                );
                $res    =   $sqlsrv_model->table('ExchangeScoreCfg')->where(array('ID'=>$id))->setField($data);
                if($res){
                    $this->success('保存成功',U('goldExchange'));
                }else{
                    $this->error('保存失败');
                }
                exit;
            }else{
                $id=I('get.id',0,'intval');
                $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','ExchangeScoreCfg');
                $info=$sqlsrv_model->table('ExchangeScoreCfg')->where(array('ID'=>$id))->find();
              // dump($info);
              // exit;
                $this->assign('info',$info);
                $this->display('goldExchangeEdit');
            }
     }

     public function goldExchangeDel(){
            $id=I('get.id',0,'intval');
            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','ExchangeScoreCfg');
            $res    =   $sqlsrv_model->table('ExchangeScoreCfg')->where(array('ID'=>$id))->delete();
            if($res){
                  $this->success('删除成功',U('goldExchange'));
            }else{
                $this->error('删除失败');
            }
     }

     public function goldExchangeList(){
            //当前用户代理agent_id
            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','ExchangeScoreCfg');
            $where    = array('1=1');
            $count    = $sqlsrv_model->table('ExchangeScoreCfg')->where($where)->count();
            $page     = new \Think\Page($count, 10);

            if ($this->iswap()) {
                    $page->rollPage = 5;
            }

            $show     = $page->show();

            $MessageList = $sqlsrv_model->table('ExchangeScoreCfg')->where($where)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('ID ASC')->select();
           // dump($MessageList);die;
            $this->assign('list', $MessageList);
            $this->assign('show', $show);
            $this->display('goldExchangeList');
     }



     //任务活动
     public function taskActivity(){
        $option =   I('option','list','trim');
        switch ($option) {
            case 'add':
                # code...
                $this->taskActivityAdd();
                break;
            case 'edit':
                # code...
                $this->taskActivityEdit();
                break;
            case 'del':
                # code...
                $this->taskActivityDel();
                break;
            case 'list':
                # code...
                $this->taskActivityList();
                break;
            default:
                # code...
                break;
        }
        die;
     }

     public function taskActivityAdd(){
            if(IS_POST){
                // dump(I('post.'));die;
                $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB','TaskListConfig');
                $data   =   array(
                    'Type'=> I('post.Type'),
                    'Finished'=>I('post.Finished'),
                    'ScoreReward'=>I('post.ScoreReward')
                );
                $res    =   $sqlsrv_model->table('TaskListConfig')->add($data);
    //            dump($res);die;
                if($res){
                    $this->success('保存成功',U('taskActivity'));
                }else{
                    $this->error('保存失败');
                }
                exit;
            }else{
                $this->display('taskActivityAdd');
            }
     }

     public function taskActivityEdit(){
            if(IS_POST){
                $id=I('post.id',0,'intval');
                $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB','TaskListConfig');
                $data   =   array(
                    'Type'=> I('post.Type'),
                    'Finished'=>I('post.Finished'),
                    'ScoreReward'=>I('post.ScoreReward')
                );
                // dump($id);die;
                $res    =   $sqlsrv_model->table('TaskListConfig')->where(array('TaskID'=>$id))->setField($data);
                if($res){
                    $this->success('保存成功',U('taskActivity'));
                }else{
                    $this->error('保存失败');
                }
                exit;
            }else{
                $id=I('get.id',0,'intval');
                $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB','TaskListConfig');
                $info=$sqlsrv_model->table('TaskListConfig')->where(array('TaskID'=>$id))->find();
              // dump($info);
              // exit;
                $this->assign('info',$info);
                $this->display('taskActivityEdit');
            }
     }

     public function taskActivityDel(){
            $id=I('get.id',0,'intval');
            $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB','TaskListConfig');
            $res    =   $sqlsrv_model->table('TaskListConfig')->where(array('TaskID'=>$id))->delete();
            if($res){
                  $this->success('删除成功',U('taskActivity'));
            }else{
                $this->error('删除失败');
            }
     }

     public function taskActivityList(){
            //当前用户代理agent_id
            $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB','TaskListConfig');
            $where    = array('1=1');
            $count    = $sqlsrv_model->table('TaskListConfig')->where($where)->count();
            $page     = new \Think\Page($count, 10);

            if ($this->iswap()) {
                    $page->rollPage = 5;
            }

            $show     = $page->show();

            $MessageList = $sqlsrv_model->table('TaskListConfig')->where($where)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('TaskID ASC')->select();
            $taskType   =   array(
                '0' =>  '普通场',
                '1' =>  '高级场',
                '2' =>  '冲击场'
            );
            if (!empty($MessageList)) {
                # code...
                foreach ($MessageList as $key => $value) {
                    # code...
                    $MessageList[$key]['type_txt']  =   $taskType[$value['type']];
                }
            }

           // dump($MessageList);die;
            $this->assign('list', $MessageList);
            $this->assign('show', $show);
            $this->display('taskActivityList');
     }

     //微信公众号图文采集
    public function collectionWeixinNewsMaterial(){
        $agentId    = 8;
        $page       =   I('post.page',0);
        $limit      =   30;
        $offset     =   $page*$limit;
        $param =   array();
        $param['offset']    =   $offset;
        $param['count']     =   $limit;
        $news_arr   =   R('Home/Weixin/GetWxNewsMaterial',$param);
//        dump($news_arr['content']);die;
        if(!empty($news_arr['content'])){
            $media_ids  =   array();
            foreach ($news_arr['content'] as $key => $value) {
                $media_ids[]    =   $value['media_id'];
            }
            if(!empty($media_ids)){
                
                $where  =   array();
                $where['uid']       =   $agentId;
                $where['media_id']  =   array('in',$media_ids);
                $delete_list    =   M('public_material_news')->where($where)->field('id,title')->select();
                $material_news_ids  =   array();
                foreach ($delete_list as $key => $value) {
                    $material_news_ids[]    =   $value['id'];
                }
                
                //删除素材
                if(!empty($material_news_ids)){
                    M('public_material_news')->where(array('id'=>array('in',$material_news_ids)))->delete();
                    M('public_material_news_detail')->where(array('material'=>array('in',$material_news_ids)))->delete();
                }
            }
            
            //重新添加
            $news_list  =   $news_arr['content'];
            $news_list  =   array_reverse($news_list);
            foreach ($news_list as $key => $value) {
                $material_news_data =   array();
                $material_news_data['uid']          =   $agentId;
                $material_news_data['title']        =   $value['items'][0]['title'];
                $material_news_data['create_time']  =   $value['create_time'];
                $material_news_data['update_time']  =   $value['update_time'];
                $material_news_data['media_id']     =   $value['media_id'];
                $material_news_data['cover_image']  =   $value['items'][0]['thumb_url'];
                $news_id    =   M('public_material_news')->add($material_news_data);
                if($news_id){
                    $material_news_detail_data  =   array();
                    foreach ($value['items'] as $k2 => $items) {
                        $material_news_detail_data[$k2]['material'] =   $news_id;
                        $material_news_detail_data[$k2]['title']    =   $items['title'];
                        $material_news_detail_data[$k2]['pic']      =   $items['thumb_url'];
                        $material_news_detail_data[$k2]['url']      =   $items['url'];
                        $material_news_detail_data[$k2]['desc']     =   $items['digest'];
                    }
                    M('public_material_news_detail')->addAll($material_news_detail_data);
                }
            }
        }
        die(json_encode(array('code'=>1,'message'=>'调用成功','result'=>$news_list)));
    }
    
    //微信公众号图片素材采集
    public function collectionWeixinImageMaterial(){
        $agentId    = 8;
//            $agentId=8;
        $where  =   array();
        $where['uid']       =   $agentId;
        $old_material_image_list    =   M('public_material_image')->where($where)->field('id,media_id')->select();
        $old_media_ids  =   array();
        if(!empty($old_material_image_list)){
            foreach ($old_material_image_list as $key => $value) {
                $old_media_ids[]    =   $value['media_id'];
            }
        }
            
        $page       =   I('post.page',0);
        $limit      =   100;
        $offset     =   $page*$limit;
        $param =   array();
        $param['offset']    =   $offset;
        $param['count']     =   $limit;
        $news_arr   =   R('Home/Weixin/GetWxImageMaterial',$param);
//        dump($news_arr['content']);die;
        $news_list  =   array();
        $news_list  =   $news_arr['content'];
        $news_list  =   array_reverse($news_list);
        if(!empty($news_list)){
            $new_media_ids  =   array();
            foreach ($news_list as $key => $value) {
                $new_media_ids[]    =   $value['media_id'];
            }
            
            $media_ids  = array_diff($new_media_ids, $old_media_ids);
//                dump($media_ids);die;
            if(!empty($media_ids)){
            
//                dump($news_list);die;
                $material_image_data  =   array();
                foreach ($news_list as $key => $value) {
                    if(in_array($value['media_id'], $media_ids)){
                            $material_image_data[$key]['uid']         =   $agentId;
                            $material_image_data[$key]['name']        =   $value['name'];
                            $material_image_data[$key]['media_id']    =   $value['media_id'];
                            $material_image_data[$key]['update_time'] =   $value['update_time'];
                            $material_image_data[$key]['cover_image'] =   $value['cover_image'];
                    }
                }
//                dump($material_image_data);die;
                if(!empty($material_image_data)){
                    M('public_material_image')->addAll($material_image_data);
                }
            }
        }
        die(json_encode(array('code'=>1,'message'=>'调用成功','result'=>$news_list)));
    }
    
    //选择图文素材
    public function getWxNewsMaterial(){
        $agentId    =   8;
        $where  =   array();
        $where['uid']   = $agentId;
        $material_news_list =   M('public_material_news')->where($where)->field("id,title,cover_image,create_time,update_time,media_id")->order('id DESC')->select();
        if(!empty($material_news_list)){
            foreach ($material_news_list as $key => $value) {
                $material_news_list[$key]['create_time']  = date('Y年m月d日',$value['create_time']);
                $material_news_list[$key]['update_time']  = date('Y年m月d日',$value['update_time']);
            }
        }
        die(json_encode(array('code'=>1,'message'=>'调用成功','result'=>$material_news_list)));
    }
    
    //选择图片素材
    public function getWxImageMaterial(){
        $agentId    =   8;
        $where  =   array();
        $where['uid']   = $agentId;
        $material_image_list =   M('public_material_image')->where($where)->order('id DESC')->select();
        if(!empty($material_image_list)){
            foreach ($material_image_list as $key => $value) {
                $material_news_list[$key]['update_time']  = date('Y年m月d日',$value['update_time']);
            }
        }
        die(json_encode(array('code'=>1,'message'=>'调用成功','result'=>$material_image_list)));
    }
    
    
    
    //发布菜单
    public function publishMenu(){
        $news_arr   =   R('Home/Weixin/createMenu');
        die(json_encode($news_arr));
    }
    
    
    
    //自动回复
    public function weixinReply(){
            $uid    = 8;
            $where  =   array();
            $where['uid']       =   $uid;
            $list =   M('public_keywords')->where($where)->select();
            
            $index_reply_type   =   array(
                0   =>  '文本',
                1   =>  '图片',
                2   =>  '图文'
            );
            $status_text    =   array(
                0   =>  '关闭',
                1   =>  '启用'
            );
            foreach ($list as $key => $value) {
                $list[$key]['status_text']  =   $status_text[$value['status']];
            }
//            dump($list);die;
            $this->assign('index_reply_type', $index_reply_type);
            $this->assign('list', $list);
            $this->display('weixin_reply');
    }
    
    //添加关键词回复
    public function weixin_reply_select(){
        
            $index_reply_type   =   array(
                0   =>  '文本',
//                1   =>  '图片',
                2   =>  '图文'
            );
            $this->assign('index_reply_type',$index_reply_type);
            $this->display();
    }
    
    
    //微信
    public function weixinReplyAdd() {
            $uid    = 8;
            $type   =   I('get.type',0,'intval');
            $index_reply_type   =   array(
                0   =>  '文本',
                1   =>  '图片',
                2   =>  '图文'
            );
            $reply_type =   $index_reply_type[$type];
            $this->assign('reply_type',$reply_type);
            $this->assign('type',$type);

            switch ($type) {
                case 0: //文本
                    $this->display('weixin_reply_add_text');
                    break;
                case 1: //图片
                    $this->display('weixin_reply_add_image');
                    break;
                case 2: //图文
                    $this->display('weixin_reply_add_news');
                    break;

                default:
                    break;
            }
        
    }
    
    
    //微信
    public function weixinReplyEdit() {
            $uid    = 8;
            
            $id     =   I('get.id',0,'intval');
            $where  =   array();
            $where['id']    =   $id;
            $weixin_reply  =   M('public_keywords')->where($where)->find();
            if(!empty($weixin_reply) && $weixin_reply['uid'] != $uid){
                $this->error('你无权操作当前信息', U('weixinReplyAdd'));
            }
            $index_reply_type   =   array(
                0   =>  '文本',
                1   =>  '图片',
                2   =>  '图文'
            );
            $reply_type =   $index_reply_type[$weixin_reply['reply_type']];
            $this->assign('reply_type',$reply_type);
            $this->assign('type',$weixin_reply['reply_type']);

            switch ($weixin_reply['reply_type']) {
                case 0: //文本
                    if(!empty($weixin_reply['reply_text'])){
                        $weixin_reply['reply_text'] = base64_decode($weixin_reply['reply_text']);
                    }
                    $this->assign('weixin_reply',$weixin_reply);
                    $this->display('weixin_reply_add_text');
                    break;
                case 1: //图片
                    if(!empty($weixin_reply['reply_media_id'])){
                        
                        $material_image  =   M('public_material_image')->where(array('media_id'=>$weixin_reply['reply_media_id']))->find();
                        $weixin_reply['reply_media_title']  =   $material_image['name'];
                    }
                    $this->assign('weixin_reply',$weixin_reply);
                    $this->display('weixin_reply_add_image');
                    break;
                case 2: //图文
                    if(!empty($weixin_reply['reply_media_id'])){
                        
                        $material_news  =   M('public_material_news')->where(array('media_id'=>$weixin_reply['reply_media_id']))->find();
                        $weixin_reply['reply_media_title']  =   $material_news['title'];
                    }
//                    dump($material_news);die;
                    $this->assign('weixin_reply',$weixin_reply);
                    $this->display('weixin_reply_add_news');
                    break;

                default:
                    break;
            }
        
    }
    
    //微信关键词入库
    public function weixinReplySave(){
            if(IS_POST){
                    $PublicKeywordsModel = D('PublicKeywords');
                    $id =   I('post.id',0,'intval');
                    $data   =   array();
                    if($id  ==  0){
                            $data     = $PublicKeywordsModel->create(I('post.'), 1);
                            if ( empty($data) ) {
                                $this->error($PublicKeywordsModel->getError());
                            } else {
                                if(!empty($data['reply_text'])){
                                    $data['reply_text'] = base64_encode($data['reply_text']);
                                }
                                if ( $PublicKeywordsModel->data($data)->add() >= 0 ) {
                                    $this->success('保存成功！');
                                } else {
                                    $this->error('保存失败！');
                                }
                            }
                    }  else {
                            // 如果父级ID大于等于3 则不能再继续保存
                            $data     = $PublicKeywordsModel->create(I('post.'), 2);
                            if ( empty($data) ) {
                                $this->error($PublicKeywordsModel->getError());
                            } else {
                                if(!empty($data['reply_text'])){
                                    $data['reply_text'] = base64_encode($data['reply_text']);
                                }
                                if ( $PublicKeywordsModel->where(array('id'=>$id))->data($data)->save() >= 0 ) {
                                    $this->success('保存成功！',U('weixinReply'));
                                } else {
                                    $this->error('保存失败！');
                                }
                            }
                    }
            }
    }
    
    //关键词回复删除
    public function weixinReplyDel(){
            $id = I('get.id', '', 'int');

            if ( empty($id) ) {
                $this->error('ID 参数丢失！');
            }

            $public_keywords = M('public_keywords');
            // 删除本品牌与本品牌下面的子品牌
            if ( $public_keywords->where(array('id'=>$id))->delete() ) {
                $this->success('删除成功！');
            } else {
                $this->error('删除失败！');
            }
        
    }
    
    
    //自动回复
    public function weixinAutoReply(){
            if(IS_POST){
                $PublicMaterialText = D('PublicMaterialText');
                $id =   I('post.id',0);
                if($id  ==  0){
                        $data     = $PublicMaterialText->create(I('post.'), 1);
                        if (empty($data)) {
                            $this->error($PublicMaterialText->getError());
                        } else {
                            // 如果父级ID大于等于3 则不能再继续保存
                            $data['title']  =   '自动回复';
                            if(!empty($data['desc'])){
                                $data['desc']   = base64_encode($data['desc']);
                            }
                            if ($PublicMaterialText->data($data)->add()) {
                                $this->success('保存成功！', U('System/weixinAutoReply'));
                            } else {
                                $this->error('保存失败！');
                            }
                        }
                }  else {
                        $data     = $PublicMaterialText->create(I('post.'), 2);
                        if (empty($data)) {
                            $this->error($PublicMaterialText->getError());
                        } else {
                            // 如果父级ID大于等于3 则不能再继续保存
                            if(!empty($data['desc'])){
                                $data['desc']   = base64_encode($data['desc']);
                            }
                            if ($PublicMaterialText->data($data)->save()) {
                                $this->success('保存成功！', U('System/weixinAutoReply'));
                            } else {
                                $this->error('保存失败！');
                            }
                        }
                }
            }  else {
                
                $uid    = 8;
                $where  =   array();
                $where['uid']       =   $uid;
                $where['title']     =   '自动回复';
                $weixin_reply =   M('public_material_text')->where($where)->find();
                if(!empty($weixin_reply['desc'])){
                    $weixin_reply['desc']   = base64_decode($weixin_reply['desc']);
                }
                
                $this->assign('weixin_reply', $weixin_reply);
                $this->display('weixin_auto_reply');
            }
    }
    
    
    //关注回复语
    public function weixinSubscribe(){
            if(IS_POST){
                $PublicMaterialText = D('PublicMaterialText');
                $id =   I('post.id',0);
                if($id  ==  0){
                        $data     = $PublicMaterialText->create(I('post.'), 1);
                        if (empty($data)) {
                            $this->error($PublicMaterialText->getError());
                        } else {
                            // 如果父级ID大于等于3 则不能再继续保存
                            $data['title']  =   '首次关注';
                            if(!empty($data['desc'])){
                                $data['desc']   = base64_encode($data['desc']);
                            }
                            if ($PublicMaterialText->data($data)->add()) {
                                $this->success('保存成功！', U('System/weixinSubscribe'));
                            } else {
                                $this->error('保存失败！');
                            }
                        }
                }  else {
                        $data     = $PublicMaterialText->create(I('post.'), 2);
                        if (empty($data)) {
                            $this->error($PublicMaterialText->getError());
                        } else {
                            // 如果父级ID大于等于3 则不能再继续保存
                            if(!empty($data['desc'])){
                                $data['desc']   = base64_encode($data['desc']);
                            }
                            if ($PublicMaterialText->data($data)->save()) {
                                $this->success('保存成功！', U('System/weixinSubscribe'));
                            } else {
                                $this->error('保存失败！');
                            }
                        }
                }
            }  else {
                
                $uid    = 8;
                $where  =   array();
                $where['uid']       =   $uid;
                $where['title']     =   '首次关注';
                $weixin_reply =   M('public_material_text')->where($where)->find();
                if(!empty($weixin_reply['desc'])){
                    $weixin_reply['desc']   = base64_decode($weixin_reply['desc']);
                }
                
                $this->assign('weixin_reply', $weixin_reply);
                $this->display('weixin_subscribe');
            }
    }
    
    
    /*
     * 自定义菜单
     */
    public function weixinMenu() {
            
            //公众帐号信息
            $agentId    = 8;
            $PublicAccountInfo   =   M('public_account')->where(array('redid'=>$agentId))->find();
            $PublicAccountInfo['menu']  = !empty($PublicAccountInfo['menu']) ? json_encode(unserialize(base64_decode($PublicAccountInfo['menu']))) : '';
            $this->assign('info',$PublicAccountInfo);
            //应用分类
            $PublicAppCatModel      = M('PublicApplicationCategory');
            $PublicAppCatList       = $PublicAppCatModel->where(array('status'=>1))->field('id,cate_name')->select();
            $AppCatList             = array();
            if (!empty($PublicAppCatList))
            {
                    foreach ($PublicAppCatList as $k=>$v)
                    {
                            $AppCatList[$v['id']]   = $v['cate_name'];
                    }
                    unset($PublicAppCatList);
            }
            $this->assign('AppCatList',$AppCatList);
            //应用列表
            $PublicAppModel         = M('PublicApplication');
            $PublicAppList          = $PublicAppModel->where(array('status'=>1))->field('id,title,mold,template_url')->select();
            $AppList                = array();
            if (!empty($PublicAppList))
            {
                    foreach($PublicAppList AS $value){
                            $AppList[$AppCatList[$value['mold']]][] = $value;
                            $AppId[]                                = $value['id'];
                    }
            }
            $this->assign('AppList',$AppList);
            $this->display('weixin_create_menu');
    }
    
    
    public function saveMenu()  {
            $id                     = I('post.id');
            //公众帐号信息
            $PublicAccountModel     = D('PublicAccount');
            $Fields                 = array('id','redid');
            $PublicAccountInfo      = $PublicAccountModel->field($Fields)->find($id);
            if(empty($PublicAccountInfo)) $this->error('接口不存在!');
            if ($PublicAccountInfo['redid'] != 8) $this->error('权限不足!');
            $menu                   = I('post.menu','','trim');
            $menu                   = !empty($menu) ? json_decode(stripslashes($menu),true) : $this->error('保存失败：菜单数据不能为空!');
            //过滤空数据
            foreach($menu as $key=>$value){
                    if(empty($value['name'])){
                            unset($menu['menu'][$key]);
                    }else{
                            if(!empty($menu['list'])){
                                    foreach($value['list'] as $k=>$v){
                                            if(empty($v['name'])){
                                                    unset($menu['menu'][$key]['list'][$k]);
                                            }
                                    }
                            }elseif(empty($value['list'])){
                                    unset($menu['menu'][$key]['list']);
                            }
                    }
            }
            $updata['id']       = $PublicAccountInfo['id'];
            $updata['menu']     =!empty($menu) ? base64_encode(serialize($menu)) : '';
            $PublicAccountModel->save($updata);
            $this->success('保存成功!',U('weixinMenu'));
    }

    public function Store() {
            $list = M('offline_stores')->field(true)->select();
    //        dump($list);die;
            $this->assign("list", $list);
            $this->display('Store');
    }

    public function addStore() {
            if (IS_POST) {
                $data                       =   array();
                $data['name']           =   I('post.name');
                $data['logo']           =   I('post.logo');
                $data['address']        =   I('post.address');
                $data['dateline']       =   NOW_TIME;
                $res = M('offline_stores')->add($data);
                if ($res) {
                    $this->success("保存成功",U('Store'));
                } else {
                    $this->error("保存失败");
                }
            } else {
                $this->display('addStore');
            }
    }

    public function editStore() {
            if (IS_POST) {
                $id = I('post.id');
                $data                       =   array();
                $data['name']           =   I('post.name');
                $data['logo']           =   I('post.logo');
                $data['address']        =   I('post.address');
                $data['dateline']       =   NOW_TIME;
                $res = M('offline_stores')->where("id = $id")->setField($data);
                if ($res) {
                    $this->success("保存成功",U('Store'));
                } else {
                    $this->error("保存失败");
                }
            } else {
                $id = I('get.id');
                $info = M('offline_stores')->where("id = $id")->find();
                
    //            dump($info);die;
                $this->assign("info", $info);
                
                
                $this->display('editStore');
            }
    }

    public function delStore() {
        $id = I('get.id',0,'intval');
        if (!$id) {
            $this->error("参数错误");
        }
        $res = M('offline_stores')->where("id = $id")->delete();
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error("删除失败");
        }
    }
}
