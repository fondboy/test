<?php
namespace App\Admin\Services;

use Illuminate\Database\Eloquent\Model;
use DB;

class MallService
{

	private $mallModel;
	public function __construct($mallModel)
	{
		$this->mallModel	= $mallModel;
	}


	public function getAllCount(){
		return 12;
	}

	public function getMallList($page){

	}


}
