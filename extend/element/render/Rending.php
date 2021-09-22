<?php

namespace element\render;

use app\admin\model\auth\Menu;
use backend\plug\Plugins;
use element\component\hook\Hook;
use think\facade\Event;
use think\facade\View;

class  Rending
{
    public $expend_all = false;
    public static $ot = [];

    public $button = ["add", "edit", "delete", "deletes", "search", "reset"];

    public function table_form_search_rules(array $fields, $tree_table = false, string $pk = 'id', $is_table = true, $is_search = true)
    {
        if (!isset($fields['button'])) $fields['button'] = $this->button;
        $this->expend_all = ($fields['expand_all'] === true) ? "true" : "false";
        $form = [];
        $form_html = '<el-form ref="form" :rules="rules" :model="form" label-width="auto">' . PHP_EOL;
        if (true === $is_table) {
            $table_html = '<el-table ref="multipleTable" border :data="data" tooltip-effect="dark" style="width: 100%" @selection-change="handleSelectionChange" ';
            if (true === $tree_table) {
                $table_html .= ' row-key="id"';
                $table_html .= ' :default-expand-all=' . $this->expend_all;
                $table_html .= ' :tree-props="{children: \'children\', hasChildren: \'hasChildren\'}"';
            }
            $table_html .= '>';
            $table_html .= '<el-table-column type="selection" width="55"></el-table-column>';
        }
        if (true === $is_search) {
            $search_html = '';
            $search = [];
        }
        $rules = [];
        foreach ($fields['fields'] as $field) {
            if (!isset($field['prop']['form_hidden'])) {
                $form_html .= Hook::make(ucfirst($field['type']), $field, 'form');
            }
            $form[$field['key']] = $field['value'];
            if (true === $is_table && true === $field['prop']['table_show']) {
                if (isset($field['prop']['options']) || isset($field['prop']['callback'])) {
                    if (!empty($field['prop']['options'])) {
                        $option = json_encode($field['prop']['options'], 256);
                    } else {
                        $model = new $field['prop']['callback'][0]();
                        $option = $model->{$field['prop']['callback'][1]}() ?: [];
                        $t = function ($option) use (&$t) {
                            foreach ($option as &$o) {
                                if (isset($o['children']) && !empty($o['children'])) {
                                    $children = $o['children'];
                                    unset($o['children']);
                                    self::$ot[] = $o;
                                    $t($children);

                                } else {
                                    self::$ot[] = $o;
                                }
                            }
                            return self::$ot;
                        };
                        $ot = $t($option);
                        $option = json_encode($ot ?: [], 256);
                    }
                    $table_html .= '<el-table-column  label="' . $field['label'] . '">';
                    $table_html .= '  <template #default="scope"> ';
                    $table_html .= '  <span v-for=\'(item,index) in ' . $option . '\'> ';
                    $table_html .= '  <el-tag v-if="item.' . $field['prop']['bind_value'] . ' == scope.row.' . $field['key'] . '"> {{item.' . $field['prop']['bind_label'] . '}}</el-tag> ';
                    $table_html .= '</span>';
                    $table_html .= '</template>';
                    $table_html .= '</el-table-column>';
                } else {
                    if (strtolower($field['type']) == 'image') {
                        $table_html .= '<el-table-column  label="' . $field['label'] . '">';
                        $table_html .= '<template #default="scope"><el-image';
                        $table_html .= ' style="width: 50px; height: 50px"';
                        $table_html .= ' :src="scope.row.' . $field['key'] . '"';
                        $table_html .= ' :preview-src-list="[scope.row.' . $field['key'] . ']">';
                        $table_html .= '</el-image>';
                        $table_html .= '</template>';
                        $table_html .= '</el-table-column>';
                    } else {
                        $table_html .= '<el-table-column prop="' . $field['key'] . '" label="' . $field['label'] . '"></el-table-column>';
                    }
                }
            }
            if (isset($field['prop']['search']) && !empty($field['prop']['search']) && true === $is_search) {
                $search_html .= Hook::make(ucfirst($field['type']), $field, 'search') . PHP_EOL;
                $search[$field['key']] = $field['value'];
            }
            if (true === $field['prop']['is_null']) {
                $rules[$field['key']] = [
                    [
                        'required' => true,
                        'message' => '请输入' . $field['label'],
                        'trigger' => $field['prop']['trigger'] ?? ''
                    ]
                ];
            }
        }
        if (true === $is_search) {
            $search_html .= Hook::make('Date', [
                'key' => 'start_time',
                'label' => '开始时间',
                'placeholder' => '请输入开始时间',
            ], 'search');
            $search['start_time'] = '';
            $search_html .= Hook::make('Date', [
                'key' => 'end_time',
                'label' => '结束时间',
                'placeholder' => '请输入结束时间',
            ], 'search');
            $search['end_time'] = '';
        }
        //获取插件
        if (isset($fields['plugins']) && !empty($fields['plugins'])) $plugins = $this->plugins($fields['plugins']);

        if (true === $is_table) {
            $table_html .= '<el-table-column fixed="right" label="操作" width="120">';
            $table_html .= '<template #default="scope">';
            if (in_array("edit", $fields['button'])) {
                $table_html .= '<el-button type="info" icon="el-icon-edit" @click="onEdit(scope.row)"></el-button>';
            }
            if (in_array("delete", $fields['button'])) {
                $table_html .= '<el-popconfirm title="确定删除吗？" @confirm="onDelete(scope.row.' . $pk . ')">';
                $table_html .= '<template #reference><el-button type="danger" icon="el-icon-delete"></el-button></template>';
                $table_html .= '</el-popconfirm>';
            }
            //button 事件
            $table_html .= $plugins['plugin_button'] ?? '';
            $table_html .= '</template></el-table-column>';
            $table_html .= ' </el-table>';
        }
        $form_html .= '</el-form>';

        //插件
        View::assign($plugins ?? []);

        //通用
        View::assign([
            'form' => json_encode($form, JSON_UNESCAPED_UNICODE),
            'form_html' => $form_html,
            'table_html' => $table_html ?? '',
            'search_html' => $search_html ?? '',
            'search' => json_encode($search ?? [], JSON_UNESCAPED_UNICODE),
            'rules' => json_encode($rules, JSON_UNESCAPED_UNICODE),
            'button' => $fields['button']
        ]);
    }

