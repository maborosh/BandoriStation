import Vue from 'vue'
import Vuex from 'vuex'
import { ASSETS_URL } from '@/utilities/constants'
import userAvatarImage from '@/assets/images/user_avatar.png'

Vue.use(Vuex)

const account = {
  namespaced: true,
  state: () => ({
    initialized: false,
    loginStatus: false,
    token: null,
    avatar: null
  }),
  mutations: {
    setInitializeStatus (state, status) {
      state.initialized = status
    },
    setLoginStatus (state, status) {
      state.loginStatus = status
    },
    setToken (state, token) {
      state.token = token
    },
    setAvatar (state, imagePath) {
      if (imagePath) {
        state.avatar = ASSETS_URL + '/images/user-avatar/' + imagePath
      } else {
        state.avatar = userAvatarImage
      }
    },
    setRoomNumberFilter (state, filterSetting) {
      state.roomNumberFilter = filterSetting
    }
  }
}

const navbar = {
  namespaced: true,
  state: () => ({
    displayMenu: false
  }),
  mutations: {
    setMenuDisplay (state, isDisplay) {
      state.displayMenu = isDisplay
    }
  }
}

const modal = {
  namespaced: true,
  modules: {
    notice: {
      namespaced: true,
      state: () => ({
        noticeList: []
      }),
      mutations: {
        pushNotice (state, payload) {
          state.noticeList.push({
            id: state.noticeList.length,
            displayModal: true,
            title: payload.title === undefined ? '提示' : payload.title,
            content: payload.content,
            displayCancel: payload.displayCancel === undefined ? false : payload.displayCancel,
            callback: payload.callback === undefined ? null : payload.callback
          })
        },
        hideNotice (state, id) {
          state.noticeList[id].displayModal = false
        },
        removeNotice (state, id) {
          state.noticeList.splice(id, 1)
        }
      }
    },
    dialog: {
      namespaced: true,
      state: () => ({
        Account: {
          updateAvatar: false,
          updateUsername: false,
          updatePassword: false,
          updateEmail: false,
          bindQQ: false
        },
        Home: {
          sendRoomNumber: false,
          setRoomNumberFilter: false,
          informUser: false,
          chatRoom: false
        },
        VerifyEmail: {
          changeEmail: false
        }
      }),
      mutations: {
        setDisplay (state, payload) {
          state[payload.view][payload.function] = payload.isDisplay
        }
      }
    }
  }
}

const route = {
  namespaced: true,
  state: () => ({
    latestPage: null,
    currentPage: null
  }),
  mutations: {
    updatePage (state, payload) {
      state.latestPage = payload.from
      state.currentPage = payload.to
    }
  }
}

const misc = {
  namespaced: true,
  state: () => ({
    userDeviceType: ''
  }),
  mutations: {
    setUserDeviceType (state, device) {
      state.userDeviceType = device
    }
  }
}

export default new Vuex.Store({
  getters: {
    menuList: (state) => {
      if (state.account.loginStatus) {
        return [
          {
            title: '个人中心',
            type: 'link',
            path: '/account',
            style: 'top'
          },
          {
            title: '关于本站',
            type: 'link',
            path: '/about',
            style: 'middle'
          },
          {
            title: '退出登陆',
            type: 'button',
            function: 'logoutAccount',
            style: 'bottom'
          }
        ]
      } else {
        return [
          {
            title: '登陆',
            type: 'link',
            path: '/login',
            style: 'top'
          },
          {
            title: '关于本站',
            type: 'link',
            path: '/about',
            style: 'bottom'
          }
        ]
      }
    }
  },
  modules: {
    account,
    navbar,
    modal,
    route,
    misc
  }
})
