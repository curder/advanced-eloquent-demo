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

```php   
/**
 * 用户与俱乐部表关联关系，用户依附于俱乐部
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function club()
{
    return $this->belongsTo(Club::class);
}

/**
 * 用户与旅行表，用户有多次旅行
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function trips()
{
    return $this->hasMany(Trip::class);
}

/**
 * 用户与好友表，用户有多个好友
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
 */
public function buddies()
{
    return $this->belongsToMany(__CLASS__, 'buddies', 'user_id', 'buddy_id')->withTimestamps();
}
```

## 过滤用户关联信息

获取当前用户所在俱乐部或好友表列表信息。

优化前，使用`Policy`：

```php
$query->where('club_id', $user->club_id)
      ->orWhereIn('id', $user->buddies->pluck('id'));
```

使用`Policy`方式，使用命令 `php artisan make:policy UserPolicy` 创建对应`UserPolicy`：

```php
public function view(App\Models\User $user, App\Models\User $other) {
    return $user->club_id === $other->club_id || $user->buddies->contains($other);
}
```

修改数据调用

```php
     ->get()
     ->filter(function ($user) {
         return Auth::user()->can('view', $user);
     })
```

优化后，创建 `Scope`：

在 `User` 模型中创建 `visibleTo`：

```php

    /**
     * 用户对数据的可见性
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User                   $user
     */
    public function scopeVisibleTo(\Illuminate\Database\Eloquent\Builder $query, App\Models\User $user) : void
    {
        $query->where(function ($query) use ($user) {
            $query->where('club_id', $user->club_id)
                  ->orWhereIn('id', $user->buddies->pluck('id'));
        });
    }
```
        
修改数据调用      
```php
    ->visibleTo(\Illuminate\Support\Facades\Auth::user())
```

## 数据排序

根据 `buddies.buddy_id` 关联正序排列。添加 `scopeOrderByBuddiesFirst` 参数

```php
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User    $user
     */
    public function scopeOrderByBuddiesFirst(\Illuminate\Database\Eloquent\Builder $query, \App\Models\User $user) : void
    {
        $query->orderBySub(function ($query) use ($user) {
            $query->selectRaw('true')
                  ->from('buddies')
                  ->whereColumn('buddies.buddy_id', 'users.id')
                  ->where('user_id', $user->id)
                  ->limit(1);
        });
    }
```

修改数据调用
```php
  ->orderByBuddiesFirst(Auth::user())
  ->orderBy('name')
```

这样调用到的数据库查询，包含 `Buddy` 的用户排序在列表最后。

## 关联旅行的最后日期

```html      
<!-- header -->
<div class="w-1/3 px-3 pt-6 pb-3 text-2xl text-green-600 font-semibold">Last Trip</div>

<!-- body -->
<div class="w-1/3 px-3 py-4 text-gray-800">{{ $user->trips->sortByDesc('went_at')->first()->went_at->diffForHumans() }}</div>
```

如上产生了大量额外的数据库查询。

优化：
```html
<div class="w-1/3 px-3 py-4 text-gray-800">{{ $user->trips()->latest('went_at')->first()->went_at->diffForHumans() }}</div>
```

调用：
```php
$users = \App\Models\User::with('club', 'trips')
```

再优化：
```html
<div class="w-1/3 px-3 py-4 text-gray-800">{{ $user->last_trip_at->diffForHumans() }}</div>
```                                                                                        
编写`scope`
```php
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeWithLastTripDate(\Illuminate\Database\Eloquent\Builder $query) : void
    {
        $query->addSubSelect('last_trip_at', function ($query) {
            $query->select('went_at')
                  ->from('trips')
                  ->whereColumn('user_id', 'users.id')
                  ->latest('went_at')
                  ->limit(1);
        });
    }
```  

配置`casts`属性：
```php
    'last_trip_at' => 'datetime',
```

调用：
```php              
$users = User::with('club')
    ->withLastTripDate()
```

## 关联最后旅行数据

```html
<div class="w-1/3 px-3 py-4 text-gray-800">
    {{ $user->last_trip_at->diffForHumans() }}
    <span class="text-sm text-gray-600">({{ $user->last_trip_lake }})</span>
</div>
```

编写`scope`
```php
   /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeWithLastTripLake(\Illuminate\Database\Eloquent\Builder $query) : void
    {
        $query->addSubSelect('last_trip_lake', function ($query) {
            $query->select('lake')
                  ->from('trips')
                  ->whereColumn('user_id', 'users.id')
                  ->latest('went_at')
                  ->limit(1);
        });
    }
```

查询调用
```php
->withLastTripLake()
```

**添加动态关联关系**

```php
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lastTrip()
    {
        return $this->belongsTo(\App\Models\Trip::class);
    }               

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeWithLastTrip(\Illuminate\Database\Eloquent\Builder $query)
    {
        $query->addSubSelect('last_trip_id', function ($query) {
            $query->select('id')
                  ->from('trips')
                  ->whereColumn('user_id', 'users.id')
                  ->latest('went_at')
                  ->limit(1);
        })->with('lastTrip');
    }
```

模版渲染：
```html
    <div class="w-1/3 px-3 py-4 text-gray-800">
        {{ $user->lastTrip->went_at->diffForHumans() }}
        <span class="text-sm text-gray-600">({{ $user->lastTrip->lake }})</span>
    </div>
```

调用：
```php
 $users = User::with('club')
                ->withLastTrip()
```
