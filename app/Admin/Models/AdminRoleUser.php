<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use App\Admin\Models\AdminUser;

class AdminRoleUser extends Model
{
    //
	protected $table		= 'admin_role_users';
	public $timestamp		= false;
	protected $primaryKey 	= '';
	
		
	public function user(){
		return $this->belongsTo('App\Admin\Models\AdminUser','user_id','id');
	}
}
