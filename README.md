## 初始化项目

```shell script
git clone https://github.com/curder/advanced-eloquent-demo.git && git checkout laracon2018

cd advanced-eloquent-demo && composer install -vvv

cp .env.example .env && php artisan key:generate
```

修改 `.env` 中的数据库配置信息。

## 填充数据
修改好数据库配置信息后，执行 `php artisan migrate:refresh --seed`，等待数据填充。

- 用户表 `users` 填充 3 条数据
- 顾客表 `customers` 填充 1000 条数据
- 公司表 `companies` 填充 1000 条数据
- 顾客互动表 `interactions` 填充 50000 条数据 

数据表单的关联关系：顾客表关联某公司，顾客表有很多互动记录。
```php                                   
protected $casts = [
    'birth_date' => 'date',
];

public function company() : \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(\App\Models\Company::class);
}

public function interactions() : \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Interaction::class);
}
```

## 通过用户名（姓+名）排序

```php
$customers = \App\Models\Customer::orderBy('last_name')->orderBy('first_name')->paginate();
```

优化后，使用模型 `scope` 代替：

```php
/**
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 */
public function scopeOrderByName(\Illuminate\Database\Eloquent\Builder $query): void
{
    $query->orderBy('last_name')->orderBy('first_name');
}
```
