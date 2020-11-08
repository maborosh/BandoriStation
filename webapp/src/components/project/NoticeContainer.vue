<template>
  <div>
    <Modal1
      v-for="notice in $store.state.modal.notice.noticeList"
      :key="notice.id"
      :display-modal="notice.displayModal"
      :title="notice.title"
      :display-cancel="notice.displayCancel"
      @hideModalEvent="hideModal($event, notice)"
      @removeModalEvent="removeModal(notice.id)"
    >
      {{ notice.content }}
    </Modal1>
  </div>
</template>

<script>
import Modal1 from '@/components/common/Modal1'

export default {
  name: 'NoticeContainer',
  components: {
    Modal1
  },
  methods: {
    hideModal (type, notice) {
      if (type === 'confirm' && notice.callback) {
        notice.callback()
      }
      this.$store.commit('modal/notice/hideNotice', notice.id)
    },
    removeModal (id) {
      this.$store.commit('modal/notice/removeNotice', id)
    }
  }
}
</script>
