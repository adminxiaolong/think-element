<?php

namespace element\component\ui;

class Datetime
{
    public static function html(array $fields,string $form_name = 'form'): string
    {
        $html = '<el-form-item prop="'.$fields['key'].'" label="'.$fields['label'].'">';
        $html .= '<el-date-picker
      v-model="'.$form_name.'.'.$fields['key'].'"
      type="datetime"
     placeholder="'.$fields['placeholder'].'"
      format="YYYY-MM-DD HH:mm:ss"
      value-format="YYYY-MM-DD HH:mm:ss"
    >
    </el-date-picker>';
        $html .=   '</el-form-item>';
        return  $html;
    }
}
