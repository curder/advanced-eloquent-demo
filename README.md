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

## 添加公司名

通过关联关系获取公司名：

```html
<!-- table header -->
<th>Company</th>

<!-- table body -->
<td>{{ $customer->company->name }}</td>
```

优化：懒加载关联公司

```
$customers = Customer::with('company')->orderByName()->paginate();
```

## 添加顾客互动表中最后的时间记录

- 通过关联关系获取数据

    ```html
    <!-- table header -->
    <th>Last Interaction</th>

    <!-- table body -->
    <td>{{ $customer->interactions->sortByDesc('created_at')->first()->created_at->diffForHumans() }}</td>
    ```

    添加查询关联关系
    ```php
    $customers = \App\Models\Customer::with('company', 'interactions')->orderByName()->paginate();
    ```

- 通过数据库查询

    ```html
    <td>{{ $customer->interactions()->latest()->first()->created_at->diffForHumans() }}</td>
    ```

    修改查询关联关系
    ```
    $customers = Customer::with('company')->orderByName()->paginate();
    ```

- 通过子查询

    ```html
    <td>{{ $customer->last_interaction_date->diffForHumans() }}</td>
    ```

    添加查询`scope`
    ```php
    public function scopeWithLastInteractionDate(\Illuminate\Database\Eloquent\Builder $query)
    {
        $subQuery = \DB::table('interactions')
            ->select('created_at')
            ->whereRaw('customer_id = customers.id')
            ->latest()
            ->limit(1);

        return $query->select('customers.*')->selectSub($subQuery, 'last_interaction_date');
    }
    ```

    修改查询语句
    ```php
    $customers = \App\Models\Customer::with('company')
        ->withLastInteractionDate()
        ->orderByName()
        ->paginate();
    ```
    修改模型`casts`属性
    ```php
    protected $casts = [
        'birth_date' => 'date',
        'last_interaction_date' => 'datetime',
    ];
    ```

- 通过子查询（优化）

    修改 `scope`
    ```php
    public function scopeWithLastInteractionDate($query)
    {
        $query->addSubSelect('last_interaction_date', \App\Models\Interaction::select('created_at')
            ->whereRaw('customer_id = customers.id')
            ->latest()
        );
    }
    ```

    在 `app\Providers\AppServiceProvide.php` 的 `boot`方法中添加 `macro`：

    ```php
    use Illuminate\Database\Eloquent\Builder;

    Builder::macro('addSubSelect', function ($column, $query) {
        if (is_null($this->getQuery()->columns)) {
            $this->select($this->getQuery()->from.'.*');
        }

        return $this->selectSub($query->limit(1)->getQuery(), $column);
    });
    ``` 
