<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'

const form = useForm({
  name: '',
})

const toast = useToast()

const submit = () => {
  form.post(route('tag-types.store'), {
    onSuccess: () => {
      form.reset()
      toast.success('Tag type created successfully.')
    },
  })
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
    <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Add Tag Type</h2>
    <form @submit.prevent="submit" class="space-y-3">
      <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
        <input v-model="form.name" type="text" required class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
      </div>

      <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50">
        {{ form.processing ? 'Saving...' : 'Save Tag Type' }}
      </button>
    </form>
  </div>
</template>
