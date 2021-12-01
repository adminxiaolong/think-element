<?php
// +----------------------------------------------------------------------
// | Created by [ PhpStorm ]
// +----------------------------------------------------------------------
// | Copyright (c) 2021-2022.
// +----------------------------------------------------------------------
// | Create Time (2021/11/26 - 10:27 上午)
// +----------------------------------------------------------------------
// | Author: 唐轶俊 <tangyijun@021.com>
// +----------------------------------------------------------------------
namespace element\component\ui;

class WangEditor
{
    public static function html(array $fields,string $form_name = 'form'): string
    {
        return
            '<el-form-item prop="'.$fields['key'].'" label="'.$fields['label'].'">'.PHP_EOL.
            ' <div id="wangEditor" data-id="'.$fields['key'].'">'.PHP_EOL.
            '</div></el-form-item>';
    }
}