<?php
namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminCommonController;
use App\Admin\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Excel;

class ExcelController extends AdminCommonController
{
    
    
    
    
    public function export()
    {
        $cellData = DB::table('site')->select('org5_id','node_name','node_type')->limit(5)->get()->toArray();
        $cellData1[] = array('仓id','点位名称','点位类型');
        foreach ($cellData as $val)
        {
            $a = array($val->org5_id,$val->node_name,$val->node_type);
            $cellData1[] =str_replace('=',' '.'=',$a);
        }
        Excel::create('点位信息',function($excel) use ($cellData1){
            $excel->sheet('score', function($sheet) use ($cellData1){
                $sheet->rows($cellData1);
            });
        })->export('xls');
        die;
    }
  }
 

