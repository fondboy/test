<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class AminCommonModel extends Model
{

    public function __construct()
    {
        parent::__construct();
        DB::enableQueryLog();
    }

    public function getQueueableRelations(){

	}
	
}
