<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Search, Trash2, Pencil, Package, Eye } from 'lucide-vue-next'
import Modal from '@/Components/sidebar/UI/Modal.vue'
import ProductDetails from '@/Components/sidebar/eCommerce/ProductDetails.vue'

const props = defineProps<{
  products: {
    data: Array<{
      id: number
      item_code: string
      name: string
      category_id: number
      category: { id: number; name: string } | null
      custom_rental_fee: string | null
      custom_security_deposit: string | null
      status: string
      description: string | null
      specifics: Record<string, any> | null
      is_active: boolean
      is_draft: boolean
      tags: Array<{ id: number; name: string }>
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
}>()

const emit = defineEmits<{
  (e: 'edit', product: typeof props.products.data[0]): void
}>()

const statusColors: Record<string, string> = {
  available: 'bg-green-100 text-green-600',
  rented: 'bg-blue-100 text-blue-600',
  maintenance: 'bg-yellow-100 text-yellow-600',
  retired: 'bg-red-100 text-red-600',
}

const isDraftText = (product: typeof props.products.data[0] & { is_draft?: boolean }) =>
  product.is_draft ? 'bg-purple-100 text-purple-600' : ''

const search = ref('')
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const onSearchInput = () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(route('products.index'), { search: search.value || undefined }, {
      preserveState: true,
      replace: true,
    })
  }, 300)
}

const deleteProduct = (id: number) => {
  if (confirm('Are you sure?')) {
    router.delete(route('products.destroy', id))
  }
}

watch(() => props.products, () => {
  search.value = new URL(window.location.href).searchParams.get('search') || ''
}, { immediate: true })

const showModal = ref(false)
const selectedProduct = ref<typeof props.products.data[0] | null>(null)

const openModal = (product: typeof props.products.data[0]) => {
  selectedProduct.value = product
  showModal.value = true
}
</script>

<template>
  <div class="space-y-4">
    <div class="relative">
      <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
      <input v-model="search" type="text" placeholder="Search products..." @input="onSearchInput" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Item</th>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Category</th>
            <th class="px-4 py-3 text-center font-medium text-slate-600 dark:text-slate-300">Status</th>
            <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Rental Fee</th>
            <th class="px-4 py-3 text-center font-medium text-slate-600 dark:text-slate-300">Active</th>
            <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-for="product in products.data" :key="product.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
            <td class="px-4 py-3 cursor-pointer" @click="openModal(product)">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                  <Package class="w-4 h-4 text-emerald-500" />
                </div>
                <div>
                  <span class="text-slate-800 dark:text-slate-200 font-medium">{{ product.name }}</span>
                  <p class="text-xs text-slate-400 font-mono">{{ product.item_code }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ product.category?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-center">
              <div class="flex items-center justify-center gap-1.5">
                <span v-if="product.is_draft" class="inline-block px-2 py-0.5 text-xs rounded-full font-medium bg-purple-100 text-purple-600">Draft</span>
                <span :class="['inline-block px-2 py-0.5 text-xs rounded-full font-medium capitalize', statusColors[product.status] || 'bg-slate-100 text-slate-600']">
                  {{ product.status }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400">
              <span v-if="product.custom_rental_fee">₱{{ parseFloat(product.custom_rental_fee).toLocaleString() }}</span>
              <span v-else class="text-slate-400">—</span>
            </td>
            <td class="px-4 py-3 text-center">
              <span :class="product.is_active ? 'text-green-500' : 'text-red-500'" class="text-xs font-medium">
                {{ product.is_active ? 'Yes' : 'No' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <button @click="openModal(product)" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-300 transition-colors">
                  <Eye class="w-4 h-4" />
                </button>
                <button @click="emit('edit', product)" class="p-1 rounded hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-500 transition-colors">
                  <Pencil class="w-4 h-4" />
                </button>
                <button @click="deleteProduct(product.id)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-colors">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="products.data.length === 0">
            <td colspan="6" class="px-4 py-8 text-center text-slate-400">No products found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="products.links" class="flex justify-center gap-1">
      <component
        :is="link.url ? Link : 'span'"
        v-for="link in products.links"
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

    <!-- Product Detail Modal -->
    <Modal :show="showModal" size="max-w-2xl" @close="showModal = false">
      <template #body>
        <ProductDetails v-if="selectedProduct" :product="selectedProduct" />
      </template>
      <template #footer>
        <button @click="showModal = false" class="px-4 py-2 text-sm rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600">Close</button>
      </template>
    </Modal>
  </div>
</template>
