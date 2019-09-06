<?php
namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminCommonController;
use App\Admin\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use DB;
use Excel;

use App\Admin\Models\ResourceManagement;

class ResourceManagementController extends AdminCommonController
{
    
     public function __construct(){
// 		parent::__construct();
        DB::connection()->enableQueryLog();
        $this->fromtime = microtime(true);
        $this->requestId = uniqid(str_replace('.','',self::getServerIP()).'s'.getmypid().'p');
    } 
    
    public function  index(){
        header("Access-Control-Allow-Origin:*");
        $this->stream =app('request')->get('json');
        self::log('ResourceManagement'.date("Ymd"),'requestId:'.$this->requestId,$this->stream);
        $this->stream = json_decode($this->stream,true);
        $limit = empty(app('request')->get('limit')) ? 20 :app('request')->get('limit');
        if (!empty($this->stream['request']['function']))
        {
            switch ($this->stream['request']['function']) {
               case '1001'://获取仓库列表
                   $where =[];
                   if (!empty($this->stream['request']['type']))
                   {
                       if ($this->stream['request']['type'] ==1)
                       {
                           $where[] = ['delete_mark','=',0];
                       }else if ($this->stream['request']['type'] ==2)
                       {
                           $where[] = ['delete_mark','=',1];
                       }else if ($this->stream['request']['type'] ==3)
                       {
                           $where[] = ['binding_barn_id','!=',0];
                       }else if ($this->stream['request']['type'] ==4)
                       {
                           $where[] = ['binding_barn_id','=',0];
                       }
                   }
                   if (!empty($this->stream['request']['barn_id']) && !is_numeric($this->stream['request']['barn_id']))
                   {
                      $where[] = ['name','like',$this->stream['request']['barn_id']];
                   }else if(!empty($this->stream['request']['barn_id']) && is_numeric($this->stream['request']['barn_id']))
                   {
                       $where[] = ['org5_id','=',$this->stream['request']['barn_id']];
                   }
                   $getWarehouseList = ResourceManagement::getWarehouseList($where,$limit);
                   $warehouseList =[];
                   $areaList = $this->getAreaCode();
                   foreach ($getWarehouseList['data'] as $k=>$v)
                   {
                       $v->province = empty($areaList[$v->province])?0:$areaList[$v->province];
                       $v->city = empty($areaList[$v->city])?0:$areaList[$v->city];
                       $v->zone = empty($areaList[$v->zone])?0:$areaList[$v->zone];
                       if($v->city&&!$v->zone&&$areaList[$v->city->parent_id]->parent_id>1){  // 修复市取值为区域bug
                           $v->zone = $v->city;
                           $v->city = $areaList[$v->city->parent_id];
                       }


                       $warehouseList[] = $v;
                   }
                   if (!empty($this->stream['request']['excel']))
                   {
                       $cellData[] = array('编号','仓名称','仓ID','省','市','区','详细地址','联系⼈名称','联系⼈电话','天猫仓ID');
                       foreach ($warehouseList as $v)
                       {
                           if (empty($v->province->name))
                           {
                               $province = '';
                           }else
                           {
                               $province = $v->province->name ;
                           }
                           if (empty($v->city->name))
                           {
                               $city =  '';
                           }else
                           {
                               $city = $v->city->name;
                           }
                           if (empty($v->zone->name))
                           {
                               $zone = '';
                           }else
                           {
                               $zone = $v->zone->name;
                           }
                           $info = array($v->id,$v->name,$v->org5_id,$province,$city,$zone,$v->address,$v->contact_name,$v->contact_phone,$v->binding_barn_id);
                           $cellData[] = $info;
                       }
                       $this->export($cellData,'仓库列表');
                   }
                   $this->returnSuccess('成功',$getWarehouseList);
               break; 
               
             case '1002'://创建仓库
                 if (empty($this->stream['request']['contact_name']) || empty($this->stream['request']['address']) || empty($this->stream['request']['contact_phone']) || empty($this->stream['request']['province']) || empty($this->stream['request']['city']) || empty($this->stream['request']['zone']) ||empty($this->stream['request']['barn_id']))
                 {
                     return $this->returnError('参数有误！');
                 }
                  $url =env('PF_URL','http://huygens.dev.uboxol.com').'/tmall/warehouse/create';
                  $getAreaCode =$this->getAreaCode();
                  $province = $getAreaCode[$this->stream['request']['province']];
                  $city  = $getAreaCode[$this->stream['request']['city']];
                  $zone = $getAreaCode[$this->stream['request']['zone']];

                 if($city&&!$zone&&$getAreaCode[$city->parent_id]->parent_id>1){  // 修复市取值为区域bug
                     $zone = $city;
                     $city = $getAreaCode[$city->parent_id];
                 }

                /* if(!$zone&&$city){
                  if($city['parent_id']>1){  // 直辖市类型的
                      $zone = $city;
                  }
                 }*/

                  $sendData =[
                      'sn'=>time(),
                      'province'=>$province->code,
                      'city'=>$city->code,
                      'zone'=>$zone->code,
                      'address'=>$this->stream['request']['address'],
                      'warehouseName'=>$this->stream['request']['barn_id'],
                      'entityId'=>558,
                      'contractName'=>$this->stream['request']['contact_name'],
                      'contractPhone'=>$this->stream['request']['contact_phone']
                  ];
                  $sign = $this->createSign($sendData);
                  $sendData['sign'] = $sign;
                  self::log('ResourceManagement'.date("Ymd"),'requestIdApi:'.$this->requestId,json_encode($sendData));
                  
                  $res = $this->curlRequest($url,'1',json_encode($sendData));
                  self::log('ResourceManagement'.date("Ymd"),'requestIdApi:'.$this->requestId,$res);
                  $res = json_decode($res,true);
                  if ($res['code'] != 200)
                  {
                      return $this->returnError($res['msg'],$res);
                  }
                  $sql ='update warehouse set contact_name = ? ,address = ? ,contact_phone = ? ,province = ? , city = ? , zone = ? ,binding_barn_id =? where id=? ';
                  $set = array(
                      $this->stream['request']['contact_name'],
                      $this->stream['request']['address'],
                      $this->stream['request']['contact_phone'],
                      $this->stream['request']['province'],
                      $this->stream['request']['city'],
                      $this->stream['request']['zone'],
                      $res['data']['tmallWarehouseId'],
                      $this->stream['request']['id']
                  );
                  ResourceManagement::updateWareHouse($sql,$set);
                  $this->returnSuccess('成功');
                  break;
              case "1003"://城市信息
                  $where = $area = [];
                  if (!empty($this->stream['request']['parent_id']) && $this->stream['request']['parent_id'] !=1)
                  {
                      $where[] = ['parent_id','=',$this->stream['request']['parent_id']];
                  }else
                  {
                      $where[] = ['parent_id','=',1];
                  }
                  $areaList =  $areaLists =[];
                  $getArea = ResourceManagement::getArea($where);
                  $this->returnSuccess('成功',$getArea);
                  break;
              case "1004"://获取天猫商场列表
                      $where =[];
                      if (!empty($this->stream['request']['type']))
                      {
                          if ($this->stream['request']['type'] ==1)
                          {
                              $where[] = ['delete_mark','=',0];
                          }else if ($this->stream['request']['type'] ==2)
                          {
                              $where[] = ['delete_mark','=',1];
                          }else if ($this->stream['request']['type'] ==3)
                          {
                              $where[] = ['ub_barn_id','!=',0];
                          }else if ($this->stream['request']['type'] ==4)
                          {
                              $where[] = ['ub_barn_id','=',0];
                          }
                      }
                      if (!empty($this->stream['request']['market_id']) && !is_numeric($this->stream['request']['market_id']))
                      {
                          $where[] = ['market_name','like',$this->stream['request']['market_id']];
                      }else if(!empty($this->stream['request']['market_id']) && is_numeric($this->stream['request']['market_id']))
                      {
                          $where[] = ['market_id','=',$this->stream['request']['market_id']];
                      }
                      $getMallLists = [];
                      $getMallList = ResourceManagement::getMallList($where,$limit);
                      $areaList = $this->getAreaCode();
                      foreach ($getMallList['data'] as $k=>$v)
                      {
                          $v->province = empty($areaList[$v->province])?0:$areaList[$v->province];
                          $v->city = empty($areaList[$v->city])?0:$areaList[$v->city];
                          $v->zone = empty($areaList[$v->zone])?0:$areaList[$v->zone];

                          if($v->city&&!$v->zone&&$areaList[$v->city->parent_id]->parent_id>1){  // 修复市取值为区域bug
                              $v->zone = $v->city;
                              $v->city = $areaList[$v->city->parent_id];
                          }


                          $getMallLists[] = $v;
                      }
                      $getAllWarehouse  = ResourceManagement::getAllWarehouse();
                      foreach ($getAllWarehouse as $val)
                      {
                          $list[$val->org5_id] = $val->name; 
                      }
                      $data['total'] = $getMallList['total'];
                      $data['list'] = $list;
                      $data['MallList'] = $getMallLists;
                      $data['status'] = 0;
                      $this->returnSuccess('成功',$data);
                      break;
                    case "1005"://商场绑定
                        if (empty($this->stream['request']['id']) || empty($this->stream['request']['ub_barn_id']) || empty($this->stream['request']['ub_barn_name']))
                        {
                            $this->returnError('失败');
                        }
                        if (!empty($this->stream['request']['type'])  && $this->stream['request']['type']==1)
                        {
                            $set = [
                                'ub_barn_id'=>$this->stream['request']['ub_barn_id'],
                                'ub_barn_name'=>$this->stream['request']['ub_barn_name']
                            ];
                        }else if (!empty($this->stream['request']['type']) && $this->stream['request']['type']==2)
                        {
                            $set = [
                                'ub_barn_id'=>0,
                                'ub_barn_name'=>''
                            ];
                        }
                        $where[] = ['id',$this->stream['request']['id']];
                        $res = ResourceManagement::updateTmallMarket($where,$set);
                        if ($res)
                        {
                            $this->returnSuccess('成功');
                        }else
                        {
                            $this->returnError('更新失败~~');
                        }
                        break;
                        
                    case "1006"://点位列表
                        $where =[];
                        if (!empty($this->stream['request']['type']))
                        {
                            if ($this->stream['request']['type'] ==1)
                            {
                                $where[] = ['site.delete_mark','=',0];
                            }else if ($this->stream['request']['type'] ==2)
                            {
                                $where[] = ['site.delete_mark','=',1];
                            }else if ($this->stream['request']['type'] ==3)
                            {
                                $where[] = ['site.tmall_node_id','!=',0];
                            }else if ($this->stream['request']['type'] ==4)
                            {
                                $where[] = ['site.tmall_node_id','=',0];
                            }
                        }
                        if (!empty($this->stream['request']['node_id']) && !is_numeric($this->stream['request']['node_id']))
                        {
                            $where[] = ['site.node_name','like',$this->stream['request']['node_id']];
                        }else if(!empty($this->stream['request']['node_id']) && is_numeric($this->stream['request']['node_id']))
                        {
                            $where[] = ['site.node_id','=',$this->stream['request']['node_id']];
                        }
                        $getSiteList = ResourceManagement::getSiteList($where,$limit);
                        
                        $data['node_type'] = array(1=>'商场',2=>'⼩区',3=>'影院',4=>'校园',5=>'写字楼',6=>'机场',7=>'地铁站',8=>'公共⼴场',9=>'专业市场',10=>'超市',11=>'医院',12=>'公园',13=>'餐厅',14=>'企业',15=>'其他');                        
                        $data['location'] = array(1=>'出⼊⼝',2=>'中庭',3=>'连廊',4=>'扶梯⼝',5=>'厕所旁',6=>'物业管理处',7=>'服务中⼼',8=>'边庭',9=>'商铺',10=>'电梯⼝',11=>'中⼼⼴场',12=>'花园',13=>'下沉式⼴场',14=>'售票⼤厅',15=>'外⼴场',16=>'⼤堂',17=>'停⻋位处',18=>'公告栏旁边',19=>'收银台旁边',20=>'未知');
                        $data['SiteList'] = $getSiteList;
                        
                        if (!empty($this->stream['request']['excel']))
                        {
                            $cellData[] = array('编号','点位名称','场地类型','位置','业务归属','仓ID','天猫点位ID','store ID');
                            foreach ($getSiteList['data'] as $v)
                            {
                                $info = array($v->id,$v->node_name,$v->node_type,$v->location,$v->belong_to,$v->org5_id,$v->tmall_node_id,$v->store_id);
                                $cellData[] = $info;
                            }
                            $this->export($cellData,'点位列表');
                        }
                        
                        $this->returnSuccess('成功',$data);
                        break;
                        
                    case "1007"://创建点位
                        if (empty($this->stream['request']['node_name']) || empty($this->stream['request']['node_type']) || empty($this->stream['request']['location']) || empty($this->stream['request']['id']))
                        {
                            $this->returnError('参数有误！');
                        }
                        //获取天猫点位id待处理
                        $sendData =[
                            'sn'=>time(),
                            'mallId'=>35559,
                            'popupName'=>$this->stream['request']['node_id'],
                            'addressDetail'=>$this->stream['request']['node_address'],
                            'popupType' =>'OTHER',
                            'ownerEntityId'=>558
                        ];
                        $url =env('PF_URL','http://huygens.dev.uboxol.com').'/tmall/shopstore/create';
                        
                        $sign = $this->createSign($sendData);
                        $sendData['sign'] = $sign;
                        self::log('ResourceManagement'.date("Ymd"),'requestIdApi:'.$this->requestId,json_encode($sendData));
                        $res = $this->curlRequest($url,'1',json_encode($sendData));
                        self::log('ResourceManagement'.date("Ymd"),'requestIdApi:'.$this->requestId,$res);
                        $res = json_decode($res,true);
                        if ($res['code'] != 200)
                        {
                            return $this->returnError($res['msg'],$res);
                        }
                        //获取完天猫id更新
                        $set = [
                            'node_name'=>$this->stream['request']['node_name'],
                            'node_type'=>$this->stream['request']['node_type'],
                            'location'=>$this->stream['request']['location'],
                            'tmall_node_id'=>$res['data']['tmallNodeId'],
                            'store_id' =>$res['data']['storeId'],
                        ];
                        $where[] = ['id',$this->stream['request']['id']];
                        $result = ResourceManagement::updateSite($where,$set);
                        $data['tmall_node_id'] = $res['data']['tmallNodeId'];
                        $this->returnSuccess('成功',$data);
                        break;
                        
                    case "1008"://设备列表
                        $where =[];
                        if (!empty($this->stream['request']['type']))
                        {
                            if ($this->stream['request']['type'] ==1)
                            {
                                $where[] = ['device.delete_mark','=',0];
                            }else if ($this->stream['request']['type'] ==2)
                            {
                                $where[] = ['device.delete_mark','=',1];
                            }else if ($this->stream['request']['type'] ==3)
                            {
                                $where[] = ['device.delete_mark','=',3];//未创建
                            }else if ($this->stream['request']['type'] ==4)
                            {
                                $where[] = ['device.delete_mark','=',0];//已创建
                            }
                        }
                        if(!empty($this->stream['request']['vm_code']) && is_numeric($this->stream['request']['vm_code']))
                        {
                            $where[] = ['device.vm_code','=',$this->stream['request']['vm_code']];
                        }
                        $where[] =['site.tmall_node_id','!=',0]; 

                        $getDeviceList = ResourceManagement::getDeviceList($where,$limit);
                        if (!empty($this->stream['request']['excel']))
                        {
                            $cellData[] = array('编号','点位名称','友宝点位id','友宝机器id','storeID','天猫devicecode','激活码');
                            foreach ($getDeviceList['data'] as $v)
                            {
                                $info = array($v->id,$v->node_name,$v->node_id,$v->vm_code,$v->site_store_id,$v->device_code,$v->activation_code);
                                $cellData[] = $info;
                            }
                            $this->export($cellData,'设备列表');
                        }
                        $this->returnSuccess('成功',$getDeviceList);
                        break;
                        
                    case "1009"://创建设备
                        if (empty($this->stream['request']['node_id']) || empty($this->stream['request']['vm_code']) || empty($this->stream['request']['node_name']) || empty($this->stream['request']['tmall_node_id']) || empty($this->stream['request']['vm_type']) || empty($this->stream['request']['store_id']) || empty($this->stream['request']['id']))
                        {
                            $this->returnError('参数有误！');
                        }
                        //获取天猫点位device_code待处理
                        $sendData =[
                            'sn'=>time(),
                            'deviceName'=>$this->stream['request']['vm_code'],
                            'deviceType'=>1,//到时候改成2 
                            'storeId'=>$this->stream['request']['store_id'],
                            'ownerId'=>558
                        ];
                        $url =env('PF_URL','http://huygens.dev.uboxol.com').'/tmall/device/create';
                        $sign = $this->createSign($sendData);
                        $sendData['sign'] = $sign;
                        self::log('ResourceManagement'.date("Ymd"),'requestIdApi:'.$this->requestId,json_encode($sendData));
                        $res = $this->curlRequest($url,'1',json_encode($sendData));
                        self::log('ResourceManagement'.date("Ymd"),'requestIdApi:'.$this->requestId,$res);
                        $res = json_decode($res,true);
                        if ($res['code'] != 200)
                        {
                            return $this->returnError($res['msg'],$res);
                        }
                        //获取完天猫device_code更新
                        $set = [
                            'tmall_node_id'=>$this->stream['request']['tmall_node_id'],
                            'store_id'=>$this->stream['request']['store_id'],
                            'device_id'=>$res['data']['deviceId'],
                            'device_code'=>$res['data']['deviceCode'],
                            'activation_code'=>$res['data']['activeCode'],
                            'delete_mark' =>3,
                        ];
                        $where[] = ['id',$this->stream['request']['id']];
                        $res = ResourceManagement::updateDevice($where,$set);
                        $this->returnSuccess('成功');
                        break;
                        
                    case "1010"://任务列表
                        $where =[];
                        if(!empty($this->stream['request']['item_name']) && !is_numeric($this->stream['request']['item_name']))
                        {
                            $where[] = ['product_name','like',$this->stream['request']['item_name']];
                        }
                        if (!empty($this->stream['request']['activity_time']))
                        {
                            $where[] =['activity_start_time','>=',$this->stream['request']['activity_time']];
                        }
                        if (!empty($this->stream['request']['end_time']))
                        {
                            $where[] =['activity_end_time','<=',$this->stream['request']['end_time']];
                        }
                        if (!empty($this->stream['request']['finish_flag']))
                        {
                            $where[] =['finish_flag','=',4];
                        }else{
                            $where[] =['finish_flag','!=',4];
                        }
                        $getTaskList = ResourceManagement::getTaskList($where,$limit);
//                         foreach ($getTaskList as $key=>$val)
//                         {
//                             $val->img_url =$val->img_url;
//                         }
                        $this->returnSuccess('成功',$getTaskList);
                        break;
                    case "1011"://仓库分配详情
                        if (empty($this->stream['request']['id']))
                        {
                            $this->returnError('参数有误！');
                        }
                        $getTaskToWarehouse =  ResourceManagement::getTaskToWarehouse($this->stream['request']['id']);
                        foreach ($getTaskToWarehouse as &$v)
                        {
                           if (empty($v->node_names))
                           {
                               $v->node_num = 0;
                           }else
                           {
                               $nodeArr = explode(',',$v->node_names);
                               $v->node_num = count($nodeArr);
                           }
                        }
                        $data['getTaskToWarehouse'] = $getTaskToWarehouse;
                        $modify = 0;
                        if (date("H:i:s") >='17:00:00')
                        {
                            $modify = 1;
                        }
                        $data['modify'] = $modify;
                        $this->returnSuccess('成功',$data);
                        break;
                    case "1012"://仓库分配
                        if (empty($this->stream['request']['lists']))
                        {
                            $this->returnError('参数有误！');
                        }
                        if (date("H:i:s") >='17:00:00')
                        {
                            $this->returnError('分配已确定数量不能修改咯~~');
                        }
                        
                        $data = $purchase_detail = [];
                        foreach ($this->stream['request']['lists'] as $val)
                        {
                            $data[] = [
                                'id'=>$val['id'],
                                'amount'=>$val['amount']
                            ];
                            $purchase_detail[] = [
                                'parent_id'=>$val['id'],
                                'purchase_amount'=>$val['amount']
                            ];
                        }
                        $updateTaskToWarehouse = ResourceManagement::updateBatch($data,'product_to_warehouse');
                        ResourceManagement::updateBatch($purchase_detail,'purchase_detail');
                        if ($updateTaskToWarehouse)
                        {
                            if (isset($this->stream['request']['finish_flag']) && $this->stream['request']['finish_flag'] ==0)
                            {
                                $where[]= ['id','=',$this->stream['request']['id']];
                                $where[]= ['finish_flag','=',0];
                                $set =['finish_flag'=>1];
                                $updateTaskToProductOne = ResourceManagement::updateTaskToProductOne($where,$set);
                            }
                            $this->returnSuccess('成功',$data);
                        }
                        $this->returnError('更新失败！');
                        break;
                    case "1013"://商品配置
                        if (empty($this->stream['request']['id']) || empty($this->stream['request']['product_id']) || empty($this->stream['request']['img_url']) || empty($this->stream['request']['list']) || empty($this->stream['request']['item_id']) ||empty($this->stream['request']['activity_id']))
                        {
                            $this->returnError('参数有误！');
                        }

                        // 设备存在性校验

                        $getOneProDevice =  ResourceManagement::getOneProDevice($this->stream['request']['id']);
                        foreach($this->stream['request']['list'] as $k=>$v){
                            $devices = empty($v['vm_codes']) ?$this->returnError('第'.($k+1).'列派发机器列表为空错误！') :$v['vm_codes'];
                            $devices_list = explode(',',$devices);
                            if (!empty($getOneProDevice))
                            {
                                $list = array();
                                foreach ($getOneProDevice as $val)
                                {
                                    $list[] = $val->vm_code;
                                }
                                $intersectLIst = array_intersect($devices_list,$list);//取交集;
                                $diffLIst = array_diff($devices_list,$intersectLIst);
                                if($diffLIst){
                                    $this->returnError('第'.($k+1).'列派发机器非法！【'.implode(',',$diffLIst).'】不存在');
                                }
                            }
                        }



                        foreach ($this->stream['request']['list'] as $val)
                        {
                            $data[] = [
                                'parent_id'=>$this->stream['request']['id'],
                                'added_time'=>$val['added_time'],
                                'dismounted_time'=>$val['dismounted_time'],
                                'vm_codes'=>$val['vm_codes'],
                                'activity_id'=>$this->stream['request']['activity_id'],
                                'item_id'=>$this->stream['request']['item_id'],
                            ];
                        }
                        $whereDevice = [];
                        $whereDevice[]  = ['parent_id','=',$this->stream['request']['id']];
                        $whereDevice[]  = ['delete_mark','=',0];
                        $updateProductToDevice = ResourceManagement::updateProductToDevice($whereDevice,['delete_mark'=>1,'update_at'=>date("Y-m-d H:i:s")]);
                        $insertProductToDevice = ResourceManagement::insertProductToDevice($data);
                        $where = [];
                        if ($insertProductToDevice)
                        {
                            $where[]= ['id','=',$this->stream['request']['id']];
                            if (!empty($this->stream['request']['finish_flag']) && $this->stream['request']['finish_flag'] >1)
                            {
                                $set =['product_id'=>$this->stream['request']['product_id'],'img_url'=>$this->stream['request']['img_url']];
                            }else
                            {
                                $set =['finish_flag'=>2,'product_id'=>$this->stream['request']['product_id'],'img_url'=>$this->stream['request']['img_url']];
                            }
                            $updateTaskToProductOne = ResourceManagement::updateTaskToProductOne($where,$set);
                            $this->returnSuccess('成功');
                        }else
                        {
                            $this->returnError('配置失败');
                        }
                        break;
                        
                    case "1014": //价格配置
                        if (empty($this->stream['request']['id']) || empty($this->stream['request']['list']) || empty($this->stream['request']['activity_id']) || empty($this->stream['request']['item_id']))
                        {
                            $this->returnError('参数有误！');
                        }
                        $getOneProDevice =  ResourceManagement::getOneProDevice($this->stream['request']['id']);
                        foreach($this->stream['request']['list'] as $k=>$v){
                            $devices = empty($v['vm_codes']) ?$this->returnError('第'.($k+1).'列派发机器列表为空错误！') :$v['vm_codes'];
                            $devices_list = explode(',',$devices);
                            if (!empty($getOneProDevice))
                            {
                                $list = array();
                                foreach ($getOneProDevice as $val)
                                {
                                    $list[] = $val->vm_code;
                                }
                                $intersectLIst = array_intersect($devices_list,$list);//取交集;
                                $diffLIst = array_diff($devices_list,$intersectLIst);
                                if($diffLIst){
                                    $this->returnError('第'.($k+1).'列派发机器非法！【'.implode(',',$diffLIst).'】不存在');
                                }
                            }
                        }
                        
                        foreach ($this->stream['request']['list'] as $val)
                        {
                            $data[] = [
                                'price'=>$val['price'],
                                'vm_codes'=>$val['vm_codes'],
                                'activity_id'=>$this->stream['request']['activity_id'],
                                'parent_id'=>$this->stream['request']['id'],
                                'item_id'=>$this->stream['request']['item_id']
                            ];
                        }
                        $wherePrice = [];
                        $wherePrice[]  = ['parent_id','=',$this->stream['request']['id']];
                        $set =['delete_mark'=>1,'update_at'=>date("Y-m-d H:i:s")];
                        $updateProductToPrice = ResourceManagement::updateProductToPrice($wherePrice,$set);
                        $insertProductToPrice = ResourceManagement::insertProductToPrice($data);
                        $where = [];
                        if ($insertProductToPrice)
                        {
                            if (!empty($this->stream['request']['finish_flag']) && $this->stream['request']['finish_flag'] ==2)
                            {
                                $where[]= ['id','=',$this->stream['request']['id']];
                                $set =['finish_flag'=>4];
                                $updateTaskToProductOne = ResourceManagement::updateTaskToProductOne($where,$set);
                            }
                            $this->returnSuccess('成功');
                        }else
                        {
                            $this->returnError('配置失败');
                        }
                        break;
                    case "1015": //商品采购列表
                        $where =[];
                        if(!empty($this->stream['request']['product_name']))
                        {
                            $where[] = ['a.product_name','like',$this->stream['request']['product_name']];
                        }
                        if (!empty($this->stream['request']['activity_start_time']))
                        {
                            $where[] =['a.activity_start_time','>=',$this->stream['request']['activity_start_time']];
                        }
                        if (!empty($this->stream['request']['activity_end_time']))
                        {
                            $where[] =['a.activity_end_time','<=',$this->stream['request']['activity_end_time']];
                        }
                        $where[] = ['a.finish_flag','=',4];
                        $getDeviceList = ResourceManagement::getProcurementList($where,$limit);
                        $this->returnSuccess('成功',$getDeviceList);
                        break;
                        
                    case "1016": //商品采购详情
                        $id = empty($this->stream['request']['id']) ?  $this->returnError('参数有误！') :$this->stream['request']['id'];
                        //获取商品信息
                        $getTaskInfo = ResourceManagement::getTaskOne($id);
                        if (empty($getTaskInfo))
                        {
                            $this->returnError('商品信息有误！');
                        }
                        //获取采购详情
                        if (!empty($this->stream['request']['barn_id']) && is_numeric($this->stream['request']['barn_id']))
                        {
                            $where[] = ['product_to_warehouse.barn_id','=',$this->stream['request']['barn_id']];
                        }else if (!empty($this->stream['request']['barn_id']) && !is_numeric($this->stream['request']['barn_id']))
                        {
                            $where[] = ['product_to_warehouse.barn_name','like','%'.$this->stream['request']['barn_id'].'%'];
                        }
                        $where[] = ['product_to_warehouse.parent_id','=',$id];
                        
                        $getPurchaseDetail = ResourceManagement::getProductToWarehouse($where);
                        $list = [];
                        if (!empty($getPurchaseDetail))
                        {
                            $weekArr = array('0'=>'星期日','1'=>'星期一','2'=>'星期二','3'=>'星期三','4'=>'星期四','5'=>'星期五','6'=>'星期六');
                            $buyTotal =$getTotal =0 ;
                            $barnIdArr = [];
                            foreach ($getPurchaseDetail as $val)
                            {
                                if (empty($list[$val->barn_id]['buyTotal']))
                                {
                                    $list[$val->barn_id]['buyTotal'] =0;
                                }
                                if (empty($list[$val->barn_id]['getTotal']))
                                {
                                    $list[$val->barn_id]['getTotal'] =0;
                                }
                                $day = date('w',strtotime($val->purchase_time));
                                $val->purchase_time =date("m-d",strtotime($val->purchase_time)).$weekArr[$day];
                                $list[$val->barn_id]['list'][] = $val;
                                if (!in_array($val->barn_id,$barnIdArr))
                                {
                                    $barnIdArr[] = $val->barn_id;
                                    $list[$val->barn_id]['buyTotal'] =$val->amount;
                                }
                                $list[$val->barn_id]['getTotal'] +=$val->actual_amount;
                                $list[$val->barn_id]['name'] = $val->barn_name;
                                $list[$val->barn_id]['barn_id'] = $val->barn_id;
                            }
                         }
                        $data['TaskInfo'] = $getTaskInfo;
                        $data['list'] = $list;
                        $this->returnSuccess('成功',$data);
                        break;
                    case "1017": //商品库存表 数据看板
                        $where = $where1 =[];
                        if (!empty($this->stream['request']['product_name']))
                        {
                            $where[] = ['product_name','like','%'.$this->stream['request']['product_name'].'%'];
                        }
                        if (!empty($this->stream['request']['brand']))
                        {
                            $where[] = ['brand','like','%'.$this->stream['request']['brand'].'%'];
                        }
                        if (!empty($this->stream['request']['company_name']))
                        {
                            $where[] = ['company_name','like','%'.$this->stream['request']['company_name'].'%'];
                        }
                        if(!empty($this->stream['request']['start_time']) && !empty($this->stream['request']['end_time']))
                        {
                            $where[] = ['create_at','>=',$this->stream['request']['start_time']];
                            $where[] = ['create_at','<=',$this->stream['request']['end_time']];
                            $where1[] = ['create_at','>=',$this->stream['request']['start_time']];
                            $where1[] = ['create_at','<=',$this->stream['request']['end_time']];
                        }
                        $getinventoryList = ResourceManagement::getinventoryList($where,$limit);
                        $getAllinventoryList = ResourceManagement::getAllinventoryList($where1);
                        $productName =  $brand = $company_name=[];
                        if (!empty($getAllinventoryList)){
                            foreach ($getAllinventoryList as $val)
                            {
                                $productName[] = $val->product_name;
                                $brand[] = $val->brand;
                                $company_name[] = $val->company_name;
                            }
                        }
                        if (!empty($getinventoryList))
                        {
                            if (!empty($this->stream['request']['excel']))
                            {
                                $cellData[] = array('编号','日期','公司名','客户名','商品名','商品id','计量单位','入库数量','出库数量','结存数量');
                                foreach ($getinventoryList['data'] as $v)
                                {
                                    $info = array($v->id,$v->create_at,$v->company_name,$v->brand,$v->product_name,$v->product_id,$v->unit,$v->inbound_num,$v->outbound_num,$v->deposit_num);
                                    $cellData[] = $info;
                                }
                                $this->export($cellData,'数据看板');
                            }
                            
                            $total = $inbound_num = $outbound_num = $deposit_num = $damage_amount= $arrival_volume = $total_quantity = $total_shipments=0;
                            foreach ($getinventoryList['data'] as $val)
                            {
                                $total += 1;// 商品总数量
                                $inbound_num +=$val->inbound_num;//入库数量
                                $outbound_num +=$val->outbound_num;//出库数量
                                $deposit_num +=$val->deposit_num; //结存数量
                                $damage_amount  += $val->damage_amount; //货损量
                                $arrival_volume += $val->arrival_volume; //到货总量
                                $total_quantity += $val->total_quantity; //派发总数量
                                $total_shipments += $val->total_shipments; //发货总量
                            }
                            $totalList = [
                                'total'=>$total,
                                'inbound_num' =>$inbound_num,
                                'outbound_num'=>$outbound_num,
                                'damage_amount'=>$damage_amount,
                                'damage_rate' => empty($outbound_num) ? 0:round($damage_amount/$outbound_num,2)*100 .'%',
                                'deposit_num'=>$deposit_num,
                                'arrival_volume'=>$arrival_volume,
                                'total_quantity'=>$total_quantity,
                                'total_shipments' =>$total_shipments
                            ];
                        }
                        $data['productName'] =array_unique($productName);
                        $data['brand'] =array_unique($brand);
                        $data['company_name'] =array_unique($company_name);
                        $data['totalList'] = $totalList;
                        $data['inventoryList'] = $getinventoryList;
                        
                        $this->returnSuccess('成功',$data);
                        break;
                    case "1018": //获取单个商品信息
                        if (!empty($this->stream['request']['id']))
                        {
                           $getTaskOne =  ResourceManagement::getTaskOne($this->stream['request']['id']);
                           if (empty($getTaskOne->product_id))
                           {
                               $getTaskOne->product_id = '';
                           }
                           $this->returnSuccess('成功',$getTaskOne);
                        }else
                        {
                            $this->returnError('商品信息有误！');
                        }
                        break;
                        
                    case "1019": //商品配置详情
                        if (!empty($this->stream['request']['id']))
                        {
                             $getProductToDevice =  ResourceManagement::getProductToDevice($this->stream['request']['id']);
                             $this->returnSuccess('成功',$getProductToDevice);
                        }else
                        {
                            $this->returnError('商品配置有误！');
                        }
                        break;
                        
                    case "1020": //价格配置详情
                        if (!empty($this->stream['request']['id']))
                        {
                            $getProductToPrice =  ResourceManagement::getProductToPrice($this->stream['request']['id']);
                            $this->returnSuccess('成功',$getProductToPrice);
                        }else
                        {
                            $this->returnError('商品配置有误！');
                        }
                        break;
                        
                    case "1021": //设备配置校验
                        $devices = empty($this->stream['request']['devices']) ?$this->returnError('参数有误！') :$this->stream['request']['devices'];
                        $id      = empty($this->stream['request']['id']) ?$this->returnError('参数有误！') :$this->stream['request']['id'];
                        $getOneProDevice =  ResourceManagement::getOneProDevice($this->stream['request']['id']);
                        $data['useProDevice'] = '';
                        $devices_list = explode(',',$devices);
                        if (!empty($getOneProDevice))
                        {
                            $data['useProDevice'] = $getOneProDevice;
                            $list = array();
                            foreach ($getOneProDevice as $val)
                            {
                                $list[] = $val->vm_code;
                            }
                            $intersectLIst = array_intersect($devices_list,$list);//取交集;
                            $diffLIst = array_diff($devices_list,$intersectLIst);
                            $devicesStr = implode(',', $diffLIst);
                            $data['useProDevice'] = $getOneProDevice;
                            $data['devices'] = $devicesStr;
                            $this->returnSuccess('成功',$data);
                        }
                        $data['devices'] = $devices;
                        $this->returnSuccess('成功',$data);
                        break;
            }
        }
       
    }
    
