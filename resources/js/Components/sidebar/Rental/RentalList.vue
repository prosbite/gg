<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Search, Eye, Pencil, Trash2, Package, CheckCircle, XCircle, ArrowUp, Undo2 } from 'lucide-vue-next'
import Modal from '@/Components/sidebar/UI/Modal.vue'
import RentalDetails from '@/Components/sidebar/Rental/RentalDetails.vue'

const props = defineProps<{
  rentals: {
    data: Array<{
      id: number
      status: string
      pickup_date: string
      return_date: string
      actual_returned_at: string | null
      internal_notes: string | null
      created_at: string
      customer: {
        id: number
        first_name: string
        last_name: string
        contact_number: string | null
      }
      items: Array<{
        id: number
        status: string
        rental_fee: string
        security_deposit: string
        product: { id: number; name: string; item_code: string; is_draft: boolean }
      }>
      payments: Array<{
        id: number
        amount: string
        type: string
        method: string
        reference_number: string | null
        created_at: string
        receipts?: Array<{ id: number; file_path: string; notes: string | null }>
      }>
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  activeTab: string
}>()

const emit = defineEmits<{
  (e: 'edit', rental: typeof props.rentals.data[0]): void
}>()

const avatarColors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-pink-500', 'bg-yellow-500', 'bg-red-500', 'bg-indigo-500', 'bg-teal-500']
const getColor = (id: number) => avatarColors[id % avatarColors.length]

const tabs = [
  { key: 'all', label: 'All' },
  { key: 'reserved', label: 'Reserved' },
  { key: 'picked_up', label: 'Picked Up' },
  { key: 'returned', label: 'Returned' },
  { key: 'cancelled', label: 'Cancelled' },
  { key: 'completed', label: 'Completed' },
]

const statusColors: Record<string, string> = {
  reserved: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
  picked_up: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
  returned: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
  cancelled: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
  completed: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
}

const search = ref('')
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const onSearchInput = () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(route('rentals.index'), { tab: props.activeTab, search: search.value || undefined }, {
      preserveState: true,
      replace: true,
    })
  }, 300)
}

const switchTab = (key: string) => {
  router.get(route('rentals.index'), { tab: key, search: undefined, page: undefined }, {
    preserveState: true,
    replace: true,
  })
}

const deleteRental = (id: number) => {
  if (confirm('Delete this rental? This will free up the products.')) {
    router.delete(route('rentals.destroy', id))
  }
}

const pickupRental = (id: number) => {
  if (confirm('Mark this rental as picked up?')) {
    router.post(route('rentals.pickup', id), {}, {
      preserveState: true,
      replace: true,
    })
  }
}

const returnRental = (id: number) => {
  if (confirm('Mark this rental as returned?')) {
    router.post(route('rentals.return', id), {}, {
      preserveState: true,
      replace: true,
    })
  }
}

const completeRental = (id: number) => {
  if (confirm('Complete this rental? Products will be marked available.')) {
    router.post(route('rentals.complete', id), {}, {
      preserveState: true,
      replace: true,
    })
  }
}

const cancelRental = (id: number) => {
  if (confirm('Cancel this rental? Products will be freed.')) {
    router.post(route('rentals.cancel', id), {}, {
      preserveState: true,
      replace: true,
    })
  }
}

watch(() => props.rentals, () => {
  search.value = new URL(window.location.href).searchParams.get('search') || ''
}, { immediate: true })

const showModal = ref(false)
const selectedRental = ref<typeof props.rentals.data[0] | null>(null)

const openModal = (rental: typeof props.rentals.data[0]) => {
  selectedRental.value = rental
  showModal.value = true
}

const formatDate = (date: string) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const totalFee = (rental: typeof props.rentals.data[0]) => {
  return rental.items.reduce((sum, item) => sum + parseFloat(item.rental_fee), 0)
}

const totalPaid = (rental: typeof props.rentals.data[0]) => {
  return rental.payments.reduce((sum, p) => sum + parseFloat(p.amount), 0)
}
</script>

