<?php

/**
 * 支付控制器
 * 王远庆
 */

namespace Api\Controller;

use Think\Db;
use Think\Controller;

class GameController extends CommonController {
    public function __construct() {
        parent::__construct();
        $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
    }

    //房卡充值
    public function setUserBind() {
            $this->errorInfo = $this->getApiError(ACTION_NAME);
            $CheckParam = array(
                array('time', 'Int', 1, '服务器时间异常', '1'),
                array('hash', 'String', 1, '签名错误', '2'),
                array('uid', 'Int', 1, $this->errorInfo['1001'], '1001'),
                array('invitation_code', 'String', 1, $this->errorInfo['1002'], '1002'),
            );
            $BackData = $this->CheckData(I('request.'), $CheckParam);
            //支付类型校验
            if (empty($BackData['invitation_code'])) {
                $this->ReturnJson(array('code' => '1007', 'msg' => '邀请码不能为空'));
            }
            $BackData['invitation_code'] = strtolower($BackData['invitation_code']);
            $agentInfo  =   M('agent')->where(array('invitation_code'=>$BackData['invitation_code']))->field(true)->find();
            if (empty($agentInfo)) {
                # code...
                $this->ReturnJson(array('code' => '1008', 'msg' => '请输入正确的邀请码'));
            }
            if ($agentInfo['is_delete'] == 1) {
                # code...
                $this->ReturnJson(array('code' => '1009', 'msg' => '请输入正确的邀请码'));
            }
            if ($agentInfo['is_lock'] == 1) {
                # code...
                $this->ReturnJson(array('code' => '1010', 'msg' => '请输入正确的邀请码'));
            }
            
            $gameuserModel  = M('gameuser');
            $gameuser       = $gameuserModel->where(array('game_user_id'=>$BackData['uid'],'user_id'=>0))->field(true)->find();
            if (!empty($gameuser)) {
                $gameuser_data = array();
                $gameuser_data['invitation_code']   = $BackData['invitation_code']; //支付编号
                $gameuser_data['update_time']       = NOW_TIME;
                $res = $gameuserModel->where(array('game_user_id'=>$BackData['uid'],'user_id'=>0))->setField($gameuser_data);
            }else{
                $gameuser_data = array();
                $gameuser_data['invitation_code']   = $BackData['invitation_code']; //支付编号
                $gameuser_data['game_user_id']      = $BackData['uid'];
                $gameuser_data['add_time']          = NOW_TIME;
                $gameuser_data['update_time']       = NOW_TIME;
                $res = $gameuserModel->add($gameuser_data);
                //绑定成功，赠送20张房卡
                $this->giveUser($BackData['uid'],20,4,'绑定邀请码');
            }
            if ($res) {
                $this->ReturnJson(array('code' => '0', 'msg' => '绑定成功', 'data' => $BackData['invitation_code']));
            }
            $this->ReturnJson(array('code' => '1044', 'msg' => '绑定失败'));
    }

     /*
     *赠送房卡
     */
    protected function giveUser($game_user_id,$pay_nums = 1,$type = 3,$use_text = '绑定邀请码'){

            //查当前代理房卡数量
            $sqlsrv_model1   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
            $playerCard  =   $sqlsrv_model1->table('GameScoreInfo')->where(array('UserID'=>$game_user_id))->field("UserID,InsureScore,Score")->find();
            dblog(array('api/game/giveUser 1001','$game_user_id'=>$game_user_id,'playerCard'=>$playerCard,'sql'=>$sqlsrv_model1->table('GameScoreInfo')->getLastSql()));
            if(empty($playerCard)){
                $this->ReturnJson(array('code' => '1031', 'msg' => '玩家游戏数据错误'));
            }

            //玩家增加房卡数
            $buyer_gamescore_update     =   $sqlsrv_model1->table('GameScoreInfo')->where(array('UserID'=>$playerCard['userid']))->setField(array('InsureScore'=>$playerCard['insurescore']+$pay_nums));

            //玩家信息
            $sqlsrv_model2   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
            $player_accounts_info   =   $sqlsrv_model2->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();

            //充值记录
            $user_recharge_recored_data  =   array();
            $user_recharge_recored_data['user_id']       =   $player_accounts_info['userid'];
            $user_recharge_recored_data['pay_nums']      =   $pay_nums;
            $user_recharge_recored_data['desc']          =   '玩家['.$player_accounts_info['nickname'].']'.$use_text.'，赠送房卡：'.$pay_nums.'张';
            $user_recharge_recored_data['add_time']      =   NOW_TIME;
            $user_recharge_recored_data['type']          =   $type;
            M('user_recharge_recored')->add($user_recharge_recored_data);
    }