    public function returnError($msg,$data ='')
    {
        $time =  microtime(true)-$this->fromtime;
        echo  json_encode(array('status'=>200,'msg'=>$msg,'data'=>$data,'time'=>$time));exit;
    }
    
    public function returnSuccess($msg,$data='')
    {
        $time =  microtime(true)-$this->fromtime;
        echo  json_encode(array('status'=>0,'msg'=>$msg,'data'=>$data,'time'=>$time));exit;
    }
    
    public function upload1()
    {
        return view('test_upload');
    }
    
    public function upload()
    {
        header("Access-Control-Allow-Origin:*");
        $id = app('request')->get('id');
        $image = app('request')->file('file')->storeAs('/images',$id.'.png');//上传商品图片
        $url = '/storage/'.$image;
        echo  json_encode(array('status'=>0,'msg'=>'成功','data'=>$url));exit;
    }
    
    /*  curl模拟请求  */
       public function curlRequest($url,$posttype='1',$jsondata=null,$timeout=''){
            $curl	= curl_init($url);
            if(!empty($timeout)){
                curl_setopt($curl, CURLOPT_TIMEOUT,$timeout);
            }
            if($posttype=='1'){		// post
                curl_setopt($curl,CURLOPT_POST,$posttype);
                curl_setopt($curl,CURLOPT_POSTFIELDS,$jsondata);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8','Content-Length: ' . strlen($jsondata)));
            }elseif($posttype=='2'){ //get
                curl_setopt($curl,CURLOPT_POST,$posttype);
                if(!empty($jsondata)){
                    curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($jsondata));
                }
            }elseif($posttype=='3'){ // post
                curl_setopt($curl,CURLOPT_POST,1);
                curl_setopt($curl,CURLOPT_POSTFIELDS,$jsondata);
            }
    
