<template>
  <div>
    <div>
      <Input1
        ref="roomNumber"
        v-model="roomNumber"
        placeholder="房间号"
        @keyup.enter.native="setFocus('description')"
      />
    </div>
    <div class="line-container">
      <Textarea1
        ref="description"
        v-model="description"
        placeholder="描述"
        :area-style="{
          height: '8rem'
        }"
        class="description"
        @keyup.enter.native="confirmSendRoomNumber"
      />
    </div>
    <div class="line-container">
      <Button1
        class="button-1"
        color="1"
        :config="{ type: 'button' }"
        @click.native="confirmSendRoomNumber"
      >
        提交
      </Button1>
    </div>
  </div>
</template>

<script>
import Input1 from '@/components/common/Input1'
import Textarea1 from '@/components/common/Textarea1'
import Button1 from '@/components/common/Button1'
import { BANNED_ROOM_NUMBER_PATTERN, BANNED_ROOM_NUMBER_DESCRIPTION_WORD } from '@/utilities/constants'

export default {
  name: 'SendRoomNumber',
  components: {
    Input1,
    Textarea1,
    Button1
  },
  props: {
    websocketWorker: Worker
  },
  data () {
    return {
      roomNumber: '',
      description: ''
    }
  },
  methods: {
    initializeData () {
      this.roomNumber = ''
      this.description = ''
    },
    confirmSendRoomNumber () {
      if (this.roomNumber === '') {
        this.$globalFunctions.notify({ content: '房间号不能为空' })
      } else if (!/^[0-9]{5,6}$/.test(this.roomNumber)) {
        this.$globalFunctions.notify({ content: '请输入正确的房间号' })
      } else if (this.description === '') {
        this.$globalFunctions.notify({ content: '房间描述不能为空' })
      } else if (!this.checkNumber()) {
        this.$globalFunctions.notify({ content: '房间号不合法，请重新确认输入有效的房间号' })
      } else if (!this.checkDescription()) {
        this.$globalFunctions.notify({ content: '房间号描述含有违禁词，请修改后再发送' })
      } else {
        this.websocketWorker.postMessage({
          action: 'sendRoomNumber',
          data: {
            room_number: this.roomNumber,
            description: this.description
          }
        })
        this.$store.commit(
          'modal/dialog/setDisplay',
          {
            view: 'Home',
            function: 'sendRoomNumber',
            isDisplay: false
          }
        )
      }
    },
    checkNumber () {
      for (const pattern of BANNED_ROOM_NUMBER_PATTERN) {
        if (pattern.test(this.roomNumber)) {
          return false
        }
      }
      return true
    },
    checkDescription () {
      const bannedWordList = JSON.parse(atob(BANNED_ROOM_NUMBER_DESCRIPTION_WORD))
      const description = this.description.replace(/\s*/g, '')
      for (const word of bannedWordList) {
        if (description.indexOf(word) > -1) {
          return false
        }
      }
      return true
    },
    setFocus (ref) {
      this.$refs[ref].focus()
    }
  }
}
</script>
