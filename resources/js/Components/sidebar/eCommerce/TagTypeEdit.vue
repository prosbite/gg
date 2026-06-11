<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'

const props = defineProps<{
  tagType: {
    id: number
    name: string
    tags_count: number
    created_at: string
  }
}>()

const emit = defineEmits<{
  (e: 'cancel'): void
}>()

const form = useForm({
  name: props.tagType.name,
})

const toast = useToast()

const submit = () => {
  form.put(route('tag-types.update', props.tagType.id), {
    onSuccess: () => {
      toast.success('Tag type updated successfully.')
      emit('cancel')
    },
  })
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
    <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Edit Tag Type</h2>
    <form @submit.prevent="submit" class="space-y-3">
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
        <input v-model="form.name" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
      </div>

      <div class="flex gap-2">
        <button type="submit" :disabled="form.processing" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50">
          {{ form.processing ? 'Updating...' : 'Update Tag Type' }}
        </button>
        <button type="button" @click="emit('cancel')" class="flex-1 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg px-4 py-2 text-sm font-medium transition-colors">
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>
