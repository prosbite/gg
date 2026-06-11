<script setup lang="ts">
import { ref, computed, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Plus, X, Package, Search, UserCheck, Camera, Upload } from 'lucide-vue-next'
import { useCustomerSearch, formatCustomerName, type CustomerSearchResult } from '@/composables/useCustomerSearch'

const props = defineProps<{
  products: Array<{ id: number; item_code: string; name: string; custom_rental_fee: string | null; custom_security_deposit: string | null; status: string }>
}>()

const emit = defineEmits<{
  (e: 'created'): void
}>()

const toast = useToast()

const form = useForm({
  customer_id: null as number | null,
  customer: {
    first_name: '',
    last_name: '',
    contact_number: '',
    address: '',
    affiliation: '',
    social_media_link: '',
  },
  pickup_date: '',
  return_date: '',
  internal_notes: '',
  items: [] as Array<{
    _key: number
    product_id: number | null
    description: string
    images: Array<{ id: string; file: File; previewUrl: string }>
    rental_fee: number | ''
    security_deposit: number | ''
  }>,
  payments: [] as Array<{
    amount: number | ''
    type: string
    method: string
    reference_number: string
    receipts: Array<{ id: string; file: File; previewUrl: string }>
  }>,
})

let itemKey = 0
const { searchResults, showSearch, search: debouncedSearch, clearResults } = useCustomerSearch()
const selectedCustomerName = ref('')

const onNameInput = () => {
  const currentName = formatCustomerName(form.customer.first_name, form.customer.last_name)
  if (currentName !== selectedCustomerName.value && form.customer_id) {
    form.customer_id = null
  }
  debouncedSearch(currentName)
}

const selectCustomer = (c: CustomerSearchResult) => {
  form.customer_id = c.id
  form.customer.first_name = c.first_name
  form.customer.last_name = c.last_name
  form.customer.contact_number = c.contact_number ?? ''
  form.customer.address = c.address ?? ''
  form.customer.affiliation = c.affiliation ?? ''
  form.customer.social_media_link = c.social_media_link ?? ''
  selectedCustomerName.value = formatCustomerName(c.first_name, c.last_name)
  clearResults()
}

const addItem = () => {
  form.items.push({
    _key: itemKey++,
    product_id: null,
    description: '',
    images: [],
    rental_fee: '',
    security_deposit: '',
  })
}

const removeItem = (index: number) => {
  form.items[index].images.forEach(img => URL.revokeObjectURL(img.previewUrl))
  form.items.splice(index, 1)
}

const addPayment = () => {
  form.payments.push({
    amount: '',
    type: 'downpayment',
    method: 'cash',
    reference_number: '',
    receipts: [],
  })
}

const removePayment = (index: number) => {
  form.payments[index].receipts.forEach(r => URL.revokeObjectURL(r.previewUrl))
  form.payments.splice(index, 1)
}

const onProductSelect = (index: number) => {
  const productId = form.items[index].product_id
  if (!productId) {
    form.items[index].rental_fee = ''
    form.items[index].security_deposit = ''
    return
  }
  const product = props.products.find(p => p.id === productId)
  if (product) {
    form.items[index].rental_fee = product.custom_rental_fee ? parseFloat(product.custom_rental_fee) : ''
    form.items[index].security_deposit = product.custom_security_deposit ? parseFloat(product.custom_security_deposit) : ''
  }
}

let imgIdCounter = 0
let receiptIdCounter = 0

const fileInput = ref<HTMLInputElement | null>(null)
const videoRef = ref<HTMLVideoElement | null>(null)
const showCamera = ref(false)
const cameraTargetIndex = ref<number | null>(null)
const cameraMode = ref<'item' | 'receipt'>('item')
let cameraStream: MediaStream | null = null

const receiptFileInput = ref<HTMLInputElement | null>(null)
const receiptTargetIndex = ref<number | null>(null)

const addImageToItem = (itemIndex: number, file: File) => {
  const id = `img_${++imgIdCounter}_${Date.now()}`
  const previewUrl = URL.createObjectURL(file)
  form.items[itemIndex].images.push({ id, file, previewUrl })
}

const addReceiptToPayment = (paymentIndex: number, file: File) => {
  const id = `receipt_${++receiptIdCounter}_${Date.now()}`
  const previewUrl = URL.createObjectURL(file)
  form.payments[paymentIndex].receipts.push({ id, file, previewUrl })
}

