<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use App\Admin\Models\AdminRoleUser;

class AdminUser extends Model
{
    //
	protected $table	= 'admin_users';
	public $timestamp	= false;
	
	public function role(){
		return $this->hasMany('App\Admin\Models\AdminRoleUser','user_id','id');
	}
	
		
}
