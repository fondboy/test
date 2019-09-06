<?php
namespace App\Admin\Services;

use App\Admin\Models\WarehouseModel;
use App\Admin\Services\AdminCommonService;

class WarehouseService extends AdminCommonService
{

	private $mallModel;
	public function __construct()
	{
		$this->mallModel	= new WarehouseModel;
	}

	public function getList($page=0,$pagesize=20){

	}


}
