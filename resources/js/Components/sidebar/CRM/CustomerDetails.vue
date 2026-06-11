<script setup lang="ts">
import { ExternalLink } from 'lucide-vue-next'

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
    created_at: string
  }
}>()

const avatarColors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-pink-500', 'bg-yellow-500', 'bg-red-500', 'bg-indigo-500', 'bg-teal-500']
const getColor = (id: number) => avatarColors[id % avatarColors.length]
</script>

<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center gap-3">
      <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold', getColor(customer.id)]">
        {{ customer.first_name.charAt(0) }}{{ customer.last_name.charAt(0) }}
      </div>
      <div>
        <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200">
          {{ customer.first_name }} {{ customer.last_name }}
        </h3>
        <p class="text-xs text-slate-500">Customer #{{ customer.id }}</p>
      </div>
    </div>

    <hr class="border-slate-200 dark:border-slate-700">

    <!-- Contact Info with Image -->
    <div class="flex gap-4">
      <!-- Identification Image -->
      <div class="w-32 shrink-0">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">ID</h4>
        <div v-if="customer.identification" class="relative group">
          <img :src="'/storage/' + customer.identification" alt="Identification" class="w-full h-24 object-cover rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900" />
          <a :href="customer.identification" target="_blank" rel="noopener noreferrer" class="absolute top-1 right-1 p-1 bg-white dark:bg-slate-800 rounded-full shadow opacity-0 group-hover:opacity-100 transition-opacity">
            <ExternalLink class="w-3 h-3 text-slate-600" />
          </a>
        </div>
        <span v-else class="text-sm text-slate-700 dark:text-slate-300">—</span>
      </div>

      <!-- Contact Info -->
      <div class="flex-1">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Contact Information</h4>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <span class="block text-xs text-slate-400">Phone</span>
            <span class="text-slate-700 dark:text-slate-300">{{ customer.contact_number ?? '—' }}</span>
          </div>
          <div>
            <span class="block text-xs text-slate-400">Email</span>
            <span class="text-slate-700 dark:text-slate-300">{{ customer.email ?? '—' }}</span>
          </div>
          <div class="col-span-2">
            <span class="block text-xs text-slate-400">Address</span>
            <span class="text-slate-700 dark:text-slate-300">{{ customer.address ?? '—' }}</span>
          </div>
        </div>
      </div>
    </div>

    <hr class="border-slate-200 dark:border-slate-700">

    <!-- Additional Details -->
    <div>
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Additional Details</h4>
      <div class="grid grid-cols-2 gap-3 text-sm">
        <div>
          <span class="block text-xs text-slate-400">Affiliation</span>
          <span class="text-slate-700 dark:text-slate-300">{{ customer.affiliation ?? '—' }}</span>
        </div>
        <div>
          <span class="block text-xs text-slate-400">Social Media</span>
          <span class="text-slate-700 dark:text-slate-300">{{ customer.social_media_link ?? '—' }}</span>
        </div>
        <div>
          <span class="block text-xs text-slate-400">Status</span>
          <span :class="customer.is_blacklisted ? 'text-red-600' : 'text-green-600'" class="font-medium">
            {{ customer.is_blacklisted ? 'Blacklisted' : 'Active' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Detail Info JSON -->
    <div v-if="customer.detail_info && Object.keys(customer.detail_info).length > 0">
      <hr class="border-slate-200 dark:border-slate-700 mb-3">
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Extra Information</h4>
      <div class="grid grid-cols-2 gap-3 text-sm">
        <div v-for="(value, key) in customer.detail_info" :key="key">
          <span class="block text-xs text-slate-400 capitalize">{{ key.replace(/_/g, ' ') }}</span>
          <span class="text-slate-700 dark:text-slate-300">{{ value ?? '—' }}</span>
        </div>
      </div>
    </div>

    <!-- Admin Notes -->
    <div v-if="customer.admin_notes">
      <hr class="border-slate-200 dark:border-slate-700 mb-3">
      <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Admin Notes</h4>
      <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ customer.admin_notes }}</p>
    </div>

    <!-- Dates -->
    <hr class="border-slate-200 dark:border-slate-700">
    <div class="flex justify-between text-xs text-slate-400">
      <span>Created: {{ customer.created_at ? new Date(customer.created_at).toLocaleDateString() : '—' }}</span>
    </div>
  </div>
</template>
