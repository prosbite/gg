<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Search, CalendarClock, Eye, Plus, ArrowUp, Undo2, CheckCircle, XCircle, Trash2, Pencil, X } from 'lucide-vue-next'
import RentalForm from '@/Components/sidebar/Rental/RentalForm.vue'
import Modal from '@/Components/sidebar/UI/Modal.vue'
import RentalDetails from '@/Components/sidebar/Rental/RentalDetails.vue'

defineOptions({ layout: MainLayout })

const props = defineProps<{
  rentals: {
    data: Array<{
      id: number
      status: string
      pickup_date: string
      return_date: string
      actual_returned_at: string | null
      created_at: string
      customer: {
        id: number
        first_name: string
        last_name: string
        contact_number: string
      }
      items: Array<{
        id: number
        status: string
        rental_fee: string
        product: { id: number; name: string; item_code: string }
      }>
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  products: Array<{ id: number; item_code: string; name: string; custom_rental_fee: string | null; custom_security_deposit: string | null; status: string }>
  activeTab: string
}>()

const tabs = [
  { key: 'reserved', label: 'Rentals' },
  { key: 'picked_up', label: 'For Pick-ups' },
  { key: 'returned', label: 'For Returns' },
]

const search = ref('')
const showForm = ref(false)
const showModal = ref(false)
const selectedRental = ref<typeof props.rentals.data[0] | null>(null)

const page = usePage()
watch(() => page.props.errors, (errors) => {
  if (errors && Object.keys(errors).length > 0) {
    showForm.value = true
  }
}, { immediate: true })

const openModal = (rental: typeof props.rentals.data[0]) => {
  selectedRental.value = rental
  showModal.value = true
}

let debounceTimer: ReturnType<typeof setTimeout> | null = null

const onSearchInput = () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(route('dashboard'), { tab: props.activeTab, search: search.value || undefined }, {
      preserveState: true,
      replace: true,
    })
  }, 300)
}

const switchTab = (key: string) => {
  router.get(route('dashboard'), { tab: key, search: undefined, page: undefined }, {
    preserveState: true,
    replace: true,
  })
}

watch(() => props.rentals, () => {
  search.value = new URL(window.location.href).searchParams.get('search') || ''
}, { immediate: true })

const statusColors: Record<string, string> = {
  reserved: 'bg-blue-100 text-blue-600',
  picked_up: 'bg-amber-100 text-amber-600',
  returned: 'bg-green-100 text-green-600',
  cancelled: 'bg-red-100 text-red-600',
  completed: 'bg-slate-100 text-slate-600',
}

const getStatusColor = (status: string) => statusColors[status] || statusColors.reserved

const pickupRental = (id: number) => {
  if (confirm('Mark this rental as picked up?')) {
    router.post(route('rentals.pickup', id), {}, { preserveState: true, replace: true })
  }
}

const returnRental = (id: number) => {
  if (confirm('Mark this rental as returned?')) {
    router.post(route('rentals.return', id), {}, { preserveState: true, replace: true })
  }
}

const completeRental = (id: number) => {
  if (confirm('Complete this rental?')) {
    router.post(route('rentals.complete', id), {}, { preserveState: true, replace: true })
  }
}

const cancelRental = (id: number) => {
  if (confirm('Cancel this rental?')) {
    router.post(route('rentals.cancel', id), {}, { preserveState: true, replace: true })
  }
}

const deleteRental = (id: number) => {
  if (confirm('Delete this rental? This will free up the products.')) {
    router.delete(route('rentals.destroy', id))
  }
}

const formatDate = (date: string) => {
  if (!date) return '\u2014'
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<template>
  <Head title="Dashboard" />

  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Dashboard</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Rental management overview</p>
      </div>
      <div class="flex justify-between sm:justify-start items-center gap-4">
        <div class="flex gap-2">
            <CalendarClock class="w-4 h-4 text-slate-400" />
            <span class="text-sm text-slate-500 dark:text-slate-400">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
        </div>
        <button
          @click="showForm = !showForm"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
          :class="showForm ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white'"
        >
          <component :is="showForm ? X : Plus" class="w-4 h-4" /> {{ showForm ? 'Cancel' : 'New Rental' }}
        </button>
      </div>
    </div>

    <div v-show="!showForm">
      <div class="flex gap-1 border-b border-slate-200 dark:border-slate-700">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="switchTab(tab.key)"
        :class="['px-4 py-2 text-sm font-medium transition-colors rounded-t-lg border-b-2 -mb-px', activeTab === tab.key ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300']"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="space-y-4 mt-4">
      <div class="relative">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
        <input
          v-model="search"
          type="text"
          :placeholder="'Search ' + (tabs.find(t => t.key === activeTab)?.label || 'rentals') + '...'"
          @input="onSearchInput"
          class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        />
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 dark:bg-slate-700">
            <tr>
              <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Customer</th>
              <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Items</th>
              <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Pickup Date</th>
              <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Return Date</th>
              <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Status</th>
              <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <tr v-for="rental in rentals.data" :key="rental.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
              <td class="px-4 py-3 cursor-pointer" @click="openModal(rental)">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xs font-bold shrink-0">
                    {{ rental.customer.first_name.charAt(0) }}{{ rental.customer.last_name.charAt(0) }}
                  </div>
                  <div>
                    <p class="text-slate-800 dark:text-slate-200 font-medium">{{ rental.customer.first_name }} {{ rental.customer.last_name }}</p>
                    <p class="text-xs text-slate-400">{{ rental.customer.contact_number }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-col gap-0.5">
                  <span v-for="item in rental.items" :key="item.id" class="text-xs text-slate-600 dark:text-slate-400">
                    {{ item.product.name }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs">{{ formatDate(rental.pickup_date) }}</td>
              <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs">{{ formatDate(rental.return_date) }}</td>
              <td class="px-4 py-3">
                <span :class="['inline-block px-2 py-0.5 text-xs rounded-full font-medium capitalize', getStatusColor(rental.status)]">
                  {{ rental.status.replace('_', ' ') }}
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
              <td colspan="6" class="px-4 py-8 text-center text-slate-400">No {{ tabs.find(t => t.key === activeTab)?.label.toLowerCase() || 'rentals' }} found.</td>
            </tr>
          </tbody>
        </table>
      </div>

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
    </div>
    </div>

    <div v-show="showForm">
      <RentalForm :products="products" @created="showForm = false" />
    </div>
  </div>

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
</template>

