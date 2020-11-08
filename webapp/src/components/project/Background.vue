<template>
  <canvas
    id="background"
    ref="background"
    :width="width"
    :height="height"
  />
</template>

<script>
import backgroundPatternImage from '@/assets/images/background_pattern.png'
import circleBaseImage from '@/assets/images/bg_common_circlebase.png'
import circleLineImage from '@/assets/images/bg_common_circleline.png'

export default {
  name: 'Background',
  data () {
    return {
      width: window.innerWidth,
      height: window.innerHeight,
      backgroundCtx: null,
      imageAsset: {
        loadCompleteNumber: 0,
        backgroundPattern: {
          image: null,
          canvas: null,
          context: null
        },
        circleBase: {
          image: null,
          width: 260,
          height: 260
        },
        circleLine: {
          image: null,
          width: 260,
          height: 260,
          canvas: null,
          context: null
        }
      }
    }
  },
  watch: {
    'imageAsset.loadCompleteNumber' (value) {
      if (value === 3) {
        this.drawBackgroundLoop()
      }
    }
  },
  mounted () {
    this.imageAsset.backgroundPattern.image = new Image()
    this.imageAsset.backgroundPattern.image.src = backgroundPatternImage
    this.imageAsset.circleBase.image = new Image()
    this.imageAsset.circleBase.image.src = circleBaseImage
    this.imageAsset.circleLine.image = new Image()
    this.imageAsset.circleLine.image.src = circleLineImage
    this.initializeBackgroundAssets()
    window.onresize = () => {
      this.width = window.innerWidth
      this.height = window.innerHeight
      this.initializeBackgroundPatternCanvas()
    }
  },
  methods: {
    initializeBackgroundAssets () {
      this.backgroundCtx = this.$refs.background.getContext('2d')
      this.imageAsset.backgroundPattern.canvas = document.createElement('canvas')
      this.imageAsset.backgroundPattern.context = this.imageAsset.backgroundPattern.canvas.getContext('2d')
      this.imageAsset.circleLine.canvas = document.createElement('canvas')
      this.imageAsset.circleLine.context = this.imageAsset.circleLine.canvas.getContext('2d')
      this.imageAsset.backgroundPattern.image.onload = () => {
        this.initializeBackgroundPatternCanvas()
        this.imageAsset.loadCompleteNumber += 1
      }
      this.imageAsset.circleBase.image.onload = () => {
        this.imageAsset.loadCompleteNumber += 1
      }
      this.imageAsset.circleLine.image.onload = () => {
        this.imageAsset.circleLine.canvas.width = this.imageAsset.circleLine.width
        this.imageAsset.circleLine.canvas.height = this.imageAsset.circleLine.height
        this.imageAsset.loadCompleteNumber += 1
      }
    },
    initializeBackgroundPatternCanvas () {
      this.imageAsset.backgroundPattern.context.clearRect(0, 0, this.width, this.height)
      this.imageAsset.backgroundPattern.canvas.width = this.width + 268
      this.imageAsset.backgroundPattern.canvas.height = this.height + 200
      this.imageAsset.backgroundPattern.context.fillStyle =
        this.imageAsset.backgroundPattern.context.createPattern(
          this.imageAsset.backgroundPattern.image, 'repeat'
        )
      this.imageAsset.backgroundPattern.context.fillRect(
        0, 0, this.imageAsset.backgroundPattern.canvas.width, this.imageAsset.backgroundPattern.canvas.height
      )
    },
    drawBackgroundLoop () {
      const currentTime = new Date().getTime()
      const patternPositionPercent = (currentTime % 14000) / 14000
      const circlePositionPercent = (currentTime % 40000) / 40000
      const patternOffset = {
        x: -267 + patternPositionPercent * 267,
        y: -patternPositionPercent * 200
      }
      this.backgroundCtx.clearRect(0, 0, this.width, this.height)
      this.imageAsset.circleLine.context.clearRect(
        0, 0,
        this.imageAsset.circleLine.canvas.width, this.imageAsset.circleLine.canvas.height
      )
      this.backgroundCtx.drawImage(this.imageAsset.backgroundPattern.canvas, patternOffset.x, patternOffset.y)
      this.backgroundCtx.drawImage(
        this.imageAsset.circleBase.image,
        (this.width - this.imageAsset.circleBase.width) / 2,
        (this.height - this.imageAsset.circleBase.height) / 2,
        this.imageAsset.circleBase.width, this.imageAsset.circleBase.height
      )
      this.imageAsset.circleLine.context.translate(
        this.imageAsset.circleLine.canvas.width / 2,
        this.imageAsset.circleLine.canvas.height / 2
      )
      this.imageAsset.circleLine.context.rotate(circlePositionPercent * 2 * Math.PI)
      this.imageAsset.circleLine.context.drawImage(
        this.imageAsset.circleLine.image,
        -this.imageAsset.circleLine.width / 2,
        -this.imageAsset.circleLine.height / 2,
        this.imageAsset.circleLine.width, this.imageAsset.circleLine.height
      )
      this.imageAsset.circleLine.context.rotate(-circlePositionPercent * 2 * Math.PI)
      this.imageAsset.circleLine.context.translate(
        -this.imageAsset.circleLine.width / 2,
        -this.imageAsset.circleLine.height / 2
      )
      this.backgroundCtx.drawImage(
        this.imageAsset.circleLine.canvas,
        (this.width - this.imageAsset.circleLine.canvas.width) / 2,
        (this.height - this.imageAsset.circleLine.canvas.height) / 2
      )
      requestAnimationFrame(this.drawBackgroundLoop)
    }
  }
}
</script>

<style lang="scss" scoped>
#background {
  position: fixed;
  z-index: -1000;
  top: 0;
}
</style>
