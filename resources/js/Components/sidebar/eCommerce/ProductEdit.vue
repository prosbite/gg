<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Check, Upload, Camera, X, Trash2, Undo2 } from 'lucide-vue-next'
import { useImageGallery } from '@/composables/useImageGallery'

const props = defineProps<{
  product: {
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
    images?: Array<{ id: number; file_path: string; thumbnail_path: string | null; label: string | null; is_primary: boolean; sort_order: number }>
  }
  categories: Array<{ id: number; name: string; tag_types: Array<{ id: number }> }>
  allTagTypes: Array<{ id: number; name: string; tags: Array<{ id: number; name: string }> }>
}>()

const emit = defineEmits<{
  (e: 'cancel'): void
}>()

const form = useForm({
  item_code: props.product.item_code,
  name: props.product.name,
  category_id: props.product.category_id,
  custom_rental_fee: props.product.custom_rental_fee ?? '',
  custom_security_deposit: props.product.custom_security_deposit ?? '',
  status: props.product.status,
  description: props.product.description ?? '',
  specifics: props.product.specifics ? JSON.stringify(props.product.specifics) : '',
  is_active: props.product.is_active,
  is_draft: props.product.is_draft,
  tags: props.product.tags.map(t => t.id),
  new_images: [] as Array<{ file: File | null; label: string }>,
  delete_images: [] as number[],
})

const toast = useToast()

const {
  images: newImages,
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
  removeImage: removeNewImage,
  updateLabel: updateNewLabel,
  clearAll,
} = useImageGallery()

const selectedTagIds = ref<Set<number>>(new Set(props.product.tags.map(t => t.id)))

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

const markedForDeletion = ref<Set<number>>(new Set())

const toggleDeleteImage = (imageId: number) => {
  const next = new Set(markedForDeletion.value)
  if (next.has(imageId)) {
    next.delete(imageId)
  } else {
    next.add(imageId)
  }
  markedForDeletion.value = next
}

const submit = () => {
  form.delete_images = Array.from(markedForDeletion.value)
  form.new_images = newImages.value.map(img => ({
    file: img.file,
    label: img.label,
  }))

  form.put(route('products.update', props.product.id), {
    onSuccess: () => {
      toast.success('Product updated successfully.')
      emit('cancel')
    },
  })
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
    <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Edit Product</h2>
    <form @submit.prevent="submit" class="space-y-3">
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Item Code</label>
        <input v-model="form.item_code" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
        <p v-if="form.errors.item_code" class="text-xs text-red-500 mt-1">{{ form.errors.item_code }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
        <input v-model="form.name" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
        <select v-model="form.category_id" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
          <option value="" disabled>Select category...</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
        <p v-if="form.errors.category_id" class="text-xs text-red-500 mt-1">{{ form.errors.category_id }}</p>
      </div>

       <div v-if="form.category_id">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tags</label>
        <div v-if="selectedCategoryTagTypes.length === 0" class="text-xs text-slate-400">No tag types configured for this category.</div>
        <div v-for="tt in selectedCategoryTagTypes" :key="tt.id" class="mb-3">
          <p class="text-xs font-bold text-purple-700 dark:text-slate-400 uppercase tracking-wide mb-1.5">{{ tt.name }}</p>
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
      <div v-else>
        <p class="text-xs text-slate-400">Select a category to choose tags.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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

      <div>
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

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Specifics <span class="text-xs text-slate-400">(JSON)</span></label>
        <textarea v-model="form.specifics" rows="2" placeholder='{"size": "M", "color": "Red"}' class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
        <p v-if="form.errors.specifics" class="text-xs text-red-500 mt-1">{{ form.errors.specifics }}</p>
      </div>

      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
          <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500" />
          <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Active</label>
        </div>
        <div class="flex items-center gap-2">
          <input v-model="form.is_draft" type="checkbox" id="is_draft" class="rounded border-slate-300 dark:border-slate-600 text-purple-600 focus:ring-purple-500" />
          <label for="is_draft" class="text-sm font-medium text-slate-700 dark:text-slate-300">Draft</label>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Images</label>

        <!-- Existing images -->
        <div v-if="product.images && product.images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-3">
          <div
            v-for="img in product.images"
            :key="img.id"
            :class="[
              'relative group bg-slate-100 dark:bg-slate-700 rounded-lg overflow-hidden border transition-opacity',
              markedForDeletion.has(img.id) ? 'opacity-40 border-red-400' : 'border-slate-200 dark:border-slate-600'
            ]"
          >
            <img :src="'/storage/' + img.file_path" class="w-full h-28 object-cover" />
            <span v-if="img.is_primary" class="absolute top-1 left-1 bg-blue-600 text-white text-[10px] px-1 rounded font-medium">Primary</span>
            <button
              type="button"
              @click="toggleDeleteImage(img.id)"
              :class="[
                'absolute top-1 right-1 text-white rounded-full p-0.5 transition-colors',
                markedForDeletion.has(img.id) ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600'
              ]"
            >
              <Trash2 v-if="!markedForDeletion.has(img.id)" class="w-3.5 h-3.5" />
              <Undo2 v-else class="w-3.5 h-3.5" />
            </button>
            <div class="p-1.5">
              <p class="text-xs text-slate-500 truncate">{{ img.label || 'No label' }}</p>
            </div>
          </div>
        </div>

        <!-- New images from gallery -->
        <div v-if="newImages.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-3">
          <div
            v-for="img in newImages"
            :key="img.id"
            class="relative group bg-slate-100 dark:bg-slate-700 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600"
          >
            <img :src="img.previewUrl" class="w-full h-28 object-cover" />
            <button
              type="button"
              @click="removeNewImage(img.id)"
              class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition-opacity"
            >
              <X class="w-3.5 h-3.5" />
            </button>
            <div class="p-1.5">
              <input
                :value="img.label"
                @input="updateNewLabel(img.id, ($event.target as HTMLInputElement).value)"
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

        <!-- Camera -->
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

        <!-- Add buttons -->
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
        <p v-if="form.errors.new_images" class="text-xs text-red-500 mt-1">{{ form.errors.new_images }}</p>
        <p v-if="form.errors.delete_images" class="text-xs text-red-500 mt-1">{{ form.errors.delete_images }}</p>
      </div>

      <div class="flex gap-2">
        <button type="submit" :disabled="form.processing" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50">
          {{ form.processing ? 'Updating...' : 'Update Product' }}
        </button>
        <button type="button" @click="emit('cancel')" class="flex-1 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg px-4 py-2 text-sm font-medium transition-colors">
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>
