# 定时任务
php think crontab



# think-admin
# ENV
- php >= 7.1.3
- mysql >= 5.5

# install
- curl -sS http://install.phpcomposer.com/installer | php
- composer config -g repo.packagist composer https://packagist.laravel-china.org
- composer update 
- 修改根目录下 .env.emp .env
- .env 配置数据库信息
- php think migrate:run
- php think seed:run

# Use
- 配置虚拟域名 OR 在根目录下执行 php think run
- yourUrl/login
- 默认用户名 admin 密码 admin

# nginx 配置
```
server {
        listen       端口;
        server_name  域名;

        access_log  logs/wenwen.access.log;

        root 项目目录/public;
        index index.php index.html index.htm;

        location / {
            index  index.php index.html index.htm;

            if (!-e $request_filename) {
                rewrite ^(.*)$ /index.php?s=$1 last;
                break;
            }
        }

        #error_page  404              /404.html;
        error_page   500 502 503 504  /50x.html;
        location = /50x.html {
            root   html;
        }

        location ~ \.php$ {
            root           项目目录/public;
            fastcgi_pass   phpfastcgi;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }

        location ^~ /data {
                deny all;
        }
    }

```
# Problem
> SQLSTATE[42000]: Syntax error or access violation: 1067 Invalid default value for 'updated_at'

设置 sql_mode;
```
show variables like 'sql_mode' ; 
```
> remove 'NO_ZERO_IN_DATE,NO_ZERO_DATE'
```
SET GLOBAL sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'
```