<?php

namespace Xadmin\Controller;

use Think\Controller;

// 控制台控制器
class IndexController extends BaseController {
    
    //首页
    public function index(){
        if(session('adminId') && session('adminAccount')){
            redirect(U("statistics"));
        }  else {
            redirect(U("Login/login"));
        }
    }
    /**
     * [statistics 统计]
     * @author TF <[2281551151@qq.com]>
     */
    public function statistics() {

        $info = array(
            '操作系统' => PHP_OS,
            '运行环境' => $_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式' => php_sapi_name(),
            '上传附件限制' => ini_get('upload_max_filesize'),
            '执行时间限制' => ini_get('max_execution_time').'秒',
            '服务器时间' => date("Y年n月j日 H:i:s"),
            '北京时间' => gmdate("Y年n月j日 H:i:s",time() + 8 * 3600),
            '服务器域名/IP' => $_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '剩余空间' => round((@disk_free_space(".") / (1024 * 1024)),2).'M',
            'register_globals' => get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
            'magic_quotes_gpc' => (1 === get_magic_quotes_gpc()) ? 'YES' : 'NO',
            'magic_quotes_runtime' => (1 === get_magic_quotes_runtime())?'YES':'NO',
        );
        $this->assign('info',$info);
        $this->display('statistics');
    }

    /**
     * [clearCache 清理缓存]
     * @author TF <[2281551151@qq.com]>
     */
    public function clearCache() {
        if (IS_POST) {
            $id = I('post.clearid');
            $clearList = I('post.clearlist');

            if (empty($id)) {
                if (empty($clearList)) {
                    $this->error('参数丢失！');
                }
                session('clearList', $clearList);
            } else {
                if (empty($id)) {
                    $this->error('参数丢失！');
                }
                $clearList = array($id);
                session('clearList', $clearList);
            }

            header('LOCATION:' . U('Index/removeCache'));
            exit();
        } else {
            $this->display('clearCache');
        }
    }

    /**
     * [clearCache 清理缓存]
     * @author TF <[2281551151@qq.com]>
     */
    public function removeCache() {
        $clearlist = session('clearList');
        if (!empty($clearlist)) {
            $id = array_shift($clearlist);
            session('clearList', $clearlist);
            switch ($id) {
                case '11':
                    $name = "PC端商品详情页面缓存";

                    break;

                case '12':
                    $name = "PC端运行时(RUNTIME)缓存";
                    $path = './Application/Runtime/Cache/Shop/';
                    if (file_exists($path)) {
                        $this->rrmdir('./Application/Runtime/Cache/Shop/');
                    }
                    break;

                case '21':
                    $name = "WAP端商品详情页面缓存";

                    break;

                case '22':
                    $name = "WAP端运行时(RUNTIME)缓存";
                    $path = './Application/Runtime/Cache/Wap/';
                    if (file_exists($path)) {
                        $this->rrmdir('./Application/Runtime/Cache/Wap/');
                    }
                    break;

                case '31':
                    $name = "商品分类列表缓存";
                    S('levelCategoryList', null);
                    break;

                case '32':
                    $name = "地区列表缓存";
                    S('levelRegionList', null);
                    break;

                case '41':
                    $name = "后台运行时(RUNTIME)缓存";
                    $path = './Application/Runtime/Cache/Admin/';
                    if (file_exists($path)) {
                        $this->rrmdir('./Application/Runtime/Cache/Admin/');
                    }
                    break;

                case '51':
                    $name = "全站缓存";
                    $variable = array(
                        '11',
                        '12',
                        '21',
                        '22',
                        '31',
                        '32',
                        '41',
                    );

                    session('clearList', $variable);
                    header('LOCATION:' . U('Index/removeCache'));
                    exit();
                    break;
            }

            $this->assign('flag', '0');
            $this->assign('msg', $name . '清理成功！');
            $this->display('removeCache');
        } else {
            $this->assign('flag', '1');
            $this->assign('msg', $name . '完成清理任务!');
            $this->display('removeCache');
        }
    }

    /**
     * [rrmdir 删除目录]
     * @author TF <[2281551151@qq.com]>
     */
    private function rrmdir($src) {
        $dir = opendir($src);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if (is_dir($full)) {
                    $this->rrmdir($full);
                } else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }

}
