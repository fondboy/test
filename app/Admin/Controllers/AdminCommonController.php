<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Encore\Admin\Facades\Admin;
use Request;
use DB;


class AdminCommonController extends Controller
{

    public function __construct()
    {
        $request = Request::instance();
        if ($request->headers->has("X-PJAX")) {
            $request->headers->set("X-PJAX", false);
        }
        // 分析菜单
        if(!empty(app('request')->get('sys'))){
            $sysArr = explode('/',base64_decode(app('request')->get('sys')));
            if(isset($sysArr[2])){
                // todo 菜单分析
                $insertArr  = ['user_id'=>Admin::user()->id,'menu_id'=>$sysArr[2],'time'=>time()];
                unset($sysArr[2]);
                $header     = implode('/',$sysArr);
                $insertArr[ 'menu_info'] = $header;
                DB::table('admin_user_menu_log')->insert($insertArr);
            }

            if(isset($this->header)&&isset($header)){
                $this->header   = $header;
            }
        }
    }
    static function log($name,$title,$content)
    {
        $log = new Logger($name);
	    $log->pushHandler(new StreamHandler('/ubox/logs/pfadmin/app/logs/' . $name .  '.log', 0));
	    $log->addInfo($title . '：' . $content);
    }
    
    static function getServerIP(){
        if(isset($_SERVER)){
            if($_SERVER['SERVER_ADDR']){
                $server_ip=$_SERVER['SERVER_ADDR'];
            }else{
                $server_ip=$_SERVER['LOCAL_ADDR'];
            }
        }else{
            $server_ip = getenv('SERVER_ADDR');
        }
        return $server_ip;
    }
    
	public function webSuccessJson($msg='success',$data=[],$extData=[]){
		$arr		= ['data'=>$data,'code'=>0 ,'msg'=>$msg];
		return response()->json(array_merge($arr,$extData));
	}
	public function webErrorJson($msg='fail',$data=[],$extData=[]){
		$arr		= ['data'=>$data,'code'=>1000 ,'msg'=>$msg];
		return response()->json(array_merge($arr,$extData));
	}

}
