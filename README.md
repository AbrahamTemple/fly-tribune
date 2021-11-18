# layui+Thinkphp论坛社区项目

## 注意

项目开发使用的是Apache，因此隐藏index.php的配置如下

- public的.htaccess文件

``` c++
<IfModule mod_rewrite.c>
    Options +FollowSymlinks -Multiviews
    RewriteEngine on

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php?/$1 [QSA,PT,L]
</IfModule>
```

如果使用Nginx请翻阅其它配置说明
