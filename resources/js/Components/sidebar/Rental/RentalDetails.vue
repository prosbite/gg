<script setup lang="ts">
import { ref } from 'vue'
import { Package, CalendarClock, CreditCard } from 'lucide-vue-next'
import ImagePreview from '@/Components/sidebar/UI/ImagePreview.vue'

const previewImages = ref<string[]>([])
const previewIndex = ref(0)

const openPreview = (images: string[], index: number) => {
  previewImages.value = images
  previewIndex.value = index
}

const props = defineProps<{
  rental: {
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
      product: { id: number; name: string; item_code: string; is_draft: boolean; description: string | null; images?: Array<{ id: number; file_path: string; thumbnail_path: string | null; label: string | null; is_primary: boolean; sort_order: number }> }
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
  }
}>()

const statusColors: Record<string, string> = {
  reserved: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
  picked_up: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
  returned: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
  cancelled: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
  completed: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
}

const formatDate = (date: string) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const totalFee = () => props.rental.items.reduce((sum, item) => sum + parseFloat(item.rental_fee), 0)
const totalDeposit = () => props.rental.items.reduce((sum, item) => sum + parseFloat(item.security_deposit), 0)
const totalPaid = () => props.rental.payments.reduce((sum, p) => sum + parseFloat(p.amount), 0)

