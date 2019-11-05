# BandoriStation

The room number collection platform of Bang Dream! Girls Band Party!

邦邦车牌/房间收集平台

目前已接入的QQ群机器人：
- Tsugu (免费) 拉机器入群请联系QQ 1810972564
- mabo (收费) 购买机器人请见 https://mabo.ink/?help/purchase_agreement
- kkrbot
- 冲冲

## API说明

HTTP/HTTPS 请求地址 api.bandoristation.com，API支持GET请求，参数通过URL参数传入，响应数据为JSON格式

## 查询房间数据

### HTTP/HTTPS

#### 参数

| 字段名 | 数据类型 | 填入值 | 说明 |
| --- | --- | --- | --- |
| function | string | query_room_number |
| latest_time | number | 13位时间戳 | 选填参数，填入之后将不返回该时间之前的数据 |

#### 响应数据

| 字段名 | 数据类型 | 说明 |
| --- | --- | --- |
| status | string | success/failure |
| response | array/string | status为success时返回数据array，为failure时返回原因 |

#### 房间数据

房间数据为字段response类型为array时的单条数据

| 字段名 | 数据类型 | 说明 |
| --- | --- | --- |
| number | number | 房间号 |
| raw_message | string | 房间的说明文字/原始信息 |
| source_info | array | 数据来源信息 |
| type | string | 房间类型 |
| time | number | 房间的发布时间，13位时间戳 |
| user_info | array | 用户信息 |

source_info:

| 字段名 | 数据类型 | 说明 |
| --- | --- | --- |
| name | string | 数据源名称 |
| type | string | 数据源类型 |

user_info:

| 字段名 | 数据类型 | 说明 |
| --- | --- | --- |
| type | string | 用户类型 |
| user_id | number | 用户ID |
| username | string | 用户名 |
| avatar | string | 用户头像 |

### WebSocket

请求地址 wss://api.bandoristation.com:50443 ，本接口将会定时返回房间数据，数据格式与HTTP/HTTPS接口的返回数据相同

## 提交房间数据

目前仅提供HTTP/HTTPS接口，接口仅对其他邦邦房间数据平台（机器人或者网站）开放，不对个人开放。想要接入可以联系QQ 2287477889，如果加不了好友也可以发送邮件给maborosh@qq.com，我会主动联系你

#### 参数

| 字段名 | 数据类型 | 填入值 | 说明 |
| --- | --- | --- | --- |
| function | string | submit_room_number |
| number | number | 房间号 | 5-6位整数 |
| user_id | number | 用户的唯一识别码 | QQ号或者其他ID |
| raw_message | string | 房间的说明文字/原始信息 | 如果使用GET方式传入需要URL编码 |
| source | string | 房间数据来源 | 机器人或者平台名称，如果是中文或者其他非英文、数字的字符并且使用GET方式传入需要URL编码 |
| token | string | 口令 |
| type | string | 25|18|12|7|other | 选填参数，房间类型 |

#### 响应数据

| 字段名 | 数据类型 | 说明 |
| --- | --- | --- |
| status | string | success/failure |
| response | string | status为success时返回空字符串，为failure时返回原因 |
