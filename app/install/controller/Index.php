<?php

namespace app\install\controller;

use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;

class Index
{
    public function index()
    {
        if(is_file(root_path() . 'public//install.lock')) {
            return error('网站已经安装');
        }
        return View::fetch();
    }

    public function step1()
    {
        if(Request::isPost()) {
            $env[] = [
                'name' => '操作系统',
                'record' => 'Unix',
                'current' => PHP_OS,
                'low' => '不限制',
                'status' => true
            ];
            $env[] = [
                'name' => 'PHP版本',
                'record' => '>7.2.x',
                'current' => PHP_VERSION,
                'low' => '7.2.x',
                'status' => PHP_VERSION > '7.0'
            ];

            $other = [
                'session',
                'pdo',
                'pdo_mysql',
                'curl',
                'gd',
                'mbstring',
                'fileinfo'
            ];
            foreach ($other as $value) {
                $open = extension_loaded($value);
                $ext[] = [
                    'name' => $value,
                    'record' => '开启',
                    'current' => $open ? '支持' : '不支持',
                    'low' => '开启',
                    'status' => $open
                ];
            }
            $check_path = [
                [
                    'name' => '控制器',
                    'path' => 'app/admin/controller'
                ],
                [
                    'name' => '模型',
                    'path' => 'app/admin/model'
                ],
                [
                    'name' => '视图',
                    'path' => 'app/admin/view'
                ],
                [
                    'name' => '文件上传',
                    'path' => 'public/storage'
                ],
                [
                    'name' => '配置文件',
                    'path' => 'public/static/backend/json'
                ],
                [
                    'name' => 'public',
                    'path' => 'public'
                ],
                [
                    'name' => 'database',
                    'path' => 'config/database.php'
                ],
            ];
            $paths = [];

            foreach ($check_path as $value) {
                $path = [
                    'name' => $value['name'],
                    'record' => '可写',
                    'current' => '可写',
                    'low' => '可写',
                    'status' => true,
                    'path' => root_path() . $value['path']
                ];
                if(!is_writable(root_path() . $value['path'])) {
                    $path['current'] = '不可写';
                    $path['status'] = false;
                }
                $paths[] = $path;
            }
            $all = $env + $ext + $paths;
            $is_next = true;
            foreach ($all as $value) {
                if(false === $value['status']) {
                    $is_next = false;
                    break;
                }
            }
            return success([
                'env' => $env,
                'ext' => $ext,
                'paths' => $paths,
                'is_next' => $is_next
            ]);
        }
    }


    public function check(\think\Request $request)
    {
        if($request->isPost()) {
            $db_config = Config::get('database');
            $hostname = $request->post('hostname');
            $username = $request->post('username');
            $password = $request->post('password');
            $hostport = $request->post('hostport');
            $charset = $request->post('charset');
            $database = $request->post('database');
            $prefix = $request->post('prefix');

            $db_config['connections']['dynamic'] = [
                'type' => 'mysql',
                'hostname' => $hostname,
                'username' => $username,
                'password' => $password,
                'hostport' => $hostport,
                'charset' => $charset,
            ];
            Config::set($db_config, 'database');
            try {
                Db::connect('dynamic', true)->connect();
            } catch (\PDOException $exception) {
                return error($exception->getMessage(), $exception->getCode(), ['next' => false]);
            }

            if('check' == Request::post('action')) {
                return success(['next' => true], 200, '链接数据库成功');
            }


            //创建数据库
            $sql = "CREATE DATABASE IF NOT EXISTS `" . $database . "` DEFAULT CHARACTER SET " . $charset;
            try {
                Db::connect('dynamic', false)->execute($sql);
            } catch (\PDOException $exception) {
                return error($exception->getMessage(), $exception->getCode());
            }
            \session('');
            //设置session
            \session('install_databases', [
                'type' => 'mysql',
                'hostname' => $hostname,
                'username' => $username,
                'password' => $password,
                'hostport' => $hostport,
                'database' => $database,
                'charset' => $charset,
                'prefix' => $prefix
            ]);

            $sql_path = app_path() . 'data/install.sql';
            $sql = split_sql($sql_path, $prefix, $charset); //对sql 进行替换
            \session('install_sql', $sql);
            $sql_count = count($sql);

            \session('install_error', 0);

            //设置站点名称
            $title = $request->post('title');
            $keywords = $request->post('keywords');
            $description = $request->post('description');
            $admin_title = $request->post('admin_title');

            \session('install_site', [
                'title' => $title,
                'keywords' => $keywords,
                'description' => $description,
                'admin_title' => $admin_title,
            ]);

            $admin_username = $request->post('admin_username');
            $admin_password = $request->post('admin_password');

            \session('install_admin', [
                'admin_username' => $admin_username,
                'admin_password' => $admin_password,
            ]);

            return success(['sql_count' => $sql_count]);
        }
    }


