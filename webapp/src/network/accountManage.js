import { request } from '@/network/request'

const functionGroup = 'AccountManage'

export function getInitialData (headers) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'getInitialData'
    }
  })
}

export function updateAvatar (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'updateAvatar',
      ...data
    }
  })
}

export function updateUsername (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'updateUsername',
      ...data
    }
  })
}

export function updatePassword (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'updatePassword',
      ...data
    }
  })
}

export function updateEmailSendVerificationCode (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'updateEmailSendVerificationCode',
      ...data
    }
  })
}

export function updateEmailVerifyEmail (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'updateEmailVerifyEmail',
      ...data
    }
  })
}

export function bindQQ (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'bindQQ',
      ...data
    }
  })
}
