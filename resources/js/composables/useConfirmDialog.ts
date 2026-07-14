import { ref } from 'vue'

const visible = ref(false)
const title = ref('')
const message = ref('')
let resolvePromise: ((value: boolean) => void) | null = null

export function useConfirmDialog() {
  function show(msg: string, ttl = 'Confirm'): Promise<boolean> {
    title.value = ttl
    message.value = msg
    visible.value = true
    return new Promise((resolve) => {
      resolvePromise = resolve
    })
  }

  function confirm() {
    visible.value = false
    resolvePromise?.(true)
    resolvePromise = null
  }

  function cancel() {
    visible.value = false
    resolvePromise?.(false)
    resolvePromise = null
  }

  return { visible, title, message, show, confirm, cancel }
}