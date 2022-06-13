<?php

namespace backend\traits;

use element\facade\Html;
use element\facade\Rending;
use think\App;
use think\facade\Request;
use think\response\Json;

trait View
{
    public $model;

    public $fields;

    public $tree_table = false;

    public $pk = 'id';

    public $order_by = 'create_time desc';

    public function __construct(App $app)
    {
        parent::__construct();
        $model_path = '\\app\\admin\\model\\';
        $json_path = root_path() . 'public/static/backend/json/';
        $model_name = explode('.', strtolower($app->request->controller()));
        foreach ($model_name as $key => $value) {
            if($key == count($model_name) - 1){
                $model_path .= ucfirst($value) . '\\';
            }else{
                $model_path .= $value . '\\';
            }
            $json_path .= $value . '/';
        }
        $model_path = trim($model_path, '\\');
        $json_path = rtrim($json_path, '/');
        $json_path .= '.json';
        $this->model = new $model_path;
        $this->pk = $this->model->getPk();
        $fields = file_get_contents($json_path);
        $this->fields = json_decode($fields, true);
        $this->tree_table = $this->fields['tree_table'];
        $orders = isset($this->fields['order_by']) ? json_decode($this->fields,true) : [];//"order_by": [{"field":"id","order": "desc"}]
        if(!empty($order)){
            foreach ($orders as $key => $order){
                $this->order_by.= $order['field'].' '.$order['order'].',';
            }
            $this->order_by = trim($this->order_by,',');
        }
        Rending::table_form_search_rules($this->fields, $this->tree_table, $this->pk, true, true);
    }

    /**
     * @return string|Json
     */
    public function index()
    {
        if (Request::isPost()) {
            $page = Request::post('page');
            $search = Request::post('search'); //根据字段信息拼接查询
            $start = ($page - 1) * 20;
            $where = " 1 = 1 ";
            if (!empty($search)) {
                if($search['start_time'] && $search['end_time']){
                    $where .= " and `create_time` >= '{$search['start_time']} 'and `create_time` <= '{$search['end_time']}'";
                }elseif ($search['start_time'] && !$search['end_time']){
                    $where .= " and `create_time` >= '{$search['start_time']}' ";
                }elseif (!$search['start_time'] && $search['end_time']){
                    $where .= " and `end_time` <= '{$search['end_time']}' ";
                }
                unset($search['start_time']);
                unset($search['end_time']);
                $fields = array_column($this->fields['fields'], null, 'key');
                foreach ($search as $key => $value) {
                    if (!empty($value)) {
                        switch ($fields[$key]['prop']['search']) {
                            case 'like':
                                $where .= " and `{$key}` like '%{$value}%'";
                                break;
                            case '>':
                                $where .= " and `{$key}` > {$value}";
                                break;
                            case '<':
                                $where .= " and `{$key}` < {$value}";
                                break;
                            default:
                                $where .= " and `{$key}` = '{$value}'";
                                break;
                        }
                    }
                }
            }
            //如果是树形表格,采用递归方式获取表格内容，并且默认上级字段为pid
            if (true === $this->tree_table) {
                $where .= " and pid = 0";
                $list = $this->model->whereRaw($where)->order($this->order_by)->limit($start, 20)->select();
                if (!empty($list)) {
                    $c = function (&$list) use (&$c) {
                        foreach ($list as $key => &$value) {
                            //根据父级别ID查询
                            $children = $this->model->where('pid', $value['id'])->select() ?:[];
                            $list[$key]['children'] = $children;
                            if (!empty($children)) {
                                $c($children);
                            }
                        }
                        return $list;
                    };
                    $list = $c($list);
                }
            } else {
                $list = $this->model->whereRaw($where)->order($this->order_by)->limit($start, 20)->select();
            }
            $count = $this->model->whereRaw($where)->count();

            return success([
                'list' => $list,
                'count' => intval($count)
            ], 200, '加载数据成功');
        }
        \think\facade\View::assign('pk', $this->pk);
        return \think\facade\View::fetch('common/index');
    }

    /**
     * @return Json
     */
    public function post(): Json
    {
        if (Request::isPost()) {
            $data = Request::only(Request::post());
            if (isset($data[$this->pk]) && !empty($data[$this->pk])) {
                $res = $this->model::update($data, [$this->pk => $data[$this->pk]]);
            } else {
                $res = $this->model->save($data);
            }
            if (false !== $res) {
                return success([], 200, '操作成功');
            }
            return error('操作失败');
        }
        return error('错误的请求方式');
    }

    /**
     * @return Json
     */
    public function delete(): Json
    {
        if (Request::isPost()) {
            $id = Request::post($this->pk);
            if (empty($id) || !isset($id)) return error('缺失的主键');
            $res = $this->model->where('id', '=', $id)->delete();
            if ($res) {
                return success([], 200, '删除数据成功');
            }
            return error('删除数据失败');
        }
        return error('错误的请求方式');
    }

    /**
     * @return Json
     */
    public function deletes(): Json
    {
        if (Request::isPost()) {
            $ids = Request::post('ids');
            if (!is_array($ids) || empty($ids)) {
                return error('缺失的主键');
            }
            $is_tree = $this->tree_table;
            if (true === $is_tree) {
                foreach ($ids as $key => $value) {
                    $row = $this->model->where('pid', $value)->findOrEmpty() ?: [];
                    if (!$row->isEmpty()) {
                        return error('有数据存在下级节点，请先删除！');
                    }
                }
            }
            $ids = implode(',', $ids);
            $res = $this->model->where('id', 'in', $ids)->delete();
            if ($res) {
                return success([], 200, '删除数据成功');
            }
            return error('删除数据失败');
        }
        return error('错误的请求方式');
    }
}