<template>
  <span
    :class="[
      'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
      variantClasses[variant]
    ]"
  >
    <slot></slot>
  </span>
</template>

<script>
export default {
  name: 'Badge',
  props: {
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'success', 'warning', 'destructive'].includes(value)
    }
  },
  setup() {
    const variantClasses = {
      default: 'bg-gray-100 text-gray-800',
      success: 'bg-green-100 text-green-800',
      warning: 'bg-yellow-100 text-yellow-800',
      destructive: 'bg-red-100 text-red-800'
    };

    return {
      variantClasses
    };
  }
};
</script>
