<?php

namespace backend\fields;

/**
 * Class Menu
 * @package extend\backend\fields
 * 定义菜单字段，渲染Form
 */
class Menu
{
    /**
     * @var bool
     * 是否开启树形表格
     */
   const IS_TREE_TABLE = true;

    const FORM_FIELD = [
        [
            'type' => 'input',
            'label' => '菜单名称',
            'key' => 'name',
            'value' => '',
            'placeholder' => '请输入菜单名称',
            'prop' => [
                'table_show' => true, //table 是否显示列
                'search' => 'like', //是否查询
                'is_null' => true,//是否必填
                'trigger' => 'blur'
            ]
        ],
        [
            'type' => 'select',
            'label' => '父级菜单',
            'key' => 'pid',
            'value' => '',
            'placeholder' => '选择父级菜单',
            'prop' => [
                'table_show' => true,
                'search' => '=',
                'is_null' => true,
                'trigger' => 'change',
                'filterable' => true,
                'callback' => ['\\app\\admin\\model\\auth\\Menu', 'getPidSelect','id','name'],
            ]
        ],
        [
            'type' => 'input',
            'label' => 'Icon',
            'key' => 'icon',
            'value' => '',
            'placeholder' => '请输入菜单图标',
            'prop' => [
                'table_show' => true,
                'search' => 'like',
                'is_null' => false,
                'trigger' => 'blur',
            ]
        ],
        [
            'type' => 'input',
            'label' => '路由',
            'key' => 'route',
            'value' => '',
            'placeholder' => '请输入路由规则',
            'prop' => [
                'table_show' => true,
                'search' => 'like',
                'is_null' => true,
                'trigger' => 'blur',
            ]
        ],
        [
            'type' => 'number',
            'label' => '权重',
            'key' => 'weigh',
            'value' => 1,
            'prop' => [
                'table_show' => true,
                'search' => 'like',
                'is_null' => false,
                'ext' => [
                    'min' => 0,
                    'max' => 9999999,
                    'step' => 1,
                ]
            ]
        ],
        [
            'type' => 'radio',
            'label' => '显示',
            'key' => 'show',
            'value' => 1,
            'prop' => [
                'table_show' => true,
                'search' => 'like',
                'is_null' => true,
                'trigger' => 'blur',
                'callback' => ['\\app\\admin\\model\\auth\\Menu', 'getShowRadio','id','name'],
            ]
        ],
        [
            'type' => 'radio',
            'label' => '子菜单',
            'key' => 'son',
            'value' => 1,
            'prop' => [
                'table_show' => true,
                'search' => 'like',
                'is_null' => true,
                'trigger' => 'change',
                'callback' => ['\\app\\admin\\model\\auth\\Menu', 'getSonRadio','id','name'],
            ]
        ],
    ];
}