const startReceiptCamera = (index: number) => startCamera(index, 'receipt')

const onFileSelect = (e: Event) => {
  const files = (e.target as HTMLInputElement).files
  if (files && cameraTargetIndex.value !== null) {
    const idx = cameraTargetIndex.value
    for (let i = 0; i < files.length; i++) {
      addImageToItem(idx, files[i])
    }
  }
  if (e.target) (e.target as HTMLInputElement).value = ''
}

const startCamera = async (index: number, mode: 'item' | 'receipt' = 'item') => {
  cameraTargetIndex.value = index
  cameraMode.value = mode
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    cameraStream = stream
    showCamera.value = true
    await nextTick()
    if (videoRef.value) videoRef.value.srcObject = stream
  } catch {
    alert('Camera access denied or not available')
  }
}

const capturePhoto = () => {
  if (!videoRef.value || cameraTargetIndex.value === null) return
  const canvas = document.createElement('canvas')
  canvas.width = videoRef.value.videoWidth
  canvas.height = videoRef.value.videoHeight
  canvas.getContext('2d')!.drawImage(videoRef.value, 0, 0)
  canvas.toBlob((blob) => {
    if (!blob) return
    const file = new File([blob], `photo_${Date.now()}.jpg`, { type: 'image/jpeg' })
    if (cameraMode.value === 'receipt') {
      addReceiptToPayment(cameraTargetIndex.value!, file)
    } else {
      addImageToItem(cameraTargetIndex.value!, file)
    }
  }, 'image/jpeg')
}

const stopCamera = () => {
  cameraStream?.getTracks().forEach(track => track.stop())
  cameraStream = null
  showCamera.value = false
  cameraTargetIndex.value = null
  cameraMode.value = 'item'
}

const selectItemImages = (index: number) => {
  cameraTargetIndex.value = index
  fileInput.value?.click()
}

const removeItemImage = (itemIndex: number, imageId: string) => {
  const imgs = form.items[itemIndex].images
  const idx = imgs.findIndex(img => img.id === imageId)
  if (idx !== -1) {
    URL.revokeObjectURL(imgs[idx].previewUrl)
    imgs.splice(idx, 1)
  }
}

const selectPaymentReceipt = (index: number) => {
  receiptTargetIndex.value = index
  receiptFileInput.value?.click()
}

const onReceiptFileSelect = (e: Event) => {
  const files = (e.target as HTMLInputElement).files
  if (files && receiptTargetIndex.value !== null) {
    const idx = receiptTargetIndex.value
    for (let i = 0; i < files.length; i++) {
      const id = `receipt_${++receiptIdCounter}_${Date.now()}`
      const previewUrl = URL.createObjectURL(files[i])
      form.payments[idx].receipts.push({ id, file: files[i], previewUrl })
    }
  }
  if (e.target) (e.target as HTMLInputElement).value = ''
}

const removePaymentReceipt = (pIndex: number, receiptId: string) => {
  const receipts = form.payments[pIndex].receipts
  const idx = receipts.findIndex(r => r.id === receiptId)
  if (idx !== -1) {
    URL.revokeObjectURL(receipts[idx].previewUrl)
    receipts.splice(idx, 1)
  }
}

const availableProducts = computed(() =>
  props.products.filter(p => p.status === 'available')
)

const submit = () => {
  const hasImages = form.items.some(item => item.images.length > 0) ||
                     form.payments.some(p => p.receipts.length > 0)
  const allPreviews: string[] = []
  form.items.forEach(item => item.images.forEach(img => allPreviews.push(img.previewUrl)))
  form.payments.forEach(p => p.receipts.forEach(r => allPreviews.push(r.previewUrl)))
  form.post(route('rentals.store'), {
    forceFormData: hasImages,
    onSuccess: () => {
      allPreviews.forEach(url => URL.revokeObjectURL(url))
      form.reset()
      clearResults()
      selectedCustomerName.value = ''
      itemKey = 0
      toast.success('Rental created successfully.')
      emit('created')
    },
  })
}

const today = new Date().toISOString().split('T')[0]

