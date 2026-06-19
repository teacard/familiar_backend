<?php

return [
    'required' => ':attribute 為必填欄位',
    'string' => ':attribute 須為字串',
    'integer' => ':attribute 須為整數',
    'numeric' => ':attribute 須為數字',
    'boolean' => ':attribute 須為布林值',
    'email' => ':attribute 格式不正確',
    'max' => [
        'string' => ':attribute 不可超過 :max 個字元',
    ],
    'min' => [
        'string' => ':attribute 至少須 :min 個字元',
    ],
    'in' => ':attribute 的值不在允許範圍內',
    'unique' => ':attribute 已被使用',
    'exists' => ':attribute 不存在',
    'confirmed' => ':attribute 與確認欄位不符',
    'nullable' => ':attribute 可為空值',
    'sometimes' => ':attribute 格式錯誤',

    'attributes' => [],
];
