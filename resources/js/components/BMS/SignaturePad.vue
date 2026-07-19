<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const props = withDefaults(defineProps<{
  width?: number
  height?: number
  penColor?: string
  bgColor?: string
}>(), {
  width: 500,
  height: 200,
  penColor: '#1f2937',
  bgColor: '#ffffff',
})

const emit = defineEmits<{
  done: [dataUrl: string]
  clear: []
}>()

const canvasRef = ref<HTMLCanvasElement | null>(null)
let ctx: CanvasRenderingContext2D | null = null
let isDrawing = false
let hasDrawn = false

const startDrawing = (e: MouseEvent | TouchEvent) => {
  isDrawing = true
  hasDrawn = true
  const pos = getPosition(e)
  ctx?.beginPath()
  ctx?.moveTo(pos.x, pos.y)
}

const draw = (e: MouseEvent | TouchEvent) => {
  if (!isDrawing) return
  e.preventDefault()
  const pos = getPosition(e)
  ctx?.lineTo(pos.x, pos.y)
  ctx?.stroke()
}

const stopDrawing = () => {
  if (isDrawing) {
    isDrawing = false
    ctx?.closePath()
  }
}

const getPosition = (e: MouseEvent | TouchEvent) => {
  const canvas = canvasRef.value
  if (!canvas) return { x: 0, y: 0 }
  const rect = canvas.getBoundingClientRect()

  if ('touches' in e) {
    return {
      x: (e.touches[0].clientX - rect.left) * (canvas.width / rect.width),
      y: (e.touches[0].clientY - rect.top) * (canvas.height / rect.height),
    }
  }
  return {
    x: (e.clientX - rect.left) * (canvas.width / rect.width),
    y: (e.clientY - rect.top) * (canvas.height / rect.height),
  }
}

const clearCanvas = () => {
  if (!ctx || !canvasRef.value) return
  ctx.fillStyle = props.bgColor
  ctx.fillRect(0, 0, canvasRef.value.width, canvasRef.value.height)
  hasDrawn = false
  emit('clear')
}

const isEmpty = () => !hasDrawn

const getDataUrl = (): string | null => {
  return canvasRef.value?.toDataURL('image/png') || null
}

const confirmSignature = () => {
  if (!hasDrawn) return
  const dataUrl = getDataUrl()
  if (dataUrl) emit('done', dataUrl)
}

onMounted(() => {
  const canvas = canvasRef.value
  if (!canvas) return
  ctx = canvas.getContext('2d')
  if (!ctx) return

  ctx.strokeStyle = props.penColor
  ctx.lineWidth = 2
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'

  ctx.fillStyle = props.bgColor
  ctx.fillRect(0, 0, canvas.width, canvas.height)
})

defineExpose({ isEmpty, getDataUrl, clearCanvas })
</script>

<template>
  <div class="inline-block">
    <canvas
      ref="canvasRef"
      :width="width"
      :height="height"
      class="border-2 border-gray-300 rounded-lg cursor-crosshair touch-none"
      style="max-width: 100%;"
      @mousedown="startDrawing"
      @mousemove="draw"
      @mouseup="stopDrawing"
      @mouseleave="stopDrawing"
      @touchstart.prevent="startDrawing"
      @touchmove.prevent="draw"
      @touchend="stopDrawing"
    />
    <div class="flex justify-between mt-2">
      <button type="button" @click="clearCanvas" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
      <button type="button" @click="confirmSignature" :disabled="!hasDrawn" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
        Use Signature
      </button>
    </div>
  </div>
</template>
