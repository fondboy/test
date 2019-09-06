<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use DB;
use Encore\Admin\Facades\Admin;

class HomeController extends Controller
{
    public function index(Content $content)
    {

        $content
            //->header('首页')
            ->description('欢迎'.Admin::user()->name) ;

        return $content;
		/*$content
			 ->breadcrumb(
				['text' => '面包屑导航1', 'url' => '/admin'],
				['text' => '面包屑导航2', 'url' => '/admin/users'],
				['text' => '面包屑导航3']
			);*/
			
		   // 添加面包屑导航 since v1.5.7
			/* ->breadcrumb(
				['text' => , 'url' => '/admin'],
				['text' => '面包屑导航2', 'url' => '/admin/users'],
				['text' => '面包屑导航3']
			)  */
		/*$content
            ->row(Dashboard::title());*/
        $content
			->row(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::extensions());
                });

                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::environment());
                });
               /* $row->column(4, function (Column $column) {
                    $column->append(Dashboard::dependencies());
                });*/
            });
			//->row('用户常用链接');
			
			return $content;
    }

    /*
     用户热门点击的菜单
     */
    public static function userHotCick(){

        /*DB::table('admin_user_menu_log')->join('admin_menu_log')
            ->where(['user_id'=>,'time'])->skip(($page-1)*$pagesize)->take($pagesize)->get()->toArray();*/


        $extensions = [
            'helpers' => [
                'name' => 'laravel-admin-ext/helpers',
                'link' => 'https://github.com/laravel-admin-extensions/helpers',
                'icon' => 'gears',
            ],
            'log-viewer' => [
                'name' => 'laravel-admin-ext/log-viewer',
                'link' => 'https://github.com/laravel-admin-extensions/log-viewer',
                'icon' => 'database',
            ],
            'backup' => [
                'name' => 'laravel-admin-ext/backup',
                'link' => 'https://github.com/laravel-admin-extensions/backup',
                'icon' => 'copy',
            ],
            'config' => [
                'name' => 'laravel-admin-ext/config',
                'link' => 'https://github.com/laravel-admin-extensions/config',
                'icon' => 'toggle-on',
            ],
            'api-tester' => [
                'name' => 'laravel-admin-ext/api-tester',
                'link' => 'https://github.com/laravel-admin-extensions/api-tester',
                'icon' => 'sliders',
            ],
            'media-manager' => [
                'name' => 'laravel-admin-ext/media-manager',
                'link' => 'https://github.com/laravel-admin-extensions/media-manager',
                'icon' => 'file',
            ],
            'scheduling' => [
                'name' => 'laravel-admin-ext/scheduling',
                'link' => 'https://github.com/laravel-admin-extensions/scheduling',
                'icon' => 'clock-o',
            ],
            'reporter' => [
                'name' => 'laravel-admin-ext/reporter',
                'link' => 'https://github.com/laravel-admin-extensions/reporter',
                'icon' => 'bug',
            ],
            'redis-manager' => [
                'name' => 'laravel-admin-ext/redis-manager',
                'link' => 'https://github.com/laravel-admin-extensions/redis-manager',
                'icon' => 'flask',
            ],
        ];

        foreach ($extensions as &$extension) {
            $name = explode('/', $extension['name']);
            $extension['installed'] = array_key_exists(end($name), Admin::$extensions);
        }

        return view('admin::dashboard.extensions', compact('extensions'));
    }

    /*
     用户最近点击的菜单
     */
    public static function userNewCick(Content $content){

    }



}