            /* https 相关 */
            if(strpos($url,'https://')!==false){
               curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,FALSE);  // 不验证证书
               curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);	// 不验证域名
            }
           
            curl_setopt($curl, CURLOPT_SSLVERSION, 1);	// 定义ssl版本
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
            $reponse = curl_exec($curl);
            if (curl_errno($curl)) {
                $gettype = gettype($jsondata);
                if($gettype=='array'){
                    $jsondata	= var_export($jsondata,true);
                }elseif($gettype!='string'){
                    $jsondata	= '';
                }
//                 doLog(__LINE__.','.$url.','.curl_error($curl).'请求参数'.$jsondata,'curlRequest');
                throw new Exception(curl_error($curl), 0);
            } else {
                $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if (200 !== $httpStatusCode) {
//                     doLog(__LINE__.','.$url.':'.$httpStatusCode,'curlRequest');
                    throw new Exception($reponse, $httpStatusCode);
                }
            }
            curl_close($curl);
            return $reponse;
    }
    
    public function last_query()
    {
        $sql = DB::getQueryLog();
        return $sql;
    }
    
    public function getAreaCode()
    {
        $areaList = [];
        $getArea = ResourceManagement::getArea([]);
        foreach ($getArea as $val)
        {
            $areaList[$val->code] = $val;
            $areaList[$val->id] = $val;
        }
        return $areaList;
    }
    
    public function createSign($sendData)
    {
        ksort($sendData);
        $signStr = '';
        foreach($sendData as $k=>$v){
            $signStr    .= $k.'='.urldecode($v);
        }
        $sign = md5($signStr.'UEFJRkFfUEFTU1dPUkQ=');
        return $sign;
    }
    
    
    public function export($cellData,$name)
    {
//         $cellData = DB::table('site')->select('org5_id','node_name','node_type')->limit(5)->get()->toArray();
//         $cellData1[] = array('仓id','点位名称','点位类型');
//         foreach ($cellData as $val)
//         {
//             $a = array($val->org5_id,$val->node_name,$val->node_type);
//             $cellData1[] =str_replace('=',' '.'=',$a);
//         }
        Excel::create($name,function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');
        die;
    }

}
