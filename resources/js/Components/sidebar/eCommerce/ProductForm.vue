<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Check, Upload, Camera, X } from 'lucide-vue-next'
import { useImageGallery } from '@/composables/useImageGallery'

const props = defineProps<{
  categories: Array<{ id: number; name: string; tag_types: Array<{ id: number }> }>
  allTagTypes: Array<{ id: number; name: string; tags: Array<{ id: number; name: string }> }>
  mode?: 'full' | 'quick'
}>()

const form = useForm({
  item_code: '',
  name: '',
  category_id: '',
  custom_rental_fee: '',
  custom_security_deposit: '',
  status: 'available',
  description: '',
  specifics: '',
  is_active: true,
  is_draft: props.mode === 'quick',
  tags: [] as number[],
  images: [] as Array<{ file: File | null; label: string }>,
})

const toast = useToast()

const {
  images,
  fileInput,
  videoRef,
  showCamera,
  isMobile,
  cameraFileInput,
  onFileSelect,
  startCamera,
  onCameraCapture,
  capturePhoto,
  stopCamera,
  removeImage,
  updateLabel,
  clearAll,
} = useImageGallery()

const selectedTagIds = ref<Set<number>>(new Set())

const selectedCategoryTagTypes = computed(() => {
  if (!form.category_id) return []
  const cat = props.categories.find(c => c.id === Number(form.category_id))
  if (!cat) return []
  const typeIds = new Set(cat.tag_types.map(tt => tt.id))
  return props.allTagTypes.filter(tt => typeIds.has(tt.id))
})

const toggleTag = (tagId: number) => {
  const next = new Set(selectedTagIds.value)
  if (next.has(tagId)) {
    next.delete(tagId)
  } else {
    next.add(tagId)
  }
  selectedTagIds.value = next
  form.tags = Array.from(next)
}

watch(() => form.category_id, () => {
  selectedTagIds.value = new Set()
  form.tags = []
})

