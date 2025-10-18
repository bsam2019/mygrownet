<template>
  <div class="w-full overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500 divide-y divide-gray-200">
      <slot></slot>
    </table>
  </div>
</template>

<script>
export default {
  name: 'Table'
};
</script>
