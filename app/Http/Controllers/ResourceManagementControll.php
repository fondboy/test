<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\ResourceManagement;

class ResourceManagementControll extends Controller
{
    public function __construct(){
        DB::connection()->enableQueryLog();
    }
    
    public function  index(Request $request){
        header("Access-Control-Allow-Origin:*");
        $this->stream =$request->input('json');
        $this->stream = json_decode($this->stream,true);
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
                           $where[] = ['barn_id','!=',0];
                       }else if ($this->stream['request']['type'] ==4)
                       {
                           $where[] = ['barn_id','=',0];
                       }
                   }
                   if (!empty($this->stream['request']['barn_id']) && !is_numeric($this->stream['request']['barn_id']))
                   {
                      $where[] = ['name','like',$this->stream['request']['barn_id']];
                   }else if(!empty($this->stream['request']['barn_id']) && is_numeric($this->stream['request']['barn_id']))
                   {
                       $where[] = ['barn_id','=',$this->stream['request']['barn_id']];
                   }
                   $limit = empty($this->stream['request']['limit']) ? 20:$this->stream['request']['limit'];
                   $getWarehouseList = ResourceManagement::getWarehouseList($where,$limit);
                   $warehouseList =[];
                   $areaList = $this->getAreaCode();
                   foreach ($getWarehouseList as $k=>$v)
                   {
                       $v->province = empty($areaList[$v->province])?0:$areaList[$v->province];
                       $v->city = empty($areaList[$v->city])?0:$areaList[$v->city];
                       $v->zone = empty($areaList[$v->zone])?0:$areaList[$v->zone];
                       $warehouseList[] = $v;
                   }
                   $this->returnSuccess('成功',$getWarehouseList);
               break; 
               
             case '1002'://创建仓库
                 if (empty($this->stream['request']['contact_name']) || empty($this->stream['request']['address']) || empty($this->stream['request']['contact_phone']) || empty($this->stream['request']['province']) || empty($this->stream['request']['city']) || empty($this->stream['request']['zone']) || empty($this->stream['request']['name']) ||empty($this->stream['request']['barn_id']))
                 {
                     return $this->returnError('参数有误！');
                 }
                  $url = 'http://huygens.dev.uboxol.com/tmall/warehouse/create';
                  $sendData =[
                      'sn'=>time(),
                      'province'=>$this->stream['request']['province'],
                      'city'=>$this->stream['request']['city'],
                      'zone'=>$this->stream['request']['zone'],
                      'address'=>$this->stream['request']['address'],
                      'warehouseName'=>$this->stream['request']['barn_id'],
                      'entityId'=>558,
                      'contractName'=>$this->stream['request']['contact_name'],
                      'contractPhone'=>$this->stream['request']['contact_phone']
                  ];
                  $sign = $this->createSign($sendData);
                  $sendData['sign'] = $sign;
                  $res = $this->curlRequest($url,'1',json_encode($sendData));
                  $res = json_decode($res,true);
                  if ($res['code'] != 200)
                  {
                      return $this->returnError('创建失败！');
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
                  $where = [];
                  if (!empty($this->stream['request']['parent_id']) && $this->stream['request']['parent_id'] !=1)
                  {
                      $where[] = ['parent_id',$this->stream['request']['parent_id']['id']];
                  }else
                  {
                      $where[] = ['parent_id',1];
                  }
                  $getArea = ResourceManagement::getArea($where);
                  foreach ($getArea as $val)
                  {
                      $area[$val->code] = $val;
                  }
                  $this->returnSuccess('成功',$area);
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
                      $limit = empty($this->stream['request']['limit']) ? 20:$this->stream['request']['limit'];
                      $getMallLists = [];
                      $getMallList = ResourceManagement::getMallList($where,$limit);
                      $areaList = $this->getAreaCode();
                      foreach ($getMallList['data'] as $k=>$v)
                      {
                          $v->province = empty($areaList[$v->province])?0:$areaList[$v->province];
                          $v->city = empty($areaList[$v->city])?0:$areaList[$v->city];
                          $v->zone = empty($areaList[$v->zone])?0:$areaList[$v->zone];
                          $getMallLists[] = $v;
                      }
                      $getAllWarehouse  = ResourceManagement::getAllWarehouse();
                      foreach ($getAllWarehouse as $val)
                      {
                          $list[$val->barn_id] = $val->name; 
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
                                $where[] = ['delete_mark','=',0];
                            }else if ($this->stream['request']['type'] ==2)
                            {
                                $where[] = ['delete_mark','=',1];
                            }else if ($this->stream['request']['type'] ==3)
                            {
                                $where[] = ['tmall_node_id','!=',0];
                            }else if ($this->stream['request']['type'] ==4)
                            {
                                $where[] = ['tmall_node_id','=',0];
                            }
                        }
                        if (!empty($this->stream['request']['node_id']) && !is_numeric($this->stream['request']['node_id']))
                        {
                            $where[] = ['node_name','like',$this->stream['request']['node_id']];
                        }else if(!empty($this->stream['request']['node_id']) && is_numeric($this->stream['request']['node_id']))
                        {
                            $where[] = ['node_id','=',$this->stream['request']['node_id']];
                        }
                        $limit = empty($this->stream['request']['limit']) ? 20:$this->stream['request']['limit'];
                        $getSiteList = ResourceManagement::getSiteList($where,$limit);
                        $getAllMall = ResourceManagement::getAllMall();
                        $allMall = array();
                        if (!empty($getAllMall))
                        {
                            foreach ($getAllMall as $val)
                            {
                                $allMall[$val->ub_barn_id] = $val;
                            }
                        }
                        $getSiteLists = [];
                        foreach ($getSiteList['data'] as $k=>$v)
                        {
                            if (!empty($allMall[$v->barn_id]))
                            {
                                $getSiteLists[$k]['siteInfo'] =$v;
                                $getSiteLists[$k]['mall'][] =$allMall[$v->barn_id];
                            }
                        }
                        $data['node_type'] = array(0=>'场地类型',1=>'商场',1=>'⼩区',2=>'影院',3=>'校园',4=>'写字楼',5=>'机场',6=>'地铁站',7=>'公共⼴场',8=>'专业市场',9=>'超市',10=>'医院',11=>'公园',12=>'餐厅',13=>'企业',14=>'其他');                        
                        $data['location'] = array(0=>'位置',1=>'出⼊⼝',2=>'中庭',3=>'连廊',4=>'扶梯⼝',5=>'厕所旁',6=>'物业管理处',7=>'服务中⼼',8=>'边庭',9=>'商铺',10=>'电梯⼝',11=>'中⼼⼴场',11=>'花园',12=>'下沉式⼴场',13=>'售票⼤厅',14=>'外⼴场',15=>'⼤堂',16=>'停⻋位处',17=>'公告栏旁边',18=>'收银台旁边',19=>'未知');
                        $data['SiteList'] = $getSiteLists;
                        $data['total'] = $getSiteList['total'];
                        $this->returnSuccess('成功',$data);
                        break;
                        
                    case "1007"://创建点位
                        if (empty($this->stream['request']['node_name']) || empty($this->stream['request']['node_type']) || empty($this->stream['request']['location']) || empty($this->stream['request']['market_id']) || empty($this->stream['request']['market_name']) || empty($this->stream['request']['id']))
                        {
                            $this->returnError('参数有误！');
                        }
                        //获取天猫点位id待处理
                        $sendData =[
                            'sn'=>time(),
                            'mallId'=>$this->stream['request']['market_id'],
                            'popupName'=>$this->stream['request']['node_id'],
                            'addressDetail'=>$this->stream['request']['node_address'],
                            'popupType' =>'OTHER',
                            'ownerEntityId'=>558,
                        ];
                        $url = 'http://huygens.dev.uboxol.com/tmall/shopstore/create';
                        $sign = $this->createSign($sendData);
                        $sendData['sign'] = $sign;