const submit = () => {
  form.images = images.value.map(img => ({
    file: img.file,
    label: img.label,
  }))

  form.post(route('products.store'), {
    onSuccess: () => {
      form.reset()
      clearAll()
      selectedTagIds.value = new Set()
      toast.success('Product created successfully.')
    },
  })
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
    <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Add Product</h2>
    <form @submit.prevent="submit" class="space-y-3">
      <div v-if="mode !== 'quick'">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Item Code</label>
        <input v-model="form.item_code" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
        <p v-if="form.errors.item_code" class="text-xs text-red-500 mt-1">{{ form.errors.item_code }}</p>
        <p v-else-if="form.is_draft" class="text-xs text-slate-400 mt-1">Auto-generated for draft products.</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
        <input v-model="form.name" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
      </div>

      <div v-if="mode !== 'quick'">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
        <select v-model="form.category_id" :required="mode !== 'quick'" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
          <option value="" disabled>Select category...</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
        <p v-if="form.errors.category_id" class="text-xs text-red-500 mt-1">{{ form.errors.category_id }}</p>
        <p v-else-if="form.is_draft" class="text-xs text-slate-400 mt-1">Auto-assigned on save.</p>
      </div>

      <div v-if="form.category_id && mode !== 'quick'">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tags</label>
        <div v-if="selectedCategoryTagTypes.length === 0" class="text-xs text-slate-400">No tag types configured for this category.</div>
        <div v-for="tt in selectedCategoryTagTypes" :key="tt.id" class="mb-3">
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">{{ tt.name }}</p>
          <div class="flex flex-wrap gap-1.5">
            <button
              v-for="tag in tt.tags"
              :key="tag.id"
              type="button"
              @click="toggleTag(tag.id)"
              :class="[
                'inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full font-medium border transition-colors',
                selectedTagIds.has(tag.id)
                  ? 'bg-blue-100 text-blue-600 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400'
                  : 'bg-white text-slate-600 border-slate-200 hover:border-blue-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600'
              ]"
            >
              <Check v-if="selectedTagIds.has(tag.id)" class="w-3 h-3" />
              {{ tag.name }}
            </button>
          </div>
        </div>
        <p v-if="form.errors.tags" class="text-xs text-red-500 mt-1">{{ form.errors.tags }}</p>
      </div>
      <div v-if="mode !== 'quick'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Rental Fee <span class="text-xs text-slate-400">(blank = category default)</span></label>
          <input v-model="form.custom_rental_fee" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          <p v-if="form.errors.custom_rental_fee" class="text-xs text-red-500 mt-1">{{ form.errors.custom_rental_fee }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Security Deposit <span class="text-xs text-slate-400">(blank = category default)</span></label>
          <input v-model="form.custom_security_deposit" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          <p v-if="form.errors.custom_security_deposit" class="text-xs text-red-500 mt-1">{{ form.errors.custom_security_deposit }}</p>
        </div>
      </div>

      <div v-if="mode !== 'quick'">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
        <select v-model="form.status" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
          <option value="available">Available</option>
          <option value="rented">Rented</option>
          <option value="maintenance">Maintenance</option>
          <option value="retired">Retired</option>
        </select>
        <p v-if="form.errors.status" class="text-xs text-red-500 mt-1">{{ form.errors.status }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
        <textarea v-model="form.description" rows="2" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
      </div>

      <div v-if="mode !== 'quick'">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Specifics <span class="text-xs text-slate-400">(JSON)</span></label>
        <textarea v-model="form.specifics" rows="2" placeholder='{"size": "M", "color": "Red"}' class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
        <p v-if="form.errors.specifics" class="text-xs text-red-500 mt-1">{{ form.errors.specifics }}</p>
      </div>

      <div v-if="mode !== 'quick'" class="flex items-center gap-2">
        <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500" />
        <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Active</label>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Images</label>

        <div v-if="images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-3">
          <div
            v-for="img in images"
            :key="img.id"
            class="relative group bg-slate-100 dark:bg-slate-700 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600"
          >
            <img :src="img.previewUrl" class="w-full h-28 object-cover" />
            <button
              type="button"
              @click="removeImage(img.id)"
              class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition-opacity"
            >
              <X class="w-3.5 h-3.5" />
            </button>
            <div class="p-1.5">
              <input
                :value="img.label"
                @input="updateLabel(img.id, ($event.target as HTMLInputElement).value)"
                placeholder="Label (e.g. Front View)"
                class="w-full text-xs rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-2 py-1 focus:ring-1 focus:ring-blue-500 outline-none"
              />
            </div>
          </div>
        </div>

        <input
          ref="cameraFileInput"
          type="file"
          accept="image/*"
          capture="environment"
          class="hidden"
          @change="onCameraCapture"
        />

        <div v-if="showCamera && !isMobile" class="mb-3 bg-black rounded-lg overflow-hidden">
          <video ref="videoRef" autoplay playsinline muted class="w-full h-40 object-cover" />
          <div class="flex justify-center gap-2 p-2 bg-slate-900">
            <button
              type="button"
              @click="capturePhoto"
              class="bg-blue-600 hover:bg-blue-700 text-white rounded-full px-4 py-1.5 text-xs font-medium"
            >
              Capture
            </button>
            <button
              type="button"
              @click="stopCamera"
              class="bg-slate-600 hover:bg-slate-700 text-white rounded-full px-4 py-1.5 text-xs font-medium"
            >
              Cancel
            </button>
          </div>
        </div>

        <div class="flex flex-wrap gap-2">
          <input
            ref="fileInput"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            multiple
            class="hidden"
            @change="onFileSelect"
          />
          <button
            type="button"
            @click="fileInput?.click()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 transition-colors"
          >
            <Upload class="w-3.5 h-3.5" />
            Upload File
          </button>
          <button
            type="button"
            @click="startCamera"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 transition-colors"
          >
            <Camera class="w-3.5 h-3.5" />
            Take Photo
          </button>
        </div>
        <p v-if="form.errors.images" class="text-xs text-red-500 mt-1">{{ form.errors.images }}</p>
      </div>

      <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50">
        {{ form.processing ? 'Saving...' : mode === 'quick' ? 'Quick Add Product' : 'Save Product' }}
      </button>
    </form>
  </div>
</template>
