<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { X } from 'lucide-vue-next'

const props = defineProps<{
  category: {
    id: number
    name: string
    prefix: string
    default_rental_fee: string
    default_security_deposit: string
    tag_types: Array<{ id: number; name: string }>
  }
  tagTypes: Array<{ id: number; name: string }>
}>()

const emit = defineEmits<{
  (e: 'cancel'): void
}>()

const form = useForm({
  name: props.category.name,
  prefix: props.category.prefix,
  default_rental_fee: props.category.default_rental_fee,
  default_security_deposit: props.category.default_security_deposit,
  tag_types: props.category.tag_types.map(t => t.id),
})

const toast = useToast()

const selectedTagTypes = ref<Array<{ id: number; name: string }>>([...props.category.tag_types])

const availableTagTypes = computed(() =>
  props.tagTypes.filter(tt => !selectedTagTypes.value.some(s => s.id === tt.id))
)

const selectedTypeId = ref('')

const addTagType = () => {
  const id = Number(selectedTypeId.value)
  if (!id) return
  const tt = props.tagTypes.find(t => t.id === id)
  if (tt) {
    selectedTagTypes.value.push(tt)
    form.tag_types = selectedTagTypes.value.map(t => t.id)
    selectedTypeId.value = ''
  }
}

const removeTagType = (id: number) => {
  selectedTagTypes.value = selectedTagTypes.value.filter(t => t.id !== id)
  form.tag_types = selectedTagTypes.value.map(t => t.id)
}

const submit = () => {
  form.put(route('categories.update', props.category.id), {
    onSuccess: () => {
      toast.success('Category updated successfully.')
      emit('cancel')
    },
  })
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
    <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Edit Category</h2>
    <form @submit.prevent="submit" class="space-y-3">
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
        <input v-model="form.name" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Prefix</label>
        <input v-model="form.prefix" type="text" required maxlength="10" placeholder="e.g. GW" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none uppercase" />
        <p v-if="form.errors.prefix" class="text-xs text-red-500 mt-1">{{ form.errors.prefix }}</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Default Rental Fee</label>
          <input v-model="form.default_rental_fee" type="number" step="0.01" min="0" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          <p v-if="form.errors.default_rental_fee" class="text-xs text-red-500 mt-1">{{ form.errors.default_rental_fee }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Default Security Deposit</label>
          <input v-model="form.default_security_deposit" type="number" step="0.01" min="0" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          <p v-if="form.errors.default_security_deposit" class="text-xs text-red-500 mt-1">{{ form.errors.default_security_deposit }}</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tag Types</label>
        <div class="flex gap-2">
          <select v-model="selectedTypeId" class="flex-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="" disabled>Select tag type...</option>
            <option v-for="tt in availableTagTypes" :key="tt.id" :value="tt.id">{{ tt.name }}</option>
          </select>
          <button type="button" @click="addTagType" :disabled="!selectedTypeId" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg px-3 py-2 text-sm font-medium transition-colors">
            Add
          </button>
        </div>
        <div v-if="selectedTagTypes.length > 0" class="flex flex-wrap gap-1.5 mt-2">
          <span v-for="tt in selectedTagTypes" :key="tt.id" class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-600 dark:bg-blue-900/30">
            {{ tt.name }}
            <button type="button" @click="removeTagType(tt.id)" class="hover:text-red-500 transition-colors">
              <X class="w-3 h-3" />
            </button>
          </span>
        </div>
        <p v-if="form.errors.tag_types" class="text-xs text-red-500 mt-1">{{ form.errors.tag_types }}</p>
      </div>

      <div class="flex gap-2">
        <button type="submit" :disabled="form.processing" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50">
          {{ form.processing ? 'Updating...' : 'Update Category' }}
        </button>
        <button type="button" @click="emit('cancel')" class="flex-1 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg px-4 py-2 text-sm font-medium transition-colors">
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>
