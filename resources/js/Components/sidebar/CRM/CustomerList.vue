<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Search, Trash2, Eye, Pencil } from 'lucide-vue-next'
import Modal from '@/Components/sidebar/UI/Modal.vue'
import CustomerDetails from '@/Components/sidebar/CRM/CustomerDetails.vue'

const props = defineProps<{
  customers: {
    data: Array<{
      id: number
      first_name: string
      last_name: string
      contact_number: string | null
      email: string | null
      address: string | null
      affiliation: string | null
      social_media_link: string | null
      identification: string | null
      detail_info: Record<string, any> | null
      is_blacklisted: boolean
      admin_notes: string | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
}>()

const emit = defineEmits<{
  (e: 'edit', customer: typeof props.customers.data[0]): void
}>()

const avatarColors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-pink-500', 'bg-yellow-500', 'bg-red-500', 'bg-indigo-500', 'bg-teal-500']
const getColor = (id: number) => avatarColors[id % avatarColors.length]

const search = ref('')
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const onSearchInput = () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(route('customers.index'), { search: search.value || undefined }, {
      preserveState: true,
      replace: true,
    })
  }, 300)
}

const deleteCustomer = (id: number) => {
  if (confirm('Are you sure?')) {
    router.delete(route('customers.destroy', id))
  }
}

watch(() => props.customers, () => {
  search.value = new URL(window.location.href).searchParams.get('search') || ''
}, { immediate: true })

const showModal = ref(false)
const selectedCustomer = ref<typeof props.customers.data[0] | null>(null)

const openModal = (customer: typeof props.customers.data[0]) => {
  selectedCustomer.value = customer
  showModal.value = true
}
</script>

<template>
  <div class="space-y-4">
    <!-- Search -->
    <div class="relative">
      <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
      <input v-model="search" type="text" placeholder="Search customers..." @input="onSearchInput" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Name</th>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Contact</th>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Email</th>
            <th class="px-4 py-3 text-center font-medium text-slate-600 dark:text-slate-300">Blacklisted</th>
            <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50" :class="selectedCustomer && selectedCustomer.id === customer.id ? 'bg-gray-200 dark:bg-gray-600' : ''">
            <td class="px-4 py-3 cursor-pointer" @click="openModal(customer)">
              <div class="flex items-center gap-3">
                <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold', getColor(customer.id)]">
                  {{ customer.first_name.charAt(0) }}{{ customer.last_name.charAt(0) }}
                </div>
                <span class="text-slate-800 dark:text-slate-200">{{ customer.first_name }} {{ customer.last_name }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ customer.contact_number ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ customer.email ?? '—' }}</td>
            <td class="px-4 py-3 text-center">
              <span :class="customer.is_blacklisted ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'" class="inline-block px-2 py-0.5 text-xs rounded-full font-medium">
                {{ customer.is_blacklisted ? 'Yes' : 'No' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <button @click="openModal(customer)" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-300 transition-colors">
                  <Eye class="w-4 h-4" />
                </button>
                <button @click="emit('edit', customer); selectedCustomer = customer" class="p-1 rounded hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-500 transition-colors">
                  <Pencil class="w-4 h-4" />
                </button>
                <button @click="deleteCustomer(customer.id)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-colors">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="customers.data.length === 0">
            <td colspan="5" class="px-4 py-8 text-center text-slate-400">No customers found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="customers.links" class="flex justify-center gap-1">
      <component
        :is="link.url ? Link : 'span'"
        v-for="link in customers.links"
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

    <!-- Customer Detail Modal -->
    <Modal :show="showModal" size="max-w-lg" @close="showModal = false">
      <template #body>
        <CustomerDetails v-if="selectedCustomer" :customer="selectedCustomer" />
      </template>
      <template #footer>
        <button @click="showModal = false" class="px-4 py-2 text-sm rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600">Close</button>
      </template>
    </Modal>
  </div>
</template>
