import { ref } from 'vue'

type NotificationType = 'success' | 'error' | 'warning' | 'info'

interface Notification {
  id: number
  type: NotificationType
  message: string
  timeout?: number
}

const notifications = ref<Notification[]>([])
let nextId = 0

export function useNotifications() {
  function add(type: NotificationType, message: string, timeout = 4000) {
    const id = ++nextId
    notifications.value.push({ id, type, message, timeout })
    if (timeout > 0) {
      setTimeout(() => remove(id), timeout)
    }
  }

  function remove(id: number) {
    const idx = notifications.value.findIndex(n => n.id === id)
    if (idx !== -1) notifications.value.splice(idx, 1)
  }

  function success(message: string) { add('success', message) }
  function error(message: string) { add('error', message, 6000) }
  function warning(message: string) { add('warning', message, 5000) }
  function info(message: string) { add('info', message) }

  return { notifications, add, remove, success, error, warning, info }
}

export type { Notification, NotificationType }