    /*
    *查看玩家绑定信息
    */
    public function getUserBindInfo(){
            $this->errorInfo = $this->getApiError(ACTION_NAME);
            $CheckParam = array(
                array('time', 'Int', 1, '服务器时间异常', '1'),
                array('hash', 'String', 1, '签名错误', '2'),
                array('uid', 'Int', 1, $this->errorInfo['1001'], '1001'),
            );
            $BackData = $this->CheckData(I('request.'), $CheckParam);
            $gameuser  =   M('gameuser')->where(array('game_user_id'=>$BackData['uid'],'user_id'=>0))->field(true)->find();
            if (empty($gameuser)) {
                # code...
                $this->ReturnJson(array('code' => '1044', 'msg' => '未绑定代理邀请码','data'=>'0'));
            }
            $this->ReturnJson(array('code' => '0', 'msg' => 'ok', 'data' => $gameuser['invitation_code']));
    }

    
    /*
    *分享活动配置
    */
    public function shareActivity(){
        dblog(array('shareActivity 1001','request'=>I('request.')));
            $this->errorInfo = $this->getApiError(ACTION_NAME);
            $CheckParam = array(
                array('time', 'Int', 1, '服务器时间异常', '1'),
                array('hash', 'String', 1, '签名错误', '2'),
                array('uid', 'Int', 1, $this->errorInfo['1001'], '1001'),
            );
            $BackData = $this->CheckData(I('request.'), $CheckParam);

            //检索配置
            $where  =   array();
            $where['StatusName']    =   array('in',array('ShareAppReward','ShareRewardCnt','ShareRewardInsure','ShareRewardScore'));
            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB', 'SystemStatusInfo');
            $SystemStatusInfo    = $sqlsrv_model->table('SystemStatusInfo')->where($where)->field(true)->select();
            $share_activity_config =   array();
            if (!empty($SystemStatusInfo)) {
                foreach ($SystemStatusInfo as $key => $value) {
                    $share_activity_config[$value['statusname']] = $value['statusvalue'];
                }

                if (empty($share_activity_config['ShareAppReward'])) {  //判断是否开启
                    $this->ReturnJson(array('code' => '4001', 'msg' => '分享活动未开启','data'=>'0'));
                }elseif (empty($share_activity_config['ShareRewardCnt'])) { //判断每天分享次数
                    $this->ReturnJson(array('code' => '4002', 'msg' => '分享活动未开启','data'=>'0'));
                }elseif (empty($share_activity_config['ShareRewardInsure']) && empty($share_activity_config['ShareRewardScore'])) {
                    $this->ReturnJson(array('code' => '4003', 'msg' => '分享活动未开启','data'=>'0'));
                }

            }else{
                    $this->ReturnJson(array('code' => '4004', 'msg' => '分享活动未开启','data'=>'0'));
            }

            $where      =   array();
            $where['game_user_id']  =   $BackData['uid'];
            $where['today']         =   date('Y-m-d');
            $share_user_log_count   =   M('share_user_log')->where($where)->count();
            if ($share_user_log_count < $share_activity_config['ShareRewardCnt']) {
                //赠送房卡或金币
                $data = array();
                $data['game_user_id']   =   $BackData['uid'];
                $data['add_time']       =   NOW_TIME;
                $data['today']          =   date('Y-m-d');
                $share_id = M('share_user_log')->add($data);

                //玩家增加金币数
                if (!empty($share_activity_config['ShareRewardScore'])) {
                    $this->giveUser($BackData['uid'],$share_activity_config['ShareRewardScore'],3,'玩家分享');
                }

                //成功返回结果
                $json_Data =  array();
                $json_Data['add_InsureScore']   = $share_activity_config['ShareRewardInsure'];
                $json_Data['add_Score']         = $share_activity_config['ShareRewardScore'];
                $this->ReturnJson(array('code' => '0', 'msg' => 'ok', 'data' => $json_Data));

            }
            $this->ReturnJson(array('code' => '4005', 'msg' => '分享活动已完成','data'=>'0'));
    }

