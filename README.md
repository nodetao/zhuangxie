<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装指南</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #2980b9;
            margin-top: 30px;
        }
        h3 {
            color: #16a085;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        code {
            font-family: Consolas, Monaco, 'Andale Mono', monospace;
            background-color: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
            color: #c7254e;
        }
        .note {
            background-color: #e7f5fe;
            border-left: 4px solid #3498db;
            padding: 10px 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>安装指南</h1>

    <h2>环境要求</h2>
    <ul>
        <li>Nginx</li>
        <li>MySQL</li>
        <li>PHP</li>
    </ul>

    <h2>1. 源码部署</h2>
    <p>将源码下载并放置到站点目录，例如：</p>
    <pre><code>/var/www/</code></pre>

    <h2>2. Nginx 配置</h2>
    <p>以下是一个示例配置，请根据实际情况修改：</p>
    <pre><code>server {
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
}</code></pre>

    <h2>3. MySQL 安装与配置</h2>

    <h3>安装 MySQL</h3>
    <pre><code>sudo apt update
sudo apt install mysql-server</code></pre>

    <h3>安全配置</h3>
    <pre><code>sudo mysql_secure_installation</code></pre>
    <p>按照提示完成以下设置：</p>
    <ol>
        <li>设置 root 密码</li>
        <li>移除匿名用户（选 Y）</li>
        <li>禁止 root 远程登录（选 Y）</li>
        <li>移除测试数据库（选 Y）</li>
        <li>重新加载权限表（选 Y）</li>
    </ol>

    <h3>数据库设置</h3>
    <p>登录 MySQL：</p>
    <pre><code>sudo mysql -u root -p</code></pre>

    <p>执行以下 SQL 命令：</p>
    <pre><code>-- 创建新用户
CREATE USER '用户名'@'localhost' IDENTIFIED BY '密码';

-- 创建数据库
CREATE DATABASE 数据库名;

-- 授权用户
GRANT ALL PRIVILEGES ON 数据库名.* TO '用户名'@'localhost';
FLUSH PRIVILEGES;

-- 设置 root 用户密码
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '你的新密码';
FLUSH PRIVILEGES;
exit</code></pre>

    <h2>4. PHP 扩展安装</h2>
    <pre><code>sudo apt install php7.4-fileinfo  # 版本可自行调整
sudo systemctl restart php7.4-fpm  # 根据 PHP 版本调整
# 或
sudo systemctl restart php-fpm</code></pre>

    <h2>5. PHPSpreadsheet 安装</h2>

    <h3>安装 Composer</h3>
    <pre><code>curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer</code></pre>

    <h3>安装 PHP 扩展（如未安装）</h3>
    <pre><code>sudo apt install php7.4-zip php7.4-xml php7.4-mbstring php7.4-gd  # PHP 7.4 示例
sudo systemctl restart php7.4-fpm</code></pre>

    <h3>安装 PHPSpreadsheet</h3>
    <p>进入项目目录：</p>
    <pre><code>cd /var/www/zhuangxie</code></pre>
    <p>执行以下命令：</p>
    <pre><code>composer require phpoffice/phpspreadsheet  # 安装
composer update phpoffice/phpspreadsheet  # 更新</code></pre>

    <h2>6. 权限设置</h2>
    <p>确保 <code>/var/www/zhuangxie/</code> 目录有写入权限。</p>

    <h2>7. 完成安装</h2>
    <p>访问以下 URL 开始安装：</p>
    <pre><code>https://域名/install.php</code></pre>

    <div class="note">
        <strong>注意：</strong>请根据实际环境调整版本号和路径。
    </div>
</body>
</html>