const typeColors: Record<string, string> = {
  downpayment: 'bg-blue-100 text-blue-600',
  balance: 'bg-green-100 text-green-600',
  security_deposit: 'bg-purple-100 text-purple-600',
  penalty: 'bg-red-100 text-red-600',
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
        <Package class="w-5 h-5 text-blue-500" />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Rental #{{ rental.id }}</h3>
        <p class="text-xs text-slate-500">Created {{ formatDate(rental.created_at) }}</p>
      </div>
      <div class="ml-auto">
        <span :class="['inline-block px-2.5 py-1 text-xs rounded-full font-medium capitalize', statusColors[rental.status]]">
          {{ rental.status.replace(/_/g, ' ') }}
        </span>
      </div>
    </div>

    <hr class="border-slate-200 dark:border-slate-700">

    <!-- Customer -->
    <div>
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Customer</h4>
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold">
          {{ rental.customer.first_name.charAt(0) }}{{ rental.customer.last_name.charAt(0) }}
        </div>
        <div>
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ rental.customer.first_name }} {{ rental.customer.last_name }}</p>
          <p v-if="rental.customer.contact_number" class="text-xs text-slate-400">{{ rental.customer.contact_number }}</p>
        </div>
      </div>
    </div>

    <!-- Dates -->
    <div>
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Schedule</h4>
      <div class="grid grid-cols-3 gap-3 text-sm">
        <div>
          <span class="block text-xs text-slate-400">Pickup</span>
          <span class="text-slate-700 dark:text-slate-300 font-medium">{{ formatDate(rental.pickup_date) }}</span>
        </div>
        <div>
          <span class="block text-xs text-slate-400">Return Due</span>
          <span class="text-slate-700 dark:text-slate-300 font-medium">{{ formatDate(rental.return_date) }}</span>
        </div>
        <div>
          <span class="block text-xs text-slate-400">Actual Return</span>
          <span class="text-slate-700 dark:text-slate-300 font-medium">{{ formatDate(rental.actual_returned_at || '') }}</span>
        </div>
      </div>
    </div>

    <hr class="border-slate-200 dark:border-slate-700">

    <!-- Items -->
    <div>
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Items ({{ rental.items.length }})</h4>
      <div class="space-y-2">
        <div v-for="item in rental.items" :key="item.id" class="border border-slate-200 dark:border-slate-700 rounded-lg p-3">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                <span v-if="item.product.is_draft" class="inline-block w-2 h-2 rounded-full bg-purple-400 shrink-0" title="Draft product" />
                {{ item.product.name }}
              </p>
              <p class="text-xs text-slate-400 font-mono">{{ item.product.item_code }}</p>
            </div>
            <span :class="['inline-block px-2 py-0.5 text-[11px] rounded-full font-medium capitalize', statusColors[item.status] || 'bg-slate-100 text-slate-600']">
              {{ item.status.replace(/_/g, ' ') }}
            </span>
          </div>
          <div v-if="item.product.description" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ item.product.description }}</div>
          <div v-if="item.product.images && item.product.images.length > 0" class="flex flex-wrap gap-1.5 mt-2">
            <button v-for="(img, i) in item.product.images" :key="img.id" type="button" @click="openPreview(item.product.images.map(x => '/storage/' + x.file_path), i)" class="w-14 h-14 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 cursor-pointer hover:ring-2 hover:ring-blue-400 transition-shadow">
              <img :src="'/storage/' + img.file_path" class="w-full h-full object-cover" />
            </button>
          </div>
          <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
            <div>
              <span class="text-slate-400">Fee:</span>
              <span class="text-slate-700 dark:text-slate-300 ml-1 font-medium">₱{{ parseFloat(item.rental_fee).toLocaleString() }}</span>
            </div>
            <div>
              <span class="text-slate-400">Deposit:</span>
              <span class="text-slate-700 dark:text-slate-300 ml-1 font-medium">₱{{ parseFloat(item.security_deposit).toLocaleString() }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Totals -->
    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50 dark:bg-slate-700/50">
      <div class="flex justify-between text-sm">
        <span class="text-slate-500">Total Rental Fee</span>
        <span class="font-semibold text-slate-700 dark:text-slate-300">₱{{ totalFee().toLocaleString() }}</span>
      </div>
      <div class="flex justify-between text-sm mt-1">
        <span class="text-slate-500">Total Security Deposit</span>
        <span class="font-semibold text-slate-700 dark:text-slate-300">₱{{ totalDeposit().toLocaleString() }}</span>
      </div>
      <div class="flex justify-between text-sm mt-1 pt-1 border-t border-slate-200 dark:border-slate-600">
        <span class="text-slate-500">Total Paid</span>
        <span class="font-semibold text-green-600 dark:text-green-400">₱{{ totalPaid().toLocaleString() }}</span>
      </div>
      <div class="flex justify-between text-sm mt-1">
        <span class="text-slate-500">Balance</span>
        <span :class="['font-semibold', (totalFee() - totalPaid()) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400']">
          ₱{{ Math.max(0, totalFee() - totalPaid()).toLocaleString() }}
        </span>
      </div>
    </div>

    <!-- Payments -->
    <div v-if="rental.payments.length > 0">
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Payments ({{ rental.payments.length }})</h4>
      <div class="space-y-1.5">
        <div v-for="payment in rental.payments" :key="payment.id" class="flex items-center justify-between border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2">
          <div class="flex items-center gap-2">
            <CreditCard class="w-3.5 h-3.5 text-slate-400" />
            <div>
              <span :class="['inline-block px-1.5 py-0.5 text-[10px] rounded-full font-medium capitalize', typeColors[payment.type] || 'bg-slate-100 text-slate-600']">
                {{ payment.type.replace(/_/g, ' ') }}
              </span>
              <span class="text-[11px] text-slate-400 ml-1.5 capitalize">{{ payment.method.replace(/_/g, ' ') }}</span>
              <span v-if="payment.reference_number" class="text-[11px] text-slate-400 ml-1.5 font-mono">#{{ payment.reference_number }}</span>
              <div v-if="payment.receipts && payment.receipts.length > 0" class="flex flex-wrap gap-1 mt-1.5">
                <button v-for="(r, ri) in payment.receipts" :key="r.id" type="button" @click="openPreview(payment.receipts.map(x => '/storage/' + x.file_path), ri)" class="w-10 h-10 rounded overflow-hidden border border-slate-200 dark:border-slate-600 cursor-pointer hover:ring-2 hover:ring-blue-400 transition-shadow">
                  <img :src="'/storage/' + r.file_path" class="w-full h-full object-cover" />
                </button>
              </div>
            </div>
          </div>
          <span class="text-sm font-semibold text-green-600 dark:text-green-400">₱{{ parseFloat(payment.amount).toLocaleString() }}</span>
        </div>
      </div>
    </div>

    <!-- Notes -->
    <div v-if="rental.internal_notes">
      <hr class="border-slate-200 dark:border-slate-700 mb-3">
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Internal Notes</h4>
      <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ rental.internal_notes }}</p>
    </div>
  </div>

  <ImagePreview :images="previewImages" :start-index="previewIndex" @close="previewImages = []; previewIndex = 0" />
</template>
