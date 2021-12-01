<?php

namespace addons\wangEditor;

use think\Addons;


class Plugin extends Addons
{
    public $info = [
        'name' => 'wangEditor',    // 插件标识
        'title' => 'wangEditor',    // 插件名称
        'description' => 'wangEditor编辑器',    // 插件简介
        'status' => 1,    // 状态
        'author' => 'byron tangyijun',
        'version' => '0.1'
    ];

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    public function wangEditor()
    {

        $plugin_script = '<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/wangeditor@latest/dist/wangEditor.min.js"></script>';
        $plugin_script .= '<script type="text/javascript" src="/static/js/jquery-3.3.1.min.js"></script>';
        $plugin_script .= '<script type="text/javascript">var wangEd;</script>';
        $upload_url = url('admin/general.upload/wangEditor');

        $plugin_func = <<<func
 onDialogOpen:function () {
        let _this = this
        setTimeout(function () {
            if(!wangEd){
                const E = window.wangEditor
                wangEd = new E("#wangEditor")
                wangEd.config.uploadImgServer = '{$upload_url}'
                wangEd.config.colors = [
    '#000000',
    '#eeece0',
    '#1c487f',
    '#4d80bf',
    '#e4e4e4',
    '#000000',
    
]
                wangEd.create()
            }    
        }, 300)
    },
 onEdit:function (row) {
        this.dialogFormVisible = true
        this.form = row
        setTimeout(function(){
            let field = \$("#wangEditor").attr('data-id')
            let str = "row."+field
            wangEd.txt.html(eval(str))
        },400);  
},
onSubmit:function (form) {
        let _this = this
        this.\$refs[form].validate((valid) => {
            if (valid) {
                let field = \$("#wangEditor").attr('data-id')
                let str = "_this.form."+field+"= wangEd.txt.html()"
                eval(str)
                axios.post(_this.api.post,_this.form).then(function (response) {
                    if(response.data.code == 200){
                        _this.\$message.success({message:response.data.msg,type:'success'})
                        _this.init()
                        _this.dialogFormVisible = false
                    }else{
                        _this.\$message.error(response.data.msg)
                    }
                })
            } else {
                return false
            }
        });
    },
    
func;
        return json_encode([

            'plugin_script' => $plugin_script,
            'plugin_func' => $plugin_func,
            'plugin_data' => [
                'Plugin_wang_editor' => new \stdClass()
            ]
        ]);
    }
}
