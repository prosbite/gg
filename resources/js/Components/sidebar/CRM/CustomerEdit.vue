<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { usePhotoCapture } from '@/composables/usePhotoCapture'
import { useToast } from 'vue-toastification'

const props = defineProps<{
  customer: {
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
  }
}>()

const emit = defineEmits<{
  (e: 'cancel'): void
}>()

const form = useForm({
  first_name: props.customer.first_name,
  last_name: props.customer.last_name,
  contact_number: props.customer.contact_number ?? '',
  email: props.customer.email ?? '',
  address: props.customer.address ?? '',
  affiliation: props.customer.affiliation ?? '',
  social_media_link: props.customer.social_media_link ?? '',
  identification: null as File | null,
  detail_info: props.customer.detail_info ? JSON.stringify(props.customer.detail_info) : '',
  is_blacklisted: props.customer.is_blacklisted,
  admin_notes: props.customer.admin_notes ?? '',
})

const toast = useToast()
const { previewUrl, fileInput, videoRef, showCamera, onFileSelect, startCamera, capturePhoto, stopCamera, removePhoto } = usePhotoCapture()

const onIdFileSelect = (e: Event) => {
  onFileSelect(e, (file) => { form.identification = file })
}

const captureIdPhoto = () => {
  capturePhoto((file) => { form.identification = file })
}

const removeIdPhoto = () => {
  form.identification = null
  removePhoto()
}

const submit = () => {
  form.post(route('customers.update', props.customer.id), {
    _method: 'put',
    onSuccess: () => {
      toast.success('Customer updated successfully.')
      emit('cancel')
    },
    forceFormData: true,
  })
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
    <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Edit Customer</h2>
    <form @submit.prevent="submit" class="space-y-3">
      <!-- Existing identification photo -->
      <div v-if="customer.identification && !previewUrl" class="relative w-full max-w-xs">
        <img :src="'/storage/' + customer.identification" class="w-full h-40 object-cover rounded-lg border border-slate-300 dark:border-slate-600" />
      </div>

      <!-- Identification upload -->
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ previewUrl || form.identification ? 'Change' : 'Upload' }} Identification</label>
        <div class="flex flex-col items-center gap-2">
          <div v-if="previewUrl" class="relative w-full max-w-xs">
            <img :src="previewUrl" class="w-full h-40 object-cover rounded-lg border border-slate-300 dark:border-slate-600" />
            <button type="button" @click="removeIdPhoto" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">✕</button>
          </div>
          <div v-else class="flex gap-2 w-full">
            <button type="button" @click="fileInput?.click()" class="flex-1 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg py-6 text-center text-sm text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
              Upload Photo
            </button>
            <button type="button" @click="startCamera" class="flex-1 border-2 border-dashed border-blue-300 dark:border-blue-600 rounded-lg py-6 text-center text-sm text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors cursor-pointer">
              📷 Capture
            </button>
          </div>
        </div>
        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onIdFileSelect" />
      </div>

      <!-- Camera modal -->
      <div v-if="showCamera" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center" @click.self="stopCamera">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 max-w-sm w-full mx-4">
          <video ref="videoRef" autoplay playsinline class="w-full rounded-lg bg-black" />
          <div class="flex gap-2 mt-3">
            <button type="button" @click="captureIdPhoto" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm">Capture</button>
            <button type="button" @click="stopCamera" class="flex-1 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg px-4 py-2 text-sm">Cancel</button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">First Name</label>
          <input v-model="form.first_name" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          <p v-if="form.errors.first_name" class="text-xs text-red-500 mt-1">{{ form.errors.first_name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Last Name</label>
          <input v-model="form.last_name" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          <p v-if="form.errors.last_name" class="text-xs text-red-500 mt-1">{{ form.errors.last_name }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Contact Number</label>
          <input v-model="form.contact_number" type="text" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
          <input v-model="form.email" type="email" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Address</label>
        <input v-model="form.address" type="text" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Affiliation</label>
        <input v-model="form.affiliation" type="text" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Social Media Link</label>
        <input v-model="form.social_media_link" type="url" placeholder="https://" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Detail Info (JSON)</label>
        <textarea v-model="form.detail_info" rows="3" placeholder='{"key": "value"}' class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
      </div>

      <div class="flex items-center gap-2">
        <input v-model="form.is_blacklisted" type="checkbox" id="is_blacklisted" class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500" />
        <label for="is_blacklisted" class="text-sm font-medium text-slate-700 dark:text-slate-300">Blacklisted</label>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Admin Notes</label>
        <textarea v-model="form.admin_notes" rows="2" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
      </div>

      <!-- Buttons -->
      <div class="flex gap-2">
        <button type="submit" :disabled="form.processing" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50">
          {{ form.processing ? 'Updating...' : 'Update Customer' }}
        </button>
        <button type="button" @click="emit('cancel')" class="flex-1 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg px-4 py-2 text-sm font-medium transition-colors">
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>
