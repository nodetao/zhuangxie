### 安装过程

环境：Nginx MySql  PHP  

下载源码放至站点目录，例：/var/www/

Nginx

server {
    listen 2323;
    server_name localhost;

    root /var/www/zhuangxie;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;  # 关键修改点
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

安装Mysql

sudo apt update                     #更新软件包
sudo apt install mysql-server       #安装Mysql

sudo mysql_secure_installation      #运行安全配置向导

1.设置 root 密码

2.移除匿名用户（选 Y）

3.禁止 root 远程登录（选 Y）

4.移除测试数据库（选 Y）

5.重新加载权限表（选 Y）

登录Mysql

sudo mysql -u root -p

创建新用户

CREATE USER '用户名'@'localhost' IDENTIFIED BY '密码';

创建数据库

CREATE DATABASE 数据库名;

授权用户

GRANT ALL PRIVILEGES ON 数据库名.* TO '用户名'@'localhost';
FLUSH PRIVILEGES;

添加root用户密码

ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '你的新密码';
FLUSH PRIVILEGES;
exit

安装PHP扩展

sudo apt install php7.4-fileinfo        #版本可以自行修改
安装后重启以使生效
sudo systemctl restart php7.4-fpm   # 根据你的PHP版本调整
# 或者
sudo systemctl restart php-fpm

安装 PHPSpreadsheet

# 下载并安装 Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 安装必需的 PHP 扩展（根据你的 PHP 版本调整），如果安装PHP时已安装扩展可忽略
sudo apt install php7.4-zip php7.4-xml php7.4-mbstring php7.4-gd   # PHP 7.4 示例
sudo systemctl restart php7.4-fpm

在项目中安装 PHPSpreadsheet 例：cd /var/www/zhuangxie
composer require phpoffice/phpspreadsheet    #安装命令
composer update phpoffice/phpspreadsheet     #更新命令


一切准备就绪，请确保：/var/www/zhuangxie/ 有写入权限


访问：https://域名/install.php  安装
