<template>
    <div v-if="password.length > 0" class="space-y-2 transition-all duration-300">
        <div class="flex items-center gap-2">
            <div class="h-1 flex-1 rounded-full bg-gray-200 overflow-hidden">
                <div
                    class="h-full transition-all duration-300"
                    :class="[
                        strengthLevel === 0 ? 'w-0' : '',
                        strengthLevel === 1 ? 'w-1/3 bg-red-500' : '',
                        strengthLevel === 2 ? 'w-2/3 bg-yellow-500' : '',
                        strengthLevel === 3 ? 'w-full bg-green-500' : ''
                    ]"
                ></div>
            </div>
            <span class="text-xs font-medium" :class="strengthTextColor">{{ strengthText }}</span>
        </div>
        <ul class="space-y-1">
            <li v-for="(check, index) in checks" :key="index"
                class="flex items-center space-x-2 text-sm"
                :class="check.passed ? 'text-green-600' : 'text-gray-500'"
            >
                <CheckCircle2 v-if="check.passed" class="h-4 w-4"/>
                <Circle v-else class="h-4 w-4"/>
                <span>{{ check.text }}</span>
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { CheckCircle2, Circle } from 'lucide-vue-next'

const props = defineProps<{
    password: string
}>()

const checks = ref([
    { text: 'At least 8 characters', passed: false },
    { text: 'Contains uppercase letter', passed: false },
    { text: 'Contains number', passed: false },
    { text: 'Contains special character', passed: false }
])

const strengthLevel = computed(() => {
    const passedChecks = checks.value.filter(c => c.passed).length
    if (passedChecks === 0) return 0
    if (passedChecks <= 2) return 1
    if (passedChecks === 3) return 2
    return 3
})

const strengthText = computed(() => {
    switch (strengthLevel.value) {
        case 0: return 'Too weak'
        case 1: return 'Weak'
        case 2: return 'Medium'
        case 3: return 'Strong'
        default: return ''
    }
})

const strengthTextColor = computed(() => {
    switch (strengthLevel.value) {
        case 0: return 'text-gray-500'
        case 1: return 'text-red-500'
        case 2: return 'text-yellow-500'
        case 3: return 'text-green-500'
        default: return ''
    }
})

watch(() => props.password, (newPassword) => {
    checks.value[0].passed = newPassword.length >= 8
    checks.value[1].passed = /[A-Z]/.test(newPassword)
    checks.value[2].passed = /[0-9]/.test(newPassword)
    checks.value[3].passed = /[!@#$%^&*]/.test(newPassword)
}, { immediate: true })
</script>
