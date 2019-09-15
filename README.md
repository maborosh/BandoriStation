# BandoriStation

The room number collection platform of Bang Dream! Girls Band Party!

邦邦车牌收集平台

## API说明

HTTP/HTTPS 请求地址 api.bandoristation.com，API支持GET请求，参数通过URL参数传入，响应数据为JSON格式。

## 查询车牌

### HTTP/HTTPS

```
/?function=query_room_number
```

#### 参数

| 字段名 | 数据类型 | 说明 |
| ---- | --- | ------- |
| latest_time | number | 选填参数，填入之后将不返回该时间之前的数据，格式为13位时间戳 |

#### 响应数据

| 字段名 | 数据类型 | 说明 |
| ---- | --- | ------- |
| status | string | success / failure |
| response | array/string | status为success时返回数据array，为failure时返回string |

#### 房间数据

房间数据为字段response类型为array时的单条数据

| 字段名 | 数据类型 | 说明 |
| ---- | --- | ------- |
| number | number | 房间号 |
| type | string | 房间类型 |
| user_id | number | 用户的唯一识别码 |
| username | string | 用户名 |
| raw_message | string | 房间的说明文字/原始信息 |
| source | string | 房间数据来源 |
| time | number | 房间的发布时间，13位时间戳 |

### WebSocket

请求地址 wss://api.bandoristation.com:50443 
