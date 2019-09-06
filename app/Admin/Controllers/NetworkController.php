<?php

namespace App\Admin\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Encore\Admin\Layout\Content;
use Encore\Admin\Grid;
use App\Admin\Models;



class NetworkController extends Controller
{
   	
	public function show(){
		if(!empty(app('request')->get('test'))){
			var_dump(config('database'));exit;
		}
		$content	= new Content;
		return $content
			->header('流量监控')
			->description('流量监控')
			->body($this->grid(20190625));
	}
	
	private function grid($dateymd){
		$grid = new Grid(new \App\Admin\Models\Network());
		$grid->actions(function ($actions) {
			// append一个操作
			$actions->append('<a href=""><i class="fa fa-eye"></i></a>');

			// prepend一个操作
			$actions->prepend('<a href=""><i class="fa fa-paper-plane"></i></a>');
		});
		//var_dump($grid->model()->getNetworkList(20190625,10));exit;
		$grid->model()->getNetworkList(20190625,10);
		$grid->_id('id')->sortable();
		$grid->column('_family_server_id','机器')->sortable();
		//var_dump($grid->model()->getNetworkList(20190625,10));exit;
        $grid->paginate(6);
		return $grid;
	}
	
}
