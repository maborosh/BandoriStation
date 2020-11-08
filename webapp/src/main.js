import Vue from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import store from './store'
import VueCookies from 'vue-cookies'
import VueClipboard from 'vue-clipboard2'
import * as globalFunctions from './utilities/globalFunctions'

import { library } from '@fortawesome/fontawesome-svg-core'
import {
  faStar, faPen, faTimes, faImage, faCopy, faPlus, faFilter, faComment,
  faExpand, faCompress
} from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

Vue.use(VueCookies)
Vue.use(VueClipboard)
Vue.prototype.$globalFunctions = globalFunctions

library.add(
  faStar, faPen, faTimes, faImage, faCopy, faPlus, faFilter, faComment,
  faExpand, faCompress
)
Vue.component('font-awesome-icon', FontAwesomeIcon)

Vue.config.productionTip = false

router.beforeEach((to, from, next) => {
  store.commit('route/updatePage', { from, to })
  next()
})

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
