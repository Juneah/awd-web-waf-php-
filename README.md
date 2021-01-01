# Awd-Waf
## 前言
原来是校内awd比赛时想使用的,很遗憾没来得及用上就被打爆了。
## 日志功能
记录所有请求，包括：时间、IP、请求文件、请求方法COOKIES、GET、POST、FILE_NAME,可自定义日志存储路径,默认为web路径下jlog.txt。
## 配置
可修改waf等级,是否开启日志、Waf等功能,以及日志路径，在文件开头修改即可
```
#等级
$level=3;
#日志功能
$log_status=1;
#waf功能
$waf_status=1;
#定义日志路径(默认为web路径下jlog.txt)
$log_path=Dir.'/jlog.txt';
```
## Waf功能
### level-1 
对以下请求addslashes和htmlspecialchars防御sql注入和xss
```
$_GET、$_POST、$_REQUEST、$_COOKIE、$_SERVER、
```
### level-2
在level1的基础上进行替换危险字符
```
/select\b|insert\b|flag\b|union\b|<\?\b|\?>|update\b|drop\b|and\b|delete\b|dumpfile\b|outfile\b|load_file|rename\b|`|\.\/|floor\(|extractvalue|updatexml|name_const|multipoint\(|base64_decode|eval\(|assert\(|file_put_contents|fwrite|curl|system|passthru|exec|system|chroot|scandir|chgrp|chown|shell_exec|proc_open|proc_get_status|popen|ini_alter|ini_restorei/i
```
### level-3
在level-1和2的基础上对文件上传进行防御
```
#修改上传文件名防止sql注入
$_FILES[*]['name']=mt_rand().'.jpg';
#修改临时文件名为空使其上传失败
$_FILES[*]['tmp_name']='';
```
### level-4
替换所有变量的值,将符号部分替换为空只留字符,不是替换文件内容,而且将有值的变量进行替换,并不会修改文件内容,这个等级可能会让功能损坏如:
```
$host='127.0.0.1';
```
被替换后等于
```
$host='127001';
```
## 使用
* 将 waf 文件直接复制到 web 网站目录下，请保证此文件具有可执行权限，确保写入日志的目录有写入文件权限
* 将waf文件包含在index.php（基于MVC的php程序，包含在第一条），或者config.php中（即尽量让所有文件都能包含此文件，且包含在文件开头，先于程序执行）

## 批量上/下waf
先将waf移动到web目录下这里以/var/www/html为例, 要使用 waf 所在的绝对路径并注意转义
* 上
```
find  -path /var/www/html -prune -o -type f -name "*.php" -print |  xargs sed -i "s/<?php/<?php include_once(\"\/var\/www\/html\/waf.php\");/g"
```
* 下
```
find  -path /var/www/html -prune -o -type f -name "*.php" -print | xargs sed -i "s/<?php include_once(\"\/var\/www\/html\/waf.php\");/<?php/g"
```
