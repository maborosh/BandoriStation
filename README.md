# BandoriStation

The room number collection platform of Bang Dream! Girls Band Party!

邦邦车牌收集平台

## API说明

HTTP/HTTPS 请求地址 api.bandoristation.com, API支持GET请求，参数通过URL参数传入，响应数据为JSON格式。

## 查询车牌

### HTTP/HTTPS

'''
/?function=query_room_number
'''

#### 参数

| 字段名 | 数据类型 | 说明 |
| ---- | --- | ------- |
| latest_time | 13位时间戳 | 选填参数，填入之后将不返回该时间之前的数据 |
