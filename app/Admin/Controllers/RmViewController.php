<?php
namespace App\Admin\Controllers;

use App\Admin\Controllers\Controller;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\AdminCommonController;

class RmViewController extends AdminCommonController
{

    public $header     = '';
    public function __construct()
    {
        $this->header   = '';
        parent::__construct();

    }

    public function actionT()
        {
            $type = app('request')->get('type');
            $url = admin_url('RmView/'.$type);
            header('location:'.$url);
        }
        //仓库创建
        public function createStoreHouse()
        {
            $content = new Content;
            
            $data['request_url'] = admin_url('resourceManagement/index');
            $data['static_url'] = '';
            return $content
            ->header($this->header)
            ->description(' ')
            ->body(view('admin.ybPf.createStoreHouse',$data));
        }
        //任务列表
        public function taskManagement()
        {
            $content = new Content;
            $data['request_url'] = admin_url('resourceManagement/index');
            $data['request_upload'] = admin_url('resourceManagement/upload');
            $data['static_url'] = '';
            return $content
            ->header($this->header)
            ->description(' ')
            ->body(view('admin.ybPf.taskManagement',$data));
        }
        
        
        //商品绑定
        public function mallBinding()
        {
            $content = new Content;
            $data['request_url'] = admin_url('resourceManagement/index');
            $data['static_url'] = '';
            return $content
            ->header($this->header)
            ->description(' ')
            ->body(view('admin.ybPf.mallBinding',$data));
        }
        
        //点位列表
        public function createPoint()
        {
            $content = new Content;
            $data['request_url'] =admin_url('resourceManagement/index');
            $data['static_url'] = '';
            return $content
            ->header($this->header)
            ->description(' ')
            ->body(view('admin.ybPf.createPoint',$data));
        }
        
        //设备列表
        public function createEquipment()
        {
            $content = new Content;
            $data['request_url'] =admin_url('resourceManagement/index');
            $data['static_url'] = '';
            return $content
            ->header($this->header)
            ->description(' ')
            ->body(view('admin.ybPf.createEquipment',$data));
        }
        
        
        
        //商品采购
        public function merchandisePurchase()
        {
            $content = new Content;
            $data['request_url'] =admin_url('resourceManagement/index');
            $data['static_url'] = '';
            return $content
            ->header($this->header)
            ->description(' ')
            ->body(view('admin.ybPf.merchandisePurchase',$data));
        }
        
        //数据看板
        public function datareConciliation()
        {
            $content = new Content;
            $data['request_url'] =admin_url('resourceManagement/index');
            $data['static_url'] = '';
            return $content
            ->header($this->header)
            ->description(' ')
            ->body(view('admin.ybPf.datareConciliation',$data));
        }
        
}