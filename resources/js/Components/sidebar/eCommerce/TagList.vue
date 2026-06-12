<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Search, Trash2, Pencil, Tag } from 'lucide-vue-next'

const props = defineProps<{
  tags: {
    data: Array<{
      id: number
      name: string
      slug: string
      type: number
      tag_type: { id: number; name: string } | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
}>()

const emit = defineEmits<{
  (e: 'edit', tag: typeof props.tags.data[0]): void
}>()

const typeColors = ['bg-blue-100 text-blue-600', 'bg-green-100 text-green-600', 'bg-purple-100 text-purple-600', 'bg-pink-100 text-pink-600', 'bg-yellow-100 text-yellow-600', 'bg-red-100 text-red-600', 'bg-indigo-100 text-indigo-600', 'bg-teal-100 text-teal-600']
const getTypeColor = (id: number) => typeColors[id % typeColors.length]

const search = ref('')
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const onSearchInput = () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(route('tags.index'), { tag_search: search.value || undefined }, {
      preserveState: true,
      replace: true,
    })
  }, 300)
}

const deleteTag = (id: number) => {
  if (confirm('Are you sure?')) {
    router.delete(route('tags.destroy', id))
  }
}

watch(() => props.tags, () => {
  search.value = new URL(window.location.href).searchParams.get('tag_search') || ''
}, { immediate: true })
</script>

<template>
  <div class="space-y-4">
    <div class="relative">
      <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
      <input v-model="search" type="text" placeholder="Search tags..." @input="onSearchInput" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Name</th>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Slug</th>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Type</th>
            <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-for="tag in tags.data" :key="tag.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-600 flex items-center justify-center">
                  <Tag class="w-4 h-4 text-slate-500 dark:text-slate-300" />
                </div>
                <span class="text-slate-800 dark:text-slate-200 font-medium">{{ tag.name }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-slate-500 dark:text-slate-400 font-mono text-xs">{{ tag.slug }}</td>
            <td class="px-4 py-3">
              <span v-if="tag.tag_type" :class="['inline-block px-2 py-0.5 text-xs rounded-full font-medium', getTypeColor(tag.tag_type.id)]">
                {{ tag.tag_type.name }}
              </span>
              <span v-else class="text-slate-400">—</span>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <button @click="emit('edit', tag)" class="p-1 rounded hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-500 transition-colors">
                  <Pencil class="w-4 h-4" />
                </button>
                <button @click="deleteTag(tag.id)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-colors">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="tags.data.length === 0">
            <td colspan="4" class="px-4 py-8 text-center text-slate-400">No tags found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="tags.links" class="flex justify-center gap-1">
      <component
        :is="link.url ? Link : 'span'"
        v-for="link in tags.links"
        :key="link.label"
        :href="link.url"
        v-html="link.label"
        :class="[
          'px-3 py-1 rounded text-sm font-medium transition-colors',
          link.active ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700',
          !link.url ? 'opacity-50 cursor-not-allowed' : ''
        ]"
      />
    </div>
  </div>
</template>