<template>
  <div class="space-y-4">
    <!-- Tabs -->
    <div class="flex gap-1 border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="switchTab(tab.key)"
        :class="['px-3 py-2 text-xs font-medium transition-colors border-b-2 -mb-px whitespace-nowrap', activeTab === tab.key ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300']"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Search -->
    <div class="relative">
      <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
      <input v-model="search" type="text" placeholder="Search by customer name or contact..." @input="onSearchInput" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Customer</th>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Items</th>
            <th class="px-4 py-3 text-center font-medium text-slate-600 dark:text-slate-300">Total</th>
            <th class="px-4 py-3 text-center font-medium text-slate-600 dark:text-slate-300">Paid</th>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Pickup</th>
            <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Return</th>
            <th class="px-4 py-3 text-center font-medium text-slate-600 dark:text-slate-300">Status</th>
            <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-for="rental in rentals.data" :key="rental.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
            <td class="px-4 py-3 cursor-pointer" @click="openModal(rental)">
              <div class="flex items-center gap-2.5">
                <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0', getColor(rental.customer.id)]">
                  {{ rental.customer.first_name.charAt(0) }}{{ rental.customer.last_name.charAt(0) }}
                </div>
                <div>
                  <p class="text-slate-800 dark:text-slate-200 font-medium text-xs">{{ rental.customer.first_name }} {{ rental.customer.last_name }}</p>
                  <p class="text-[11px] text-slate-400">{{ rental.customer.contact_number || '—' }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <div class="flex flex-col gap-0.5">
                <span v-for="item in rental.items" :key="item.id" class="text-[11px] text-slate-600 dark:text-slate-400 flex items-center gap-1">
                  <span v-if="item.product.is_draft" class="inline-block w-1.5 h-1.5 rounded-full bg-purple-400 shrink-0" />
                  {{ item.product.name }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3 text-center text-xs font-medium text-slate-700 dark:text-slate-300">
              ₱{{ totalFee(rental).toLocaleString() }}
            </td>
            <td class="px-4 py-3 text-center text-xs font-medium text-green-600 dark:text-green-400">
              ₱{{ totalPaid(rental).toLocaleString() }}
            </td>
            <td class="px-4 py-3 text-[11px] text-slate-600 dark:text-slate-400">{{ formatDate(rental.pickup_date) }}</td>
            <td class="px-4 py-3 text-[11px] text-slate-600 dark:text-slate-400">{{ formatDate(rental.return_date) }}</td>
            <td class="px-4 py-3 text-center">
              <span :class="['inline-block px-2 py-0.5 text-[11px] rounded-full font-medium capitalize', statusColors[rental.status] || 'bg-slate-100 text-slate-600']">
                {{ rental.status.replace(/_/g, ' ') }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-0.5">
                <button @click="openModal(rental)" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-300 transition-colors" title="View details">
                  <Eye class="w-3.5 h-3.5" />
                </button>
                <button v-if="rental.status === 'reserved'" @click="pickupRental(rental.id)" class="p-1 rounded hover:bg-amber-100 dark:hover:bg-amber-900/30 text-amber-500 transition-colors" title="Mark picked up">
                  <ArrowUp class="w-3.5 h-3.5" />
                </button>
                <button v-if="rental.status === 'picked_up'" @click="returnRental(rental.id)" class="p-1 rounded hover:bg-green-100 dark:hover:bg-green-900/30 text-green-500 transition-colors" title="Mark returned">
                  <Undo2 class="w-3.5 h-3.5" />
                </button>
                <button v-if="rental.status === 'returned'" @click="completeRental(rental.id)" class="p-1 rounded hover:bg-green-100 dark:hover:bg-green-900/30 text-green-500 transition-colors" title="Complete rental">
                  <CheckCircle class="w-3.5 h-3.5" />
                </button>
                <button v-if="rental.status === 'reserved'" @click="emit('edit', rental)" class="p-1 rounded hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-500 transition-colors" title="Edit">
                  <Pencil class="w-3.5 h-3.5" />
                </button>
                <button v-if="rental.status === 'reserved'" @click="cancelRental(rental.id)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-colors" title="Cancel">
                  <XCircle class="w-3.5 h-3.5" />
                </button>
                <button v-if="rental.status === 'cancelled' || rental.status === 'completed'" @click="deleteRental(rental.id)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-colors" title="Delete">
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="rentals.data.length === 0">
            <td colspan="8" class="px-4 py-8 text-center text-slate-400">No rentals found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="rentals.links" class="flex justify-center gap-1">
      <component
        :is="link.url ? Link : 'span'"
        v-for="link in rentals.links"
        :key="link.label"
        :href="link.url || '#'"
        v-html="link.label"
        :class="[
          'px-3 py-1 rounded text-sm font-medium transition-colors',
          link.active ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700',
          !link.url ? 'opacity-50 cursor-not-allowed' : ''
        ]"
      />
    </div>

    <!-- Detail Modal -->
    <Modal :show="showModal" size="max-w-2xl" @close="showModal = false">
      <template #body>
        <RentalDetails v-if="selectedRental" :rental="selectedRental" />
      </template>
      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <button v-if="selectedRental?.status === 'reserved'" @click="pickupRental(selectedRental.id); showModal = false" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition-colors">
            <ArrowUp class="w-4 h-4" />
            Mark Picked Up
          </button>
          <button v-if="selectedRental?.status === 'picked_up'" @click="returnRental(selectedRental.id); showModal = false" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors">
            <Undo2 class="w-4 h-4" />
            Mark Returned
          </button>
          <button v-if="selectedRental?.status === 'returned'" @click="completeRental(selectedRental.id); showModal = false" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors">
            <CheckCircle class="w-4 h-4" />
            Complete Rental
          </button>
          <button v-if="selectedRental?.status === 'reserved'" @click="emit('edit', selectedRental); showModal = false" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-colors">
            <Pencil class="w-4 h-4" />
            Edit
          </button>
          <button v-if="selectedRental?.status === 'reserved'" @click="cancelRental(selectedRental.id); showModal = false" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-red-500 hover:bg-red-600 text-white transition-colors">
            <XCircle class="w-4 h-4" />
            Cancel
          </button>
          <button v-if="selectedRental?.status === 'cancelled' || selectedRental?.status === 'completed'" @click="deleteRental(selectedRental.id); showModal = false" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-red-500 hover:bg-red-600 text-white transition-colors">
            <Trash2 class="w-4 h-4" />
            Delete
          </button>
          <button @click="showModal = false" class="px-4 py-2 text-sm font-medium rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Close</button>
        </div>
      </template>
    </Modal>
  </div>
</template>