    //跑马条公告
    public function getPaomatiao(){
            $CheckParam = array(
                array('time', 'Int', 1, '服务器时间异常', '1'),
                array('hash', 'String', 1, '签名错误', '2'),
                array('uid', 'Int', 1, '用户参数错误', '1001'),
            );
            $BackData = $this->CheckData(I('request.'), $CheckParam);

            $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB', 'WeiXinInfo');
            $GameWeixin    = $sqlsrv_model->table('WeiXinInfo')->where($where)->field('GameWeixin')->find();

            $this->ReturnJson(array('code' => '0', 'msg' => 'ok', 'data' =>$GameWeixin['gameweixin'] ));
    }

    //实名认证
    public function playerAuthentication(){
            dblog(array('playerAuthentication 1001','request'=>I('request.')));
            $CheckParam = array(
                array('time', 'Int', 1, '服务器时间异常', '1'),
                array('hash', 'String', 1, '签名错误', '2'),
                array('uid', 'Int', 1, '用户参数错误', '1001'),
                array('idcard', 'String', 1, '联系方式', '1002'),
                array('name', 'String', 1, '姓名', '1003'),
            );
            $BackData = $this->CheckData(I('request.'), $CheckParam);


            $where      =   array();
            $where['idcard']  =   $BackData['idcard'];
            $gameuser_authentication   =   M('gameuser_authentication')->where($where)->count();
            if ($gameuser_authentication > 0) {
                    $this->ReturnJson(array('code' => '3001', 'msg' => '当前身份证号已经被使用','data'=>'0'));
            }

            $where      =   array();
            $where['game_user_id']  =   $BackData['uid'];
            $gameuser_authentication   =   M('gameuser_authentication')->where($where)->count();
            if ($gameuser_authentication > 0) {
                    $this->ReturnJson(array('code' => '2001', 'msg' => '你已经提交过了','data'=>'0'));
            }

            $check_idcard = is_qili_card(trim($BackData['idcard']));
            if (!$check_idcard) {
                    $this->ReturnJson(array('code' => '2003', 'msg' => '身份证格式错误','data'=>'0'));
            }
            // if (strlen($BackData['idcard']) != 15 && strlen($BackData['idcard']) != 18) {
            //         $this->ReturnJson(array('code' => '2003', 'msg' => '身份证格式错误','data'=>'0'));
            // }

            //实名认证
            $gameuser_authentication_data  =   array();
            $gameuser_authentication_data['game_user_id']       =   $BackData['uid'];
            $gameuser_authentication_data['dateline']      =   NOW_TIME;
            $gameuser_authentication_data['idcard']         =   $BackData['idcard'];
            $gameuser_authentication_data['name']         =   $BackData['name'];
            $res = M('gameuser_authentication')->add($gameuser_authentication_data);

            if ($res) {
                $this->giveUser($BackData['uid'],5,5,'实名认证');
                $this->ReturnJson(array('code' => '0', 'msg' => 'ok','data'=>'0'));
            }
            $this->ReturnJson(array('code' => '2002', 'msg' => '保存失败','data'=>'0'));

        
    }


    //实名认证信息查询
    public function getPlayerAuthenticationInfo(){
            $CheckParam = array(
                array('time', 'Int', 1, '服务器时间异常', '1'),
                array('hash', 'String', 1, '签名错误', '2'),
                array('uid', 'Int', 1, '用户参数错误', '1001'),
            );
            $BackData = $this->CheckData(I('request.'), $CheckParam);
            $where      =   array();
            $where['game_user_id']  =   $BackData['uid'];
            $gameuser_authentication   =   M('gameuser_authentication')->where($where)->field(true)->find();
            if (!empty($gameuser_authentication)) {
                    $this->ReturnJson(array('code' => '1', 'msg' => '你已经提交过了','data'=>'0','idcard'=>$gameuser_authentication['idcard'],'name'=>$gameuser_authentication['name']));
            }

            $this->ReturnJson(array('code' => '0', 'msg' => 'ok','data'=>'0'));
    }