const onPickupChange = () => {
  if (form.pickup_date) {
    const d = new Date(form.pickup_date)
    d.setDate(d.getDate() + 2)
    if (d.getDay() === 6) d.setDate(d.getDate() + 1)
    form.return_date = d.toISOString().split('T')[0]
  }
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
    <div class="flex items-center gap-2 mb-4">
      <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
        <Package class="w-4 h-4 text-blue-500" />
      </div>
      <h2 class="text-lg font-semibold text-slate-800 dark:text-white">New Rental</h2>
    </div>

    <form @submit.prevent="submit" class="space-y-3">
      <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3">
        <div class="flex items-center gap-1.5 mb-2">
          <Search class="w-3.5 h-3.5 text-slate-400" />
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Customer</span>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div class="relative">
            <label class="block text-xs font-medium text-slate-500 mb-0.5">First Name</label>
            <input v-model="form.customer.first_name" type="text" required @input="onNameInput" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
            <p v-if="form.errors['customer.first_name']" class="text-xs text-red-500 mt-0.5">{{ form.errors['customer.first_name'] }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-0.5">Last Name</label>
            <input v-model="form.customer.last_name" type="text" required @input="onNameInput" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
            <p v-if="form.errors['customer.last_name']" class="text-xs text-red-500 mt-0.5">{{ form.errors['customer.last_name'] }}</p>
          </div>
        </div>

        <div v-if="showSearch" class="mt-1 border border-blue-200 dark:border-blue-700 rounded-lg bg-blue-50 dark:bg-blue-900/20 max-h-32 overflow-y-auto divide-y divide-blue-100 dark:divide-blue-800">
          <button
            v-for="c in searchResults"
            :key="c.id"
            type="button"
            @click="selectCustomer(c)"
            class="w-full text-left px-3 py-1.5 text-xs hover:bg-blue-100 dark:hover:bg-blue-800/40 transition-colors flex items-center gap-2"
          >
            <UserCheck class="w-3 h-3 text-blue-500 shrink-0" />
            <span class="font-medium text-slate-700 dark:text-slate-200">{{ c.first_name }} {{ c.last_name }}</span>
            <span class="text-slate-400">{{ c.contact_number }}</span>
          </button>
        </div>

        <div class="grid grid-cols-2 gap-2 mt-2">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-0.5">Contact Number</label>
            <input v-model="form.customer.contact_number" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
            <p v-if="form.errors['customer.contact_number']" class="text-xs text-red-500 mt-0.5">{{ form.errors['customer.contact_number'] }}</p>
          </div>
          <div class="col-span-2">
            <label class="block text-xs font-medium text-slate-500 mb-0.5">Address</label>
            <input v-model="form.customer.address" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
            <p v-if="form.errors['customer.address']" class="text-xs text-red-500 mt-0.5">{{ form.errors['customer.address'] }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-0.5">Affiliation <span class="text-slate-400">(optional)</span></label>
            <input v-model="form.customer.affiliation" type="text" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-0.5">Social Media Link <span class="text-slate-400">(optional)</span></label>
            <input v-model="form.customer.social_media_link" type="url" placeholder="https://" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>
        </div>

        <p v-if="form.errors.customer_id" class="text-xs text-red-500 mt-1">{{ form.errors.customer_id }}</p>
        <p v-if="form.customer_id" class="text-xs text-green-600 dark:text-green-400 mt-1 flex items-center gap-1">
          <UserCheck class="w-3 h-3" /> Existing customer selected
        </p>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pickup Date</label>
          <input v-model="form.pickup_date" type="date" :min="today" required @change="onPickupChange" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          <p v-if="form.errors.pickup_date" class="text-xs text-red-500 mt-1">{{ form.errors.pickup_date }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Return Date</label>
          <input v-model="form.return_date" type="date" :min="form.pickup_date || today" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          <p v-if="form.errors.return_date" class="text-xs text-red-500 mt-1">{{ form.errors.return_date }}</p>
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Items</label>
          <button type="button" @click="addItem" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
            <Plus class="w-3.5 h-3.5" /> Add Item
          </button>
        </div>

        <div v-if="form.items.length === 0" class="text-xs text-slate-400 text-center py-4 border border-dashed border-slate-300 dark:border-slate-600 rounded-lg">
          No items yet. Click "Add Item" to add rental products.
        </div>

        <div v-for="(item, index) in form.items" :key="item._key" class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 mb-2 space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 uppercase">Item {{ index + 1 }}</span>
            <button type="button" @click="removeItem(index)" class="p-0.5 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-colors">
              <X class="w-3.5 h-3.5" />
            </button>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Select Product</label>
            <select v-model="item.product_id" @change="onProductSelect(index)" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
              <option :value="null">Pick existing product...</option>
              <option v-for="p in availableProducts" :key="p.id" :value="p.id">{{ p.item_code }} â€” {{ p.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Or Quick-Add New Product</label>
            <div class="space-y-2">
              <div v-if="item.images.length > 0" class="flex flex-wrap gap-2">
                <div v-for="img in item.images" :key="img.id" class="relative w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600">
                  <img :src="img.previewUrl" class="w-full h-full object-cover" />
                  <button type="button" @click="removeItemImage(index, img.id)" class="absolute top-0.5 right-0.5 bg-red-500 hover:bg-red-600 text-white rounded-full p-0.5">
                    <X class="w-3 h-3" />
                  </button>
                </div>
              </div>
              <div class="flex gap-2">
                <button
                  type="button"
                  @click="startCamera(index)"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-blue-300 dark:border-blue-600 bg-white dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600 dark:text-blue-400 transition-colors"
                >
                  <Camera class="w-3.5 h-3.5" />
                  Take Photo
                </button>
                <button
                  type="button"
                  @click="selectItemImages(index)"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 transition-colors"
                >
                  <Upload class="w-3.5 h-3.5" />
                  Upload Photos
                </button>
              </div>
              <textarea v-model="item.description" rows="2" placeholder="Describe the item (will be edited later)..." class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
            </div>
          </div>

          <p v-if="form.errors['items.' + index + '.product_id'] || form.errors['items.' + index + '.description']" class="text-xs text-red-500 mt-1">Please select a product or provide a photo and description for this item.</p>
        </div>
        <p v-if="form.errors.items" class="text-xs text-red-500 mt-1">{{ form.errors.items }}</p>
      </div>

      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Payments <span class="text-xs text-slate-400">(optional)</span></label>
          <button type="button" @click="addPayment" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
            <Plus class="w-3.5 h-3.5" /> Add Payment
          </button>
        </div>

        <div v-for="(payment, index) in form.payments" :key="index" class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 mb-2 space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 uppercase">Payment {{ index + 1 }}</span>
            <button type="button" @click="removePayment(index)" class="p-0.5 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-colors">
              <X class="w-3.5 h-3.5" />
            </button>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1">Amount</label>
              <input v-model="payment.amount" type="number" step="0.01" min="0" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
              <select v-model="payment.type" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="downpayment">Downpayment</option>
                <option value="balance">Balance</option>
                <option value="security_deposit">Security Deposit</option>
                <option value="penalty">Penalty</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1">Method</label>
              <select v-model="payment.method" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="cash">Cash</option>
                <option value="gcash">GCash</option>
                <option value="bank_transfer">Bank Transfer</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1">Reference #</label>
              <input v-model="payment.reference_number" type="text" placeholder="Ref # (optional)" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Receipt <span class="text-slate-400">(optional)</span></label>
            <div class="flex flex-wrap gap-2 mb-2">
              <div v-for="r in payment.receipts" :key="r.id" class="relative w-16 h-16 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600">
                <img :src="r.previewUrl" class="w-full h-full object-cover" />
                <button type="button" @click="removePaymentReceipt(index, r.id)" class="absolute top-0.5 right-0.5 bg-red-500 hover:bg-red-600 text-white rounded-full p-0.5">
                  <X class="w-3 h-3" />
                </button>
              </div>
            </div>
            <div class="flex gap-2">
              <button type="button" @click="startReceiptCamera(index)" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-lg border border-blue-300 dark:border-blue-600 bg-white dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600 dark:text-blue-400 transition-colors">
                <Camera class="w-3 h-3" /> Take Photo
              </button>
              <button type="button" @click="selectPaymentReceipt(index)" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 transition-colors">
                <Upload class="w-3 h-3" /> Upload Receipt
              </button>
            </div>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Internal Notes</label>
        <textarea v-model="form.internal_notes" rows="2" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
      </div>

      <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="onFileSelect" />
      <input ref="receiptFileInput" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="onReceiptFileSelect" />

      <div v-if="showCamera" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center" @click.self="stopCamera">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 max-w-sm w-full mx-4">
          <video ref="videoRef" autoplay playsinline class="w-full rounded-lg bg-black" />
          <div class="flex gap-2 mt-3">
            <button type="button" @click="capturePhoto" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm">Capture</button>
            <button type="button" @click="stopCamera" class="flex-1 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg px-4 py-2 text-sm">Cancel</button>
          </div>
        </div>
      </div>

      <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50">
        {{ form.processing ? 'Saving...' : 'Create Rental' }}
      </button>
    </form>
  </div>
</template>


