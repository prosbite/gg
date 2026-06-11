<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'

const props = defineProps<{
  tagTypes: Array<{ id: number; name: string }>
}>()

const form = useForm({
  name: '',
  type: '',
})

const toast = useToast()

const submit = () => {
  form.post(route('tags.store'), {
    onSuccess: () => {
      form.reset()
      toast.success('Tag created successfully.')
    },
  })
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
    <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Add Tag</h2>
    <form @submit.prevent="submit" class="space-y-3">
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
        <input v-model="form.name" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tag Type</label>
        <select v-model="form.type" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
          <option value="" disabled>Select type...</option>
          <option v-for="tt in tagTypes" :key="tt.id" :value="tt.id">{{ tt.name }}</option>
        </select>
        <p v-if="form.errors.type" class="text-xs text-red-500 mt-1">{{ form.errors.type }}</p>
      </div>

      <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50">
        {{ form.processing ? 'Saving...' : 'Save Tag' }}
      </button>
    </form>
  </div>
</template>
