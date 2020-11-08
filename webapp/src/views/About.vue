<template>
  <SectionContainer title="关于本站">
    <p>本站为游戏BanG Dream! Girls Band Party!房间号收集平台</p>
    <p>在线人数：{{ onlineNumber }}</p>
    <b>仓库链接：</b>
    <li>
      <a
        href="https://github.com/maborosh/BandoriStation"
        target="_blank"
      >GitHub</a>
    </li>
    <li>
      <a
        href="https://gitee.com/maborosh/BandoriStation"
        target="_blank"
      >Gitee</a>
    </li>
    <p>
      <b>联系作者：</b><br>
      QQ: 2287477889<br>
      Email: maborosh@qq.com
    </p>
  </SectionContainer>
</template>

<script>
import SectionContainer from '@/components/common/SectionContainer'
import { getOnlineNumber } from '@/network/common'

export default {
  name: 'About',
  components: {
    SectionContainer
  },
  data () {
    return {
      onlineNumber: 0
    }
  },
  created () {
    this.$store.commit('navbar/setMenuDisplay', true)
    getOnlineNumber().then(response => {
      this.$globalFunctions.handleAPIResponse(
        response,
        responseData => {
          this.onlineNumber = responseData.online_number
        }
      )
    }).catch(
      error => {
        this.$globalFunctions.notify({ content: '请求失败' })
        console.log(error)
      }
    )
  }
}
</script>
