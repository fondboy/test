<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

use  App\Admin\Models\AminCommonModel;


class WarehouseModel extends AminCommonModel
{

    protected $table = 'warehouse_copy';
    const UPDATED_AT = 'update_at';
    public function __construct()
    {
        parent::__construct();
    }

    /* 获取列表 */
    public function getList($whereArr,$page=1,$pagesize=2,$field='*')
    {
        $whereArr[]    = ['delete_mark','=',0]; // 未删除状态
        if($page){

            return   DB::table($this->table)
                ->where($whereArr)->skip(($page-1)*$pagesize)->take($pagesize)->get()->toArray();
        }else{
            return DB::table($this->table)
                ->where($whereArr)->get()->toArray();
        }
    }

    /* 获取列表 */
    public function getCount($whereArr)
    {
        $whereArr[]    = ['delete_mark','=',0]; // 未删除状态
        return DB::table($this->table)->where($whereArr)->count();
    }

    /*删除*/
    public function updateData($where,$update,$update_at='-1'){
        //$update['update_at']    = date('Y-m-d H:i:s');
        if($update_at!='-1'){
            $where['update_at'] = $update_at;
        }

        return DB::table($this->table)
            ->where($where)
            ->update($update);
    }


}
