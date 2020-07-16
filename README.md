# aliyun-log-server

日志处理服务端，对接阿里云日志服务。

> 简单的框架实现，包含：路由、自动加载、异常处理、执行溯源、安全机制等。

```php
LogCenter::getInstance()->debug("日志信息", ["name"=>"BNDong"]);
```

# repository tree

```
├─ common
│  ├─ Function.php - 公共函数
│  └─ index.html
├─ config
│  ├─ Config.php - 基础配置
│  └─ index.html
├─ doc - 项目文档目录
├─ handler - 处理程序目录
├─ lib - 库目录
├─ logger - 核心目录
│  ├─ crypt - 加解密函数库
│  ├─ hook - 钩子组
│  ├─ vendor - 第三方类库
│  ├─ Config.php - 系统配置
│  ├─ Function.php - 系统函数
│  ├─ Handler.php - 基础 Handler
│  ├─ Hook.php - 系统钩子处理
│  ├─ Logger.php - 基础文件
│  ├─ Redis.php - 缓存 Redis
│  └─ index.html
├─ logs - 运行日志目录
├─ php-sdk - PHP SDK
├─ .gitignore
├─ .htaccess
├─ README.md
├─ doc.bat
├─ index.html
└─ servers.php - 入口文件
```

# implementation interface

实现接口，详情调用查看文档：

* 创建日志库
* 删除日志库
* 查询日志分布情况
* 获取投递任务列表
* 查询日志数据
* 获取日志库下分区信息
* 列出 project 下的所有日志库名称
* 获取单个分区的日志数据
* 写入日志

# log level

|level        |code        |
|:-----------:|:----------:|
|**debug**    |100|
|**info**     |200|
|**warn**     |300|
|**error**    |400|
|**fatal**    |500|

# log format

```json
{
    "client_id"  : "客户端ID",
    "log_level"  : "日志级别",
    "level_code" : "级别编码",
    "time"       : "时间",
    "message"    : "信息",
    "data"       : "数据",
    "backtrace"  : {
        "file"     : "日志发起文件",
        "line"     : "日志发起行号",
        "class"    : "日志发起当前类",
        "function" : "日志发起当前方法",
    }
}
```
