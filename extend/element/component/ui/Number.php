<?php

namespace element\component\ui;

class Number
{
    public static function html(array $fields,string $form_name = 'form'): string
    {
        $min = $fields['prop']['ext']['min'] ?? 1;
        $max = $fields['prop']['ext']['max'] ?? 999999;
        $step = $fields['prop']['ext']['step'] ?? 1;
        return
            '<el-form-item prop="' . $fields['key'] . '" label="' . $fields['label'] . '">' . PHP_EOL .
            '<el-input-number ' . PHP_EOL .
            ':min="' . $min . '" ' . PHP_EOL .
            ':max="' . $max . '" ' . PHP_EOL .
            ':step = "' . $step . '"' . PHP_EOL .
            'v-model="' . $form_name . '.' . $fields['key'] . '">' . PHP_EOL .
            '</el-input-number>' . PHP_EOL .
            '</el-form-item>' . PHP_EOL;
    }
}
