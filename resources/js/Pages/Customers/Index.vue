<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import CustomerForm from '@/Components/sidebar/CRM/CustomerForm.vue'
import CustomerEdit from '@/Components/sidebar/CRM/CustomerEdit.vue'
import CustomerList from '@/Components/sidebar/CRM/CustomerList.vue'

defineOptions({ layout: MainLayout })

const props = defineProps<{
  customers: {
    data: Array<{
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
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
}>()

const editingCustomer = ref<typeof props.customers.data[0] | null>(null)

const onEdit = (customer: typeof props.customers.data[0]) => {
  editingCustomer.value = customer
}
</script>

<template>
  <Head title="Customers" />

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
      <CustomerForm v-if="!editingCustomer" />
      <CustomerEdit v-else :customer="editingCustomer" @cancel="editingCustomer = null" />
    </div>
    <div class="lg:col-span-2">
      <CustomerList :customers="customers" @edit="onEdit" />
    </div>
  </div>
</template>
