#!/usr/bin/php
<?php
PHP_SAPI==='cli' or die(!ob_end_clean());

require 'Zend/autoload.php';//FIXME 如果把api的子类也做成库，就不需要反复require了。而且项目update时，并不会下载库里的vendor

$http = new Swoole\Http\Server('127.0.0.1', 9501);//FIXME 允许命令行参数设置！！！

$http->on('request', function ($request, $response){

  //这个无须重置
  $_SERVER += array_change_key_case($request->server,CASE_UPPER);

  //把Swoole非标准的请求头导入PHP标准的$_SERVER变量，一定要在结束时把这些新增的key删除！！！
  foreach($request->header as $k=>$v) $_SERVER['HTTP_'.strtoupper(str_replace('-','_',$k))] = $v;
  
  $obj = new xmlscript\ping;//FIXME 这里不能硬编码！！！

  $str = $obj();

  $response->status(http_response_code());//魔术方法之后才能取得http_response_code

  foreach($obj->headers_list() as $k=>$v) $response->header($k,$v);//导出响应头

  $response->end($str);

  //必须同时删除上次的请求头，而且code回归200
  foreach($_SERVER as $k=>$v) if(stripos($k,'HTTP_')===0) unset($_SERVER[$k]);
  http_response_code(200);

});
$http->start();
