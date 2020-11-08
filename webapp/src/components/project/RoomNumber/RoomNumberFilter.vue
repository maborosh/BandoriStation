<template>
  <div>
    <div class="title">
      房间类型
    </div>
    <div class="options-container">
      <Checkbox1
        v-model="typeList"
        class="checkbox-1 option"
        text="7万常规房"
        value="7"
      />
      <Checkbox1
        v-model="typeList"
        class="checkbox-1 option"
        text="12万高手房"
        value="12"
      />
      <Checkbox1
        v-model="typeList"
        class="checkbox-1 option"
        text="18万大师房"
        value="18"
      />
      <Checkbox1
        v-model="typeList"
        class="checkbox-1 option"
        text="25万房"
        value="25"
      />
      <Checkbox1
        v-model="typeList"
        class="checkbox-1 option"
        text="其他房"
        value="other"
      />
    </div>
    <div class="line-container-broad title">
      屏蔽关键词
    </div>
    <div class="line-container">
      <div>
        <Input1
          id="block-word-input"
          v-model="keyword"
          placeholder="关键词"
          @keyup.enter.native="addKeyword"
        />
        <Button1
          id="block-word-add-button"
          class="button-1"
          color="3"
          :config="{ type: 'button' }"
          @click.native="addKeyword"
        >
          添加
        </Button1>
      </div>
      <div class="line-container">
        <Pool
          title="屏蔽关键词"
          :data-list="keywordList"
          @removeItem="removeItemInList('keywordList', $event)"
        />
      </div>
    </div>
    <div class="line-container-broad title">
      屏蔽用户
    </div>
    <div class="line-container">
      <Pool
        title="屏蔽用户"
        :data-list="userList"
        @removeItem="removeItemInList('userList', $event)"
      />
    </div>
    <div class="line-container button-container">
      <div class="inner-button-container">
        <Button1
          class="button-1"
          color="1"
          :config="{ type: 'button' }"
          @click.native="confirmUpdateFilter"
        >
          应用
        </Button1>
      </div>
      <div class="inner-button-container">
        <Button1
          class="button-1"
          color="2"
          :config="{ type: 'button' }"
          @click.native="initializeData"
        >
          重置
        </Button1>
      </div>
    </div>
  </div>
</template>

<script>
import Checkbox1 from '@/components/common/Checkbox1'
import Button1 from '@/components/common/Button1'
import Input1 from '@/components/common/Input1'
import Pool from '@/components/common/Pool'
import { updateRoomNumberFilter } from '@/network/mainAction'

export default {
  name: 'RoomNumberFilter',
  components: {
    Pool,
    Input1,
    Button1,
    Checkbox1
  },
  props: {
    filter: {
      type: Object,
      default () {
        return {}
      }
    }
  },
  data () {
    return {
      typeList: [],
      typeListDef: ['7', '12', '18', '25', 'other'],
      keyword: '',
      keywordList: [],
      userList: []
    }
  },
  methods: {
    initializeData () {
      this.typeList = this.typeListDef.filter(item => {
        return !this.filter.type.includes(item)
      })
      this.keyword = ''
      const keywordList = []
      for (let i = 0; i < this.filter.keyword.length; i++) {
        keywordList.push({
          id: i,
          content: this.filter.keyword[i]
        })
      }
      this.keywordList = keywordList
      const userList = []
      for (let i = 0; i < this.filter.user.length; i++) {
        userList.push({
          id: i,
          content: this.filter.user[i].username,
          raw: this.filter.user[i]
        })
      }
      this.userList = userList
    },
    addKeyword () {
      if (this.keyword === '') {
        this.$globalFunctions.notify({ content: '请输入需要添加的屏蔽关键词' })
      } else {
        let pushFlag = true
        for (const item of this.keywordList) {
          if (item.content === this.keyword) {
            pushFlag = false
            break
          }
        }
        if (pushFlag) {
          this.keywordList.push({
            id: this.keywordList.length === 0 ? 0 : this.keywordList[this.keywordList.length - 1].id + 1,
            content: this.keyword
          })
          this.keyword = ''
        } else {
          this.$globalFunctions.notify({ content: '输入的屏蔽关键词已被添加，请重新输入' })
        }
      }
    },
    addUser (userInfo) {
      let pushFlag = true
      for (const item of this.userList) {
        if (item.raw.type === userInfo.type && item.raw.user_id === userInfo.user_id) {
          pushFlag = false
          break
        }
      }
      if (pushFlag) {
        this.$globalFunctions.notify({
          content: '确认屏蔽用户“' + userInfo.username + '”？',
          displayCancel: true,
          callback: () => {
            this.userList.push({
              id: this.userList.length === 0 ? 0 : this.userList[this.userList.length - 1].id + 1,
              content: userInfo.username,
              raw: userInfo
            })
            this.confirmUpdateFilter()
          }
        })
      } else {
        this.$globalFunctions.notify({ content: '该用户已被屏蔽，请勿重复添加' })
      }
    },
    removeItemInList (listName, id) {
      this[listName] = this[listName].filter(item => {
        return item.id !== id
      })
    },
    confirmUpdateFilter () {
      const filter = {
        type: [],
        keyword: [],
        user: []
      }
      filter.type = this.typeListDef.filter(item => {
        return !this.typeList.includes(item)
      })
      for (const item of this.keywordList) {
        filter.keyword.push(item.content)
      }
      for (const item of this.userList) {
        filter.user.push(item.raw)
      }
      if (this.$store.state.account.loginStatus) {
        updateRoomNumberFilter(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          { room_number_filter: filter }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            () => {
              this.$store.commit(
                'modal/dialog/setDisplay',
                {
                  view: 'Home',
                  function: 'setRoomNumberFilter',
                  isDisplay: false
                }
              )
              this.$emit('updateFilter')
            }
          )
        }).catch(
          error => {
            this.$globalFunctions.notify({ content: '请求失败' })
            console.log(error)
          }
        )
      } else {
        this.$cookies.set('roomNumberFilter', filter, 259200)
        this.$emit('updateFilter')
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.title {
  font-weight: bold;
  font-size: 1.8rem;
}

.options-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}

.option {
  margin-top: 1rem;
}

#block-word-input {
  width: calc(100% - 6.5rem);
  display: inline-block;
  vertical-align: top;
}

#block-word-add-button {
  width: 6rem;
  display: inline-block;
  vertical-align: top;
  margin-left: 0.5rem;
}
</style>