    public function install(\think\Request $request)
    {
        $database = \session('install_databases');
        $sql = \session('install_sql');
        $sql_index = $request->post('sql_index') ?: 0;
        $db_config = Config::get('database');
        $db_config['connections']['dynamic'] = $database;
        Config::set($db_config, 'database');
        if($sql_index >= count($sql)) {
            return success([
                'sql_error' => \session('install_error') ?: 0,
                'message' => '数据库安装完成！'
            ]);
        }
        $sql_to_exec = $sql[$sql_index] . ';';
        try {
            $db = Db::connect('dynamic', true);
        } catch (\PDOException $exception) {
            return error($exception->getMessage(), $exception->getCode(), ['next' => false]);
        }

        $result = sp_execute_sql($db, $sql_to_exec);
        if(!empty($result['error'])) {
            $install_error = \session('install_error') ?: 0;
            \session('install_error', $install_error + 1);
        }
        return success($result);
    }

    /**
     * @param \think\Request $request
     * @return \think\response\Json
     * 设置站点基本信息
     */
    public function site(\think\Request $request)
    {
        if($request->isPost()) {
            $db = $this->connect_session_mysql();
            $site = \session('install_site');
            foreach (['title', 'keywords', 'description', 'admin_title'] as $value) {
                $sql = "UPDATE {$db['config']['prefix']}config SET `value` = '{$site[$value]}' WHERE `key` = '{$value}'";
                $db['db']->execute($sql);
            }
            return success([
                'error' => 0,
                'message' => '设置站点信息成功！'
            ]);
        }
    }

    public function admin(\think\Request $request)
    {
        if($request->isPost()) {
            $db = $this->connect_session_mysql();
            $admin = \session('install_admin');
            $date = date('Y-m-d H:i:s');
            $pass = password_hash($admin['admin_password'], 1);
            $account = $admin['admin_username'];
            $ip = $request->ip();
            try{
                $sql = "INSERT INTO `{$db['config']['prefix']}admin` VALUES (1, 0,'','$account','$pass','$ip','$date','$date');";
                $res = $db['db']->execute($sql);
            }catch (\Exception $e){
                return error($e->getMessage().'->管理员账号添加错误，请重试:'.$sql);
            }
            if($res) {
                return success([
                    'error' => 0,
                    'message' => '增加管理员信息成功！'
                ]);
            }
            return error('增加管理员信息失败,请检查配置或者重新安装（需要删掉已安装的数据库）');
        }
    }

    //设置数据库信息
    public function database(\think\Request $request)
    {
        if($request->isPost()) {
            $database_session = \session('install_databases');
            //$database_file = file_get_contents(app_path() . 'data/database.tpl');
            $database_file = file_get_contents(app_path() . 'data/env.develop.tpl');
            //替换变量
            $database_file = sprintf($database_file,
                $database_session['hostname'],
                $database_session['database'],
                $database_session['username'],
                $database_session['password'],
                $database_session['hostport'],
                $database_session['charset'],
                $database_session['prefix']
            );
            //替换数据库配置文件
            $res = file_put_contents(root_path() . '.env.develop', $database_file);
            if($res) {
                return success([
                    'error' => 0,
                    'message' => '更新数据库配置文件成功！'
                ]);
            }
            return error('更新数据库配置文件失败，,请检查配置或者重新安装（需要删掉已安装的数据库）');
        }
    }

    private function connect_session_mysql()
    {
        $database = \session('install_databases');
        $db_config = Config::get('database');
        $db_config['connections']['dynamic'] = $database;
        Config::set($db_config, 'database');

        try {
            $db = Db::connect('dynamic', true);
        } catch (\PDOException $exception) {
            return error($exception->getMessage(), $exception->getCode(), ['next' => false]);
        }
        return [
            'db' => $db,
            'config' => $database
        ];
    }

    public function block()
    {
        $path = root_path() . 'public/install.lock';
        @touch($path);
        return success([
            'error' => 0,
            'message' => '写入锁定文件成功！'
        ]);
    }
}