import store from '@/store'
import { ERROR_CODE_DEFINITION } from '@/utilities/constants'

export function notify (payload) {
  store.commit('modal/notice/pushNotice', payload)
}

export function generateRequestHeader (token) {
  return { 'Auth-Token': token }
}

export function handleAPIResponse (response, success, failure) {
  if (response.data.status === 'success') {
    if (response.data.response !== undefined) {
      success(response.data.response)
    } else {
      notify({ content: '未知错误' })
    }
  } else {
    if (failure) {
      failure()
    } else {
      let notice
      if (ERROR_CODE_DEFINITION[response.data.response]) {
        notice = ERROR_CODE_DEFINITION[response.data.response]
      } else {
        notice = response.data.response !== undefined
          ? response.data.response
          : '未知错误'
      }
      notify({ content: notice })
    }
  }
}

export function buttonHold (interval, setCountDownText, afterHold) {
  const timerId = setInterval(() => {
    interval -= 1
    if (interval <= 0) {
      clearInterval(timerId)
      afterHold()
    } else {
      setCountDownText('(' + interval + ')')
    }
  }, 1000)
}