    //首次赠送数量
    public function getInsureScoreNums(){
            $CheckParam = array(
                array('time', 'Int', 1, '服务器时间异常', '1'),
                array('hash', 'String', 1, '签名错误', '2'),
                array('uid', 'Int', 1, '用户参数错误', '1001'),
            );
            $BackData = $this->CheckData(I('request.'), $CheckParam);

            $ShareRewardScore = S('ShareRewardScore');
            if (empty($ShareRewardScore)) {
                $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB', 'SystemStatusInfo');
                $where      =   array();
                $where['StatusName']  =   "ShareRewardScore";
                $SystemStatusInfo    = $sqlsrv_model->table('SystemStatusInfo')->where($where)->field('StatusName,StatusValue')->find();

                S('ShareRewardScore',$ShareRewardScore,60);

                $this->ReturnJson(array('code' => '0', 'msg' => 'ok', 'data' =>$SystemStatusInfo['statusvalue'] ));
            }

            $this->ReturnJson(array('code' => '0', 'msg' => 'ok', 'data' =>$ShareRewardScore ));
    }

    //分享配置信息
    public function getShareInfo(){
            $CheckParam = array(
                array('time', 'Int', 1, '服务器时间异常', '1'),
                array('hash', 'String', 1, '签名错误', '2'),
                array('uid', 'Int', 1, '用户ID错误', '1001'),
            );
            $BackData = $this->CheckData(I('request.'), $CheckParam);

            $share_result = S('share_result');
            if (empty($share_result)) {
                $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB','WeiXinInfo');
                $where    = array('1=1');
                $share_info    = $sqlsrv_model->table('WeiXinInfo')->where($where)->field(true)->find();

                $share_result = array();
                $share_describe = !empty($share_info['fangkaweixin']) ? trim($share_info['fangkaweixin']) : APP_NAME;
                $share_result['share_describe'] = $share_describe;
                $share_url = !empty($share_info['adviceweixin']) ? trim($share_info['adviceweixin']) : 'http://'.WEB_DOMAIN.'/Home/Download';
                $share_result['share_url'] = $share_url;
                $share_icon = !empty($share_info['proxyweixin']) ? trim($share_info['proxyweixin']) : 'http://'.WEB_DOMAIN.'/Public/Game/download/img/logo2.png';
                $share_result['share_icon'] = $share_icon;
                S('share_result',$share_result,60);
            }else{
                $share_describe = !empty($share_result['share_describe']) ? trim($share_result['share_describe']) : APP_NAME;
                $share_url = !empty($share_result['share_url']) ? trim($share_result['share_url']) : 'http://'.WEB_DOMAIN.'/Home/Download';
                $share_icon = !empty($share_result['share_icon']) ? trim($share_result['share_icon']) : 'http://'.WEB_DOMAIN.'/Public/Game/download/img/logo2.png';
            }
            $result_data = array(
                'code' => '0',
                'msg' => 'ok',
                'share_describe' => $share_describe,
                'share_url' => $share_url,
                'share_icon' => $share_icon,

            );
            $this->ReturnJson($result_data);
    }

    /*
    *查看公告/系统消息（默认取最新一条消息）
    */
    public function getOneSystemNews(){
            $CheckParam = array(
                array('time', 'Int', 1, '服务器时间异常', '1'),
                array('hash', 'String', 1, '签名错误', '2'),
                array('uid', 'Int', 1, '用户ID错误', '1001'),
            );
            $BackData = $this->CheckData(I('request.'), $CheckParam);
            $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB','SystemMessage');
            $where    = array('1=1');
            $news    = $sqlsrv_model->table('SystemMessage')->where($where)->order('ID DESC')->find();

            $this->ReturnJson(array('code' => '0', 'msg' => 'ok', 'data' => $news['messagestring']));
    }

    //错误信息总会
    protected function getApiError($apiname) {
            $ApiError = array();
            //店铺广告列表
            $ApiError['setUserBind']['1001'] = '用户ID';
            $ApiError['setUserBind']['1002'] = '邀请码';
            $ApiError['getUserBindInfo']['1001'] = '用户ID';
            $ApiError['getUserBindInfo']['1002'] = '邀请码';
            return $ApiError[$apiname];
    }

}

?>