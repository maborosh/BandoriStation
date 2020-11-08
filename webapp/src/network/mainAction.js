import { request } from '@/network/request'

const functionGroup = 'MainAction'

export function initializeAccountSetting (headers) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'initializeAccountSetting'
    }
  })
}

export function getRoomNumberFilter (headers) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'getRoomNumberFilter'
    }
  })
}

export function updateRoomNumberFilter (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'updateRoomNumberFilter',
      ...data
    }
  })
}

export function informUser (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'informUser',
      ...data
    }
  })
}