    /**
     * @param $plugins
     * 后台插件
     */
    public function plugins($plugins)
    {
        $plugin_html = '';
        $plugin_data = [];
        $plugin_func = '';
        $plugin_mon = '';
        $plugin_script = '';
        $plugin_css = '';
        $plugin_button = '';

        foreach ($plugins as $key => $value) {
            $res = hook($value, null, false); //调用插件
            $res = json_decode($res, true);
            $plugin_html .= $res['plugin_html'] ?? '';
            $plugin_func .= $res['plugin_func'] ?? '' . ',';
            $plugin_data += $res['plugin_data'] ?? [];
            $plugin_mon .= $res['plugin_mon'] ?? '';
            $plugin_script .= $res['plugin_script'] ?? '';
            $plugin_css .= $res['plugin_css'] ?? '';
            $plugin_button .= $res['plugin_button'] ?? '';
        }

        return [
            'plugin_html' => $plugin_html,
            'plugin_data' => json_encode($plugin_data, 256),
            'plugin_func' => $plugin_func,
            'plugin_mon' => $plugin_mon,
            'plugin_script' => $plugin_script,
            'plugin_css' => $plugin_css,
            'plugin_button' => $plugin_button
        ];
    }

    public function aside($routes = [])
    {
        $model = new Menu();
        $menus = $model->getMenus($routes);
        View::assign('aside_html', $this->treeAside(
            tree($menus, 0)
        ));
    }

    private function treeAside(array $menus): string
    {
        $html = ' ';
        foreach ($menus as $key => $row) {
            if (!$row['children']) {
                $html .= '<a href="' . $row['route'] . '"><el-menu-item index="' . $row['id'] . '"><template #title><i class="' . $row['icon'] . '"></i><span>' . $row['name'] . '</span></template></el-menu-item></a>';
            } else {
                $html .= '<el-submenu index="' . $row['id'] . '"><template #title><i class="' . $row['icon'] . '"></i> <span>' . $row['name'] . '</span></template>';
                $html .= $this->treeAside($row['children']);
                $html .= '</el-submenu>';
            }
        }
        return $html;
    }

    public function breadcrumb($row, $model)
    {
        $parents = function ($pid) use ($model, &$parents) {
            static $data = [];
            if ($pid != 0) $row = $model->getRowById($pid);
            if (!empty($row)) {
                $data[] = $row;
                $parents($row['pid']);
            }
            return $data;
        };
        $data = isset($row['pid']) ? $parents($row['pid']) : [];
        array_unshift($data, $row);
        $breadcrumb_html = '';
        foreach (array_reverse($data) as $key => $value) {
            $breadcrumb_html .= isset($value['route']) ? ' <el-breadcrumb-item><a href="' . $value['route'] . '">' . $value['name'] . '</a></el-breadcrumb-item>' : '';
        }
        View::assign('breadcrumb_html', $breadcrumb_html);
    }
}

