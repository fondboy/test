<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Layout\Content;

class RmViewController extends Controller
{
        public function WarehouseList(Content $content)
        {
            return $content
            			->header('')
            			->description('点位资源管理')
            			->body(view('createStoreHouse'));
        }
        
        public function BarnTmList(Content $content)
        {
            return $content
            ->header('')
            ->description('点位资源管理')
            ->body(view('createBarnTm'));
        }
}