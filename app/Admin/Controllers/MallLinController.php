<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminCommonController;
use  Encore\Admin\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Grid;
use App\Admin\Models\Network;
use App\Admin\Services\MallService;
use App\Admin\Models\WarehouseModel;
use Illuminate\Support\Facades\Log;
use DB;



class MallLinController extends AdminCommonController
{
   /*  public function __construct(){
		parent::__construct(); 
	} */
	
	public function index(){

		//var_dump($this->grid(20190625));exit;
      /*  $content    = new Content;
        $data       =  (new Network() )->getNetworkList(20190625,10);

        return $content
            ->header('index流量监控')
            ->description('流量监控')
            ->body( view('admin.test',['data'=>$data]));*/

		/*return  view('admin.ybPf.mall.index',[]);*/

		Log::useDailyFiles(storage_path('logs/zabbix/1'),0);
		$error	= 'An informational message.';
		Log::emergency($error);
		Log::alert($error);
		Log::critical($error);
		Log::error($error);
		Log::warning($error);
		Log::notice($error);
		Log::info($error);
		Log::debug($error);

		$selectList	= [['id'=>1,'name'=>'name1'],['id'=>2,'name'=>'name2'],['id'=>3,'name'=>'name3']];
        $content    = new Content;
        return $content
			->header('点位资源管理/仓库创建 ')
			->description(' ')
			->view('admin.ybPf.mall.index',['selectList'=>$selectList]);
	}


	/*
	 仓列表
	 * */
	public function warehouseTable(){
		$page		= app('request')->get('page')?? 1;
		$pagesize	= app('request')->get('limit')?? 15;


		// 载入模型
		//$mallService	= new MallService;
		$mallModel	= new WarehouseModel;
		$data 		= $mallModel->getList([],$page,$pagesize);
		$count 		= $mallModel->getCount([]);
		return $this->webSuccessJson('',$data,['count'=> $count]);

	}

	/* 删除 */
	public function warehouseDel(){
		$id				= app('request')->get('id')?? 0;
		$update_at		= app('request')->get('update_at')?? 0;
		$id		= intval($id);
		if(!$id){
			return $this->webErrorJson('id为空');
		}

		// 载入模型
		$mallModel	= new WarehouseModel;
		$res 		= $mallModel->updateData(['id'=>$id],['delete_mark'=>1],$update_at);

		if($res){
			return $this->webSuccessJson();
		}

		return $this->webErrorJson('数据库操作失败');

	}

	public function aaa(){
        $content    = new Content;
        $data       =  (new Network() )->getNetworkList(20190625,10);
        return $content
			->view('admin.test',['data'=>$data]);
	}
	
	public function show(Content $content){
		return $content
			->header('show流量监控')
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
		$grid->model()->getNetworkClosure(20190625,10);

		$grid->_id('id')->sortable();
		//var_dump($grid->model()->getConnection());exit;
		$grid->column('_family_server_id','机器')->sortable();
		//var_dump($grid->model()->getNetworkList(20190625,10));exit;
        $grid->paginate(6);
		return $grid;
	}
	
}
