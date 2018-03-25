<?php
namespace Xadmin\Controller;
use Think\Controller;
// 公共控制器
class PublicController extends Controller {
    /**
     * [auth 权限认证]
     * @author TF <[2281551151@qq.com]>
     */
    public function auth($adminId) {
        $auth        = new \Think\Auth();

        // 普通用户得到权限列表
        $getAuthList = $auth->getAuthList($adminId, 1);

        if ( empty($getAuthList) ) {
            session(null);
            $this->error('你的账号没任何操作权限！', U('Login/login'));
        }

        session('authList', $getAuthList);

        if ( !$auth->check(CONTROLLER_NAME.'-'.ACTION_NAME , $adminId) ) {

            // 无访问权限的时候才跳转
            $white = in_array(CONTROLLER_NAME.'-'.ACTION_NAME, array(
                'Login-login','Index-index'
            ));

            $preJumpUrl = session('preJumpUrl');
//             if(session('adminId')==1) return true;
            if ( $white || empty($preJumpUrl) ) {
                // 找出可直接跳转的权限地址
                $canJumpList = M('admin_auth_rule')->where(array('direct_jump'=>'1'))->order('sort DESC')->getField('name', true);
                foreach ($getAuthList as $value) {
                    foreach ( $canJumpList as $jumpValue ) {
                        if ( strtolower($value) == strtolower($jumpValue) ) {
                            $url = str_replace('-', '/', $jumpValue);
                            session('preJumpUrl', $url);
                            header('LOCATION:' . U($url));
                            exit();
                        }
                    }
                }
            } else {
                header("Content-type:text/html;charset=utf-8");
                exit('你没有足够的权限访问该地址！<a href="' . U($preJumpUrl) . '">跳转到可访问页面</a>');
            }
        }
    }

    /**
     * [img 动态图片]
     * @author TF <[2281551151@qq.com]>
     */
    public function img() {
        $size = I('get.size');

        if ( empty($size) ) {
            $size = '100x100';
        }

        preg_match('/(\d+).(\d+)/', $size, $match);

        $im     = imagecreatetruecolor($match[0], $match[1]);
        $gray   = imagecolorallocate($im, 175, 175, 175);
        imagefill($im, 0, 0, $gray);

        $font   = './ThinkPHP/Library/Think/Verify/ttfs/4.ttf';
        $black  = imagecolorallocate($im, 0, 0, 0);
        $text   = $match[1] . ' x ' . $match[2];

        if ( $match[1] > $match[2] ) {
            $size = round($match[2] * 0.1);
        } else {
            $size = round($match[1] * 0.1);
        }

        $array = imagettfbbox($size, 0, $font, $text);
        $x = round(($match[0] / 2) - ($array[2] / 2));
        $y = round($match[1] / 2);
        imagettftext($im, $size, 0, $x, $y, $black, $font, $text);
        header('Content-Type: image/jpeg');
        imagejpeg($im);
        imagedestroy($im);
    }
}