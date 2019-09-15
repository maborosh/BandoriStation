# BandoriStation

The room number collection platform of Bang Dream! Girls Band Party!

邦邦车牌/房间收集平台

QQ群内机器人接入：
- Tsugu (免费) 拉群前请联系QQ 1810972564
- mabo (收费) 购买机器人请见 https://mabo.ink/?help/purchase_agreement

## API说明

HTTP/HTTPS 请求地址 api.bandoristation.com，API支持GET请求，参数通过URL参数传入，响应数据为JSON格式

## 查询房间数据

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
| status | string | success/failure |
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

请求地址 wss://api.bandoristation.com:50443 ，本接口将会定时返回房间数据，数据格式与HTTP/HTTPS接口的返回数据相同

## 提交房间数据

目前仅提供HTTP/HTTPS接口，接口仅对其他邦邦房间数据平台（机器人或者网站）开放，不对个人开放。想要接入可以联系QQ 2287477889，如果加不了好友也可以发送邮件给2287477889@qq.com，我会主动联系你

```
/?function=submit_room_number
```

#### 参数

| 字段名 | 数据类型 | 说明 |
| ---- | --- | ------- |
| number | number | 房间号 |
| user_id | number | 用户的唯一识别码 |
| raw_message | string | 房间的说明文字/原始信息 |
| source | string | 房间数据来源 |
| token | string | 口令 |
| type | number | 房间类型，选填参数，可选项为25、18、12、7 |

#### 响应数据

| 字段名 | 数据类型 | 说明 |
| ---- | --- | ------- |
| status | string | success/failure |
| response | string | status为success时返回空，为failure时返回原因 |
