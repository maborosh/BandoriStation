import axios from 'axios'
import { SERVER_URL, API_URL } from '@/utilities/constants'

export function request (config) {
  const instance = axios.create({
    baseURL: SERVER_URL,
    method: 'POST',
    timeout: 5000
  })
  return instance(config)
}

export function requestAPI (config) {
  const instance = axios.create({
    baseURL: API_URL,
    method: 'POST',
    timeout: 5000
  })
  return instance(config)
}
