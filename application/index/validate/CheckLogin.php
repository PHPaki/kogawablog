<?php
namespace app\index\validate;

use think\Validate;

class CheckLogin extends Validate
{
    //验证规则
    protected $rule = [
        'name|用户名'=>'require|length:3,12',
        'password|密码'=>'require|length:6,32',
        'valCode'=>'require|captcha',
        'repassword|再次输入密码'=>'confirm:password'
    ];

    protected $scene = [
        'login' => ['name', 'password'],
        'register' =>['name'=>'require|length:3,12|unique:user', 'password', 'repassword'],
    ];


}