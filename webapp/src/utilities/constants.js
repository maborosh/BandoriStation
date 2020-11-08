
export const SERVER_URL = 'https://server.bandoristation.com'
export const API_URL = 'https://api.bandoristation.com'
export const WEBSOCKET_API_URL = 'wss://api.bandoristation.com'
export const ASSETS_URL = 'https://asset.bandoristation.com'

export const ERROR_CODE_DEFINITION = {
  'Not allowed': '不允许的操作',
  'Unparsable format': '无法解析的上报格式',
  'Missing Parameters': '缺少上报参数',
  'Missing Parameter "function"': '缺少功能参数',
  'Undefined function group': '缺少功能组参数',
  'Undefined function': '功能未定义',
  'Forbidden method': '禁止的上报方法',
  'Undefined access token': '访问令牌未定义',
  'Token validation failure': '令牌校验失败',
  'Nonexistent user': '用户不存在',
  'Undefined email': '未找到邮箱，请重新设置邮箱地址',
  'Duplicate email': '邮箱已被注册',
  'Invalid email': '无效的邮箱地址',
  'Verified email': '邮箱已被验证',
  'Undefined verification code': '验证码未定义',
  'Invalid verification code': '验证码错误，请重新输入',
  'Too many logins': '登陆次数过多，请稍后再试',
  'Wrong password': '密码错误',
  'Too many signups': '注册次数过多，请稍后再试',
  'Username or email already exists': '用户名或邮箱已被注册',
  'Username already exists': '用户名已经存在',
  'QQ already exists': '该QQ号已经被绑定',
  'Undefined verification request': '未找到验证请求'
}

export const BANNED_ROOM_NUMBER_PATTERN = [
  /^(\d)\1+$/, /^114514$/, /^11451$/, /^14514$/, /^415411$/, /^15411$/
]

export const BANNED_ROOM_NUMBER_DESCRIPTION_WORD = 'WyJcdTkxY2VcdTUxN2QiLCJcdTUxN2RcdTkxY2UiLCJcdTdjYWEiLCJcdTgxZWQiLCJubXNsIiwiXHU0ZjYwXHU1OTg4XHU2YjdiXHU0ZTg2IiwiXHU2NzQwIiwiXHU4MWVhXHU2MTcwIiwiXHU2NjBmXHU3NzYxI\n' +
  'iwiXHU5NmMwIiwiXHU3MzFkXHU2YjdiIiwiXHU4YzAzXHU2MjBmIiwiXHU5MWNlXHU3Mzc4IiwiMTkxOSIsIlx1NGY2MFx1OWE2YyIsIlx1NWI2NFx1NTEzZiIsIlx1NzAzNSIsIlx1N2NkZSIsIlx1NGY2MFx1NT\n' +
  'QxNyIsIlx1NjcwOVx1NzVjNSJd'
