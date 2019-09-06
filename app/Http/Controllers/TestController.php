<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use DB;
class TestController extends Controller
{
	// 测试 用
	// > php artisan key:generate
	// Application key [base64:6hdSXPSz0286ITXUmvGpDJH6SP092+RY1kGGZUSV6Ds=] set successfully.
    public function  testindex(){
	
		$user = DB::select('select * from ahead_user where _id>0 limit 1 ');
		dd($user);
		echo 'hello world';die;
	}
	
	
}
	
	
	
