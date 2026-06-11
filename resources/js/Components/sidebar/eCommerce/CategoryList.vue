<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Search, Trash2, Pencil, FolderTree } from 'lucide-vue-next'

const props = defineProps<{
  categories: {
    data: Array<{
      id: number
      name: string
      prefix: string
      default_rental_fee: string
      default_security_deposit: string
      tag_types: Array<{ id: number; name: string }>
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
}>()

const emit = defineEmits<{
  (e: 'edit', category: typeof props.categories.data[0]): void
}>()

const search = ref('')
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const onSearchInput = () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(route('categories.index'), { search: search.value || undefined }, {
      preserveState: true,
      replace: true,
    })
  }, 300)
}

const deleteCategory = (id: number) => {
  if (confirm('Are you sure?')) {
    router.delete(route('categories.destroy', id))
  }
}

watch(() => props.categories, () => {
  search.value = new URL(window.location.href).searchParams.get('search') || ''
}, { immediate: true })
</script>

<template>
  <div class="space-y-4">
    <div class="relative">
      <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
      <input v-model="search" type="text" placeholder="Search categories..." @input="onSearchInput" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Name</th>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Prefix</th>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Tag Types</th>
            <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Rental Fee</th>
            <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Security Deposit</th>
            <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-for="category in categories.data" :key="category.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                  <FolderTree class="w-4 h-4 text-indigo-500" />
                </div>
                <span class="text-slate-800 dark:text-slate-200 font-medium">{{ category.name }}</span>
              </div>
            </td>
            <td class="px-4 py-3">
              <span class="inline-block px-2 py-0.5 text-xs rounded-full font-medium bg-slate-100 dark:bg-slate-600 text-slate-600 dark:text-slate-300 font-mono">
                {{ category.prefix }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex flex-wrap gap-1">
                <span v-for="tt in category.tag_types" :key="tt.id" class="inline-block px-2 py-0.5 text-xs rounded-full font-medium bg-blue-100 text-blue-600 dark:bg-blue-900/30">
                  {{ tt.name }}
                </span>
                <span v-if="!category.tag_types || category.tag_types.length === 0" class="text-slate-400">—</span>
              </div>
            </td>
            <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400">₱{{ parseFloat(category.default_rental_fee).toLocaleString() }}</td>
            <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400">₱{{ parseFloat(category.default_security_deposit).toLocaleString() }}</td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <button @click="emit('edit', category)" class="p-1 rounded hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-500 transition-colors">
                  <Pencil class="w-4 h-4" />
                </button>
                <button @click="deleteCategory(category.id)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-colors">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="categories.data.length === 0">
            <td colspan="6" class="px-4 py-8 text-center text-slate-400">No categories found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="categories.links" class="flex justify-center gap-1">
      <component
        :is="link.url ? Link : 'span'"
        v-for="link in categories.links"
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
