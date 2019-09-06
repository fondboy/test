<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ResourceManagement extends Model
{
    //获取仓库列表
    public static function getWarehouseList($where,$limit)
    {
      return  DB::table('warehouse')->select('*')->where($where)->orderBy('create_at','desc')->paginate($limit)->toArray();
    }
    
    //获取全部仓库信息
    public static function getAllWarehouse()
    {
        return  DB::table('warehouse')->select('name','barn_id','org5_id')->where('delete_mark',0)->get();
    }
    
    //仓库更新
    public static function updateWareHouse($sql,$set){
        return DB::update($sql,$set);
    }
    
    public static function getArea($where){
        $china_area = DB::table('china_area')->select('code','name','id','parent_id')->where($where)->get();
        return  $china_area->toArray();
    }
    
    //获取商场列表
    public static function getMallList($where,$limit){
       $tmall_market= DB::table('tmall_market')->select('*')->where($where)->orderBy('create_at','desc')->paginate($limit);
        return $tmall_market->toArray();
    }
    
    public static function getAllMall()
    {
        $where = [
            ['delete_mark','=',0],
            ['ub_barn_id','!=',''],
            ['ub_barn_id','>=',0]
        ];
        return  DB::table('tmall_market')->select('market_id','market_name','ub_barn_id')->where($where)->get();
    }
    
    //商场更新
    public static function updateTmallMarket($where,$set){
        return DB::table('tmall_market')->where($where)->update($set);
    }
    
    
    //获取点位列表
    public static function getSiteList($where,$limit){
        $where[] = ['warehouse.binding_barn_id','!=',0];
        $siteList = DB::table('site')->join('warehouse','site.org5_id','=','warehouse.org5_id')->select('site.*')->where($where)->orderBy('create_at','desc')->paginate($limit);
        return $siteList->toArray();
    }
    
    //点位更新
    public static function updateSite($where,$set){
        return DB::table('site')->where($where)->update($set);
    }
    
    //获取设备列表
    public static function getDeviceList($where,$limit){
        
        return DB::table('device')->join('site', 'device.node_id', '=', 'site.node_id')->select('device.*','site.node_name','site.tmall_node_id as site_tmall_node_id','site.store_id as site_store_id')->where($where)->orderBy('create_at','desc')->paginate($limit)->toArray();
    }
    
    //设备更新
    public static function updateDevice($where,$set){
          return DB::table('device')->where($where)->update($set);
    }
    
    //获取任务列表
    public static function getTaskList($where,$limit){
        return DB::table('task_to_product')
               ->select('*')->where($where)->orderBy('update_at','id')
                                           ->orderBy('activity_id','desc')->paginate($limit);
    }
    
    //采购列表
    public static function getProcurementList($where,$limit){
        return DB::table('task_to_product as a')
                ->leftJoin('product_to_warehouse as b','a.id','=','b.parent_id')
                ->select('a.*',DB::raw('count(a.id) as barnCount, sum(b.amount) as totalQuantity,sum(b.arrival_volume) as totalArrivalVolume'))
                ->where($where)->groupBy('a.id')->orderBy('activity_id','asc')->paginate($limit);
    }
    
    //获取单个任务信息
    public static function getTaskOne($id){
        return DB::table('task_to_product')->select('*')->where('id',$id)->first();
    }
    
    //获取任务分配仓库
    public static function getTaskToWarehouse($taskId){
        return DB::table('product_to_warehouse')
                    ->select('*')
                    ->where('parent_id',$taskId)
                    ->get();
    }
    
    //获取任务分配点位
    public static function getSitefWId($wIds){
        return DB::table('site')
                    ->select('node_name','barn_id') 
                    ->whereIn('barn_id',$wIds)
                    ->get();
    }
    //仓库分配 批量更新
    public static function updateTaskToWarehouse($data){
        return updateBatch($data,'laravel_task_to_warehouse');
    }
    //任务更新
    public static function updateTaskToProductOne($where,$set){
        return DB::table('task_to_product')->where($where)->update($set);
    }
    
    //获取单个商品下的仓库分配   采购详情
    public static function getProductToWarehouse($where){
        return DB::table('product_to_warehouse')
                    ->leftJoin('purchase_detail','product_to_warehouse.id','=','purchase_detail.parent_id')
                    ->select('product_to_warehouse.barn_id','product_to_warehouse.amount','product_to_warehouse.barn_name','purchase_detail.*')
                    ->where($where)->get();
    }
    //商品详情
    public static function getProductToDevice($parent_id)
    {
        $where =[];
        $where[] = ['parent_id','=',$parent_id];
        $where[] = ['delete_mark','=',0];
        return DB::table('product_to_device')->select('*')->where($where)->get();
    }
    //商品配置
    public static function insertProductToDevice($data)
    {
        return DB::table('product_to_device')->insert($data);
    }
    
    public static function updateProductToDevice($where,$set)
    {
        return DB::table('product_to_device')->where($where)->update($set);
    }
    
    //价格详情
    public static function getProductToPrice($parent_id)
    {
        $where =[];
        $where[] = ['parent_id','=',$parent_id];
        $where[] = ['delete_mark','=',0];
        return DB::table('product_to_price')->select('*')->where($where)->get();
    }
    
    //价格配置
    public static function insertProductToPrice($data)
    {
        return DB::table('product_to_price')->insert($data);
    }
    
    //更新价格选项
    public static function updateProductToPrice($where,$set)
    {
        return DB::table('product_to_price')->where($where)->update($set);
    }
    
    //获取库存  数据看板
    public static  function getinventoryList($where,$limit)
    {
        return DB::table('inventory')->select('*')->where($where)->orderBy('create_at','desc')->paginate($limit)->toArray();
    }
    
    public static function getAllinventoryList($where)
    {
        return DB::table('inventory')->select('*')->where($where)->get();
    }
    
    //获取单个商品可配置的设备
    public static function getOneProDevice($id)
    {
        return DB::table('task_to_product')
                    ->join('task_to_shop','task_to_shop.activity_id','=','task_to_product.activity_id')
                    ->join('site','task_to_shop.shop_id','=','site.tmall_node_id')
                    ->join('device','device.node_id','=','site.node_id')->select('device.vm_code')->where('task_to_product.id','=',$id)->get();
    }
   
    
    public static function updateBatch($multipleData = [],$tableName)
    {
        try {
            if (empty($multipleData)) {
                throw new \Exception("数据不能为空");
            }
            $firstRow  = current($multipleData);
            
            $updateColumn = array_keys($firstRow);
            // 默认以id为条件更新，如果没有ID则以第一个字段为条件
            $referenceColumn = isset($firstRow['id']) ? 'id' : current($updateColumn);
            unset($updateColumn[0]);
            // 拼接sql语句
            $updateSql = "UPDATE " . $tableName . " SET ";
            $sets      = [];
            $bindings  = [];
            foreach ($updateColumn as $uColumn) {
                $setSql = "`" . $uColumn . "` = CASE ";
                foreach ($multipleData as $data) {
                    $setSql .= "WHEN `" . $referenceColumn . "` = ? THEN ? ";
                    $bindings[] = $data[$referenceColumn];
                    $bindings[] = $data[$uColumn];
                }
                $setSql .= "ELSE `" . $uColumn . "` END ";
                $sets[] = $setSql;
                }
                $updateSql .= implode(', ', $sets);
                $whereIn   = collect($multipleData)->pluck($referenceColumn)->values()->all();
                $bindings  = array_merge($bindings, $whereIn);
                $whereIn   = rtrim(str_repeat('?,', count($whereIn)), ',');
                $updateSql = rtrim($updateSql, ", ") . " WHERE `" . $referenceColumn . "` IN (" . $whereIn . ")";
                // 传入预处理sql语句和对应绑定数据
                return DB::update($updateSql, $bindings);
            } catch (\Exception $e) {
                return false;
            }
          }
            
    
    
}
