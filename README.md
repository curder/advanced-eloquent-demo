## 初始化项目

```shell script
git clone https://github.com/curder/advanced-eloquent-demo.git

cd advanced-eloquent-demo && composer install -vvv

cp .env.example .env && php artisan key:generate
```

修改`.env`中的数据库配置信息。

## 填充数据

修改好数据库配置信息后，执行 `php artisan migrate:refresh --seed`，等待数据填充。

- 用户表 `users` 填充 1000 条记录
- 俱乐部表 `clubs` 填充 4 条记录
- 好友表 `buddies` 填充 8 条记录
- 旅行表 `trips` 填充 30000 条记录

数据表单的关联关系：用户属于1个俱乐部，用户由多个旅行记录，用户由多个好友。

## 
