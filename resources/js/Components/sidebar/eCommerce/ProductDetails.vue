<script setup lang="ts">
import { Package } from 'lucide-vue-next'

const props = defineProps<{
  product: {
    id: number
    item_code: string
    name: string
    category: { id: number; name: string } | null
    custom_rental_fee: string | null
    custom_security_deposit: string | null
    status: string
    description: string | null
    specifics: Record<string, any> | null
    is_active: boolean
    tags: Array<{ id: number; name: string }>
    images?: Array<{ id: number; file_path: string; thumbnail_path: string | null; label: string | null; is_primary: boolean }>
    created_at: string
  }
}>()

const statusColors: Record<string, string> = {
  available: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
  rented: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
  maintenance: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
  retired: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
        <Package class="w-5 h-5 text-emerald-500" />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200">{{ product.name }}</h3>
        <p class="text-xs text-slate-500 font-mono">{{ product.item_code }}</p>
      </div>
    </div>

    <hr class="border-slate-200 dark:border-slate-700">

    <div class="grid grid-cols-2 gap-3 text-sm">
      <div>
        <span class="block text-xs text-slate-400">Category</span>
        <span class="text-slate-700 dark:text-slate-300">{{ product.category?.name ?? '—' }}</span>
      </div>
      <div>
        <span class="block text-xs text-slate-400">Status</span>
        <span :class="['inline-block px-2 py-0.5 text-xs rounded-full font-medium capitalize mt-0.5', statusColors[product.status]]">
          {{ product.status }}
        </span>
      </div>
      <div>
        <span class="block text-xs text-slate-400">Rental Fee</span>
        <span class="text-slate-700 dark:text-slate-300">{{ product.custom_rental_fee ? '₱' + parseFloat(product.custom_rental_fee).toLocaleString() : 'Category default' }}</span>
      </div>
      <div>
        <span class="block text-xs text-slate-400">Security Deposit</span>
        <span class="text-slate-700 dark:text-slate-300">{{ product.custom_security_deposit ? '₱' + parseFloat(product.custom_security_deposit).toLocaleString() : 'Category default' }}</span>
      </div>
      <div>
        <span class="block text-xs text-slate-400">Active</span>
        <span :class="product.is_active ? 'text-green-600' : 'text-red-600'" class="font-medium">{{ product.is_active ? 'Yes' : 'No' }}</span>
      </div>
    </div>

    <div v-if="product.tags && product.tags.length > 0">
      <hr class="border-slate-200 dark:border-slate-700 mb-3">
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Tags</h4>
      <div class="flex flex-wrap gap-1.5">
        <span v-for="tag in product.tags" :key="tag.id" class="px-2.5 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-600 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-400">
          {{ tag.name }}
        </span>
      </div>
    </div>

    <div v-if="product.description">
      <hr class="border-slate-200 dark:border-slate-700 mb-3">
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Description</h4>
      <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ product.description }}</p>
    </div>

    <div v-if="product.specifics && Object.keys(product.specifics).length > 0">
      <hr class="border-slate-200 dark:border-slate-700 mb-3">
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Specifics</h4>
      <div class="grid grid-cols-2 gap-3 text-sm">
        <div v-for="(value, key) in product.specifics" :key="key">
          <span class="block text-xs text-slate-400 capitalize">{{ key.replace(/_/g, ' ') }}</span>
          <span class="text-slate-700 dark:text-slate-300">{{ value ?? '—' }}</span>
        </div>
      </div>
    </div>

    <div v-if="product.images && product.images.length > 0">
      <hr class="border-slate-200 dark:border-slate-700 mb-3">
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Images ({{ product.images.length }})</h4>
      <div class="grid grid-cols-3 gap-2">
        <div v-for="img in product.images" :key="img.id" class="relative group">
          <img :src="'/storage/' + img.file_path" class="w-full h-20 object-cover rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900" />
          <span v-if="img.is_primary" class="absolute top-0.5 left-0.5 bg-blue-600 text-white text-[10px] px-1 rounded font-medium">Primary</span>
          <p v-if="img.label" class="text-[10px] text-slate-500 mt-0.5 truncate">{{ img.label }}</p>
        </div>
      </div>
    </div>

    <hr class="border-slate-200 dark:border-slate-700">
    <div class="text-xs text-slate-400">
      Created: {{ product.created_at ? new Date(product.created_at).toLocaleDateString() : '—' }}
    </div>
  </div>
</template>
