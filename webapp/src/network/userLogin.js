import { request } from '@/network/request'

const functionGroup = 'UserLogin'

export function login (data) {
  return request({
    data: {
      function_group: functionGroup,
      function: 'login',
      ...data
    }
  })
}

export function logout (headers) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'logout'
    }
  })
}

export function signup (data) {
  return request({
    data: {
      function_group: functionGroup,
      function: 'signup',
      ...data
    }
  })
}

export function getCurrentEmail (headers) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'getCurrentEmail'
    }
  })
}

export function changeEmail (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'changeEmail',
      ...data
    }
  })
}

export function sendEmailVerificationCode (headers) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'sendEmailVerificationCode'
    }
  })
}

export function verifyEmail (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'verifyEmail',
      ...data
    }
  })
}

export function resetPasswordSendEmailVerificationCode (data) {
  return request({
    data: {
      function_group: functionGroup,
      function: 'resetPasswordSendEmailVerificationCode',
      ...data
    }
  })
}

export function resetPasswordVerifyEmail (data) {
  return request({
    data: {
      function_group: functionGroup,
      function: 'resetPasswordVerifyEmail',
      ...data
    }
  })
}

export function resetPassword (headers, data) {
  return request({
    headers,
    data: {
      function_group: functionGroup,
      function: 'resetPassword',
      ...data
    }
  })
}
