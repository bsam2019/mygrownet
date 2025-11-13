<template>
    <div class="network-tree">
        <div class="tree-node">
            <div class="node-card">
                <div class="font-medium">{{ tree.name }}</div>
                <div class="text-sm text-gray-500">{{ tree.email }}</div>
                <div class="text-xs text-gray-400 mt-1">Level {{ tree.level }}</div>
            </div>
            
            <div v-if="tree.children && tree.children.length > 0" class="children">
                <div class="children-container">
                    <NetworkTree
                        v-for="child in tree.children"
                        :key="child.id"
                        :tree="child"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    tree: {
        type: Object,
        required: true
    }
});
</script>

<style scoped>
.network-tree {
    display: flex;
    justify-content: center;
    padding: 1rem;
}

.tree-node {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.node-card {
    padding: 1rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    min-width: 200px;
    text-align: center;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.children {
    margin-top: 2rem;
    position: relative;
}

.children::before {
    content: '';
    position: absolute;
    top: -2rem;
    left: 50%;
    width: 2px;
    height: 2rem;
    background: #d1d5db;
}

.children-container {
    display: flex;
    gap: 2rem;
    position: relative;
}

.children-container::before {
    content: '';
    position: absolute;
    top: -2rem;
    left: 0;
    right: 0;
    height: 2px;
    background: #d1d5db;
}

.children-container > * {
    position: relative;
}

.children-container > *::before {
    content: '';
    position: absolute;
    top: -2rem;
    left: 50%;
    width: 2px;
    height: 2rem;
    background: #d1d5db;
}
</style>
