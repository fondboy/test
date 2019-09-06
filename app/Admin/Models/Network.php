<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Network extends Model
{
    //
	protected $table = 'ahead_yc_server_netcost_sum'; 
	
	/* protected __construct(){
		parent::__construct();
	} */
	
	public function getNetworkList($dateymd,$limit=20){
		//return $users = DB::table($this->table)
           // ->where('_dateymd','=',$dateymd)->paginate($limit);
			
		return $this->where('_dateymd',$dateymd)
				->select('*')
               ->orderBy('_eth_rx', 'desc');
	}
	
}