//                         $res = $this->curlRequest($url,'1',json_encode($sendData));
//                         $res = json_decode($res,true);
                        
                        $res['code'] =200;
                        $res['data']['tmallNodeId'] =100;
                        if ($res['code'] != 200)
                        {
                            return $this->returnError('创建失败！');
                        }
                        //获取完天猫id更新
                        $set = [
                            'node_name'=>$this->stream['request']['node_name'],
                            'node_type'=>$this->stream['request']['node_type'],
                            'location'=>$this->stream['request']['location'],
                            'market_id'=>$this->stream['request']['market_id'],
                            'market_name'=>$this->stream['request']['node_name'],
                            'tmall_node_id'=>$res['data']['tmallNodeId'],
                        ];
                        $where[] = ['id',$this->stream['request']['id']];
                        $res = ResourceManagement::updateSite($where,$set);
                        $this->returnSuccess('成功');
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
                                $where[] = ['device.device_code','!=',0];
                            }else if ($this->stream['request']['type'] ==4)
                            {
                                $where[] = ['device.device_code','=',0];
                            }
                        }
                        if(!empty($this->stream['request']['vm_code']) && is_numeric($this->stream['request']['vm_code']))
                        {
                            $where[] = ['device.vm_code','=',$this->stream['request']['vm_code']];
                        }
                        $limit = empty($this->stream['request']['limit']) ? 20:$this->stream['request']['limit'];
                        $getDeviceList = ResourceManagement::getDeviceList($where,$limit);
                        $this->returnSuccess('成功',$getDeviceList);
                        break;
                        
                    case "1009"://创建设备
                        if (empty($this->stream['reuqest']['node_id']) || empty($this->stream['reuqest']['vm_code']) || empty($this->stream['reuqest']['node_name']) || empty($this->stream['reuqest']['tmall_node_id']) || empty($this->stream['reuqest']['vm_type']) || empty($this->stream['reuqest']['store_id']) || empty($this->stream['reuqest']['id']))
                        {
                            $this->returnError('参数有误！');
                        }
                        //获取天猫点位device_code待处理
                        $sendData =[
                            'sn'=>time(),
                            'deviceName'=>$this->stream['request']['vm_code'],
                            'deviceType'=>$this->stream['request']['vm_type'],
                            'storeId'=>$this->stream['request']['store_id'],
                            'ownerId'=>558,
                        ];
                        $url = 'http://huygens.dev.uboxol.com/tmall/device/create';
                        $sign = $this->createSign($sendData);
                        $sendData['sign'] = $sign;
                        $res = $this->curlRequest($url,'1',json_encode($sendData));
                        $res = json_decode($res,true);
                        if ($res['code'] != 200)
                        {
                            return $this->returnError('创建失败！');
                        }
                        //获取完天猫device_code更新
                        $set = [
                            'device_id'=>$res['data']['deviceId'],
                            'device_code'=>$res['data']['deviceCode'],
                            'activation_code'=>$res['data']['activeCode'],
                        ];
                        $where[] = ['id',$this->stream['request']['id']];
                        $res = ResourceManagement::updateDevice($where,$set);
                        break;
                        
                    case "1010"://任务列表
                        $where =[];
                        if(!empty($this->stream['request']['item_name']) && is_numeric($this->stream['request']['item_name']))
                        {
                            $where[] = ['item_name','like',$this->stream['request']['item_name']];
                        }
                        if (!empty($this->stream['request']['activity_time']))
                        {
                            $where[] =['activity_time','>=',$this->stream['request']['activity_time']];
                        }
                        $limit = empty($this->stream['request']['limit']) ? 20:$this->stream['request']['limit'];
                        $getTaskList = ResourceManagement::getTaskList($where,$limit);
                        $this->returnSuccess('成功',$getTaskList);
                        break;
                    case "1011"://仓库分配详情
                        if (empty($this->stream['request']['id']))
                        {
                            $this->returnError('参数有误！');
                        }
                            $getTaskToWarehouse =  ResourceManagement::getTaskToWarehouse($this->stream['request']['id']);
                            if (!empty($getTaskToWarehouse))
                            {
                                $list = $barn_id = [];
                                foreach ($getTaskToWarehouse as $val)
                                {
                                    $list[$val->barn_id] = $val;
                                    $barn_id[] = $val->barn_id;
                                }
                                
                                
//                                 $getProductToSite = ResourceManagement::getSitefWId($barn_id);
//                                 if (!empty($getProductToSite))
//                                 {
//                                     foreach ($getProductToSite as $v)
//                                     {
//                                         if (!empty($list[$v->barn_id]))
//                                         {
//                                             $list[$val->barn_id]->siteList[] = $v->node_name; 
//                                         }
//                                     }
//                                 }
                                $data['list'] = $list;
                            }
                        $this->returnSuccess('成功',$data);
                        break;
                    case "1012"://仓库分配
                        if (empty($this->stream['request']['lists']))
                        {
                            $this->returnError('参数有误！');
                        }
                        $data = [];
                        foreach ($this->stream['request']['lists'] as $val)
                        {
                            $data[] = [
                                'id'=>$val['id'],
                                'amount'=>$val['amount']
                            ];
                        }
                        $updateTaskToWarehouse = ResourceManagement::updateBatch($data,'laravel_task_to_warehouse');
                        if ($updateTaskToWarehouse)
                        {
                            $this->returnSuccess('成功',$data);
                        }
                        $this->returnError('更新失败！');
                        break;
                    case "1013"://商品配置
                        if (empty($this->stream['request']['id']) || empty($this->stream['request']['product_id']) || empty($this->stream['request']['img_url']) || empty($this->stream['request']['list']) || empty($this->stream['request']['item_id']) ||empty($this->stream['request']['activity_id']))
                        {
                            $this->returnError('参数有误！');
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
                        $updateProductToDevice = ResourceManagement::updateProductToDevice($whereDevice,['delete_mark'=>1,'update_at'=>date("Y-m-d H:i:s")]);
                        $insertProductToDevice = ResourceManagement::insertProductToDevice($data);
                        $where = [];
                        if ($insertProductToDevice)
                        {
                            $where[]= ['id','=',$this->stream['request']['id']];
//                             $where[]= ['finish_flag','=',1];
                            $set =['finish_flag'=>2];
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
                            $where[]= ['id','=',$this->stream['request']['id']];
                            $where[]= ['finish_flag','=',2];
                            $set =['finish_flag'=>4];
                            $updateTaskToProductOne = ResourceManagement::updateTaskToProductOne($where,$set);
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
                            $where[] = ['product_name','like',$this->stream['request']['product_name']];
                        }
                        if (!empty($this->stream['request']['activity_start_time']))
                        {
                            $where[] =['activity_start_time','>=',$this->stream['request']['activity_start_time']];
                        }
                        if (!empty($this->stream['request']['activity_end_time']))
                        {
                            $where[] =['activity_end_time','>=',$this->stream['request']['activity_end_time']];
                        }
                        
                        $limit = empty($this->stream['request']['limit']) ? 20:$this->stream['request']['limit'];
                        $getDeviceList = ResourceManagement::getTaskList($where,$limit);
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
                            $buyTotal =$getTotal =0 ;
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
                                $list[$val->barn_id]['list'][] = $val;
                                $list[$val->barn_id]['buyTotal'] +=$val->shipment_amount;
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
                        $limit = empty($this->stream['request']['limit']) ? 20:$this->stream['request']['limit'];
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
                            $total = $inbound_num = $outbound_num = $deposit_num = $damage_amount= 0;
                            foreach ($getinventoryList as $val)
                            {
                                $total += 1;
                                $inbound_num +=$val->inbound_num;
                                $outbound_num +=$val->outbound_num;
                                $deposit_num +=$val->deposit_num;
                                $damage_amount  += $val->damage_amount; 
                            }
                            $totalList = [
                                'total'=>$total,
                                'inbound_num' =>$inbound_num,
                                'outbound_num'=>$outbound_num,
                                'damage_amount'=>$damage_amount,
                                'damage_rate' => empty($outbound_num) ? 0:round($damage_amount/$outbound_num,2)*100 .'%',
                                'deposit_num'=>$deposit_num,
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
            }
        }
       
    }
    
    public function returnError($msg)
    {
        echo  json_encode(array('status'=>200,'msg'=>$msg,'data'=>''));exit;
    }
    
    public function returnSuccess($msg,$data='')
    {
        echo  json_encode(array('status'=>0,'msg'=>$msg,'data'=>$data));exit;
    }
    
    public function upload1()
    {
        return view('test_upload');
    }
    
    public function upload(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        $id = $request->input('id');
        $image = $request->file('file')->storeAs('/public/image/product',$id.'.png');//上传商品图片
        $url = 'http://dev-uc.ipktv.com/storage/app/'.$image;
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

}