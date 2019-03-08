# aliyun-log-server
🌏 aliyun log processing server.
<br>日志处理服务端，对接阿里云日志服务。

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