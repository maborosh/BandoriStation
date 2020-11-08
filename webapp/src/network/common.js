import { requestAPI } from '@/network/request'

export function getOnlineNumber () {
  return requestAPI({
    data: {
      function: 'getOnlineNumber'
    }
  })
}
