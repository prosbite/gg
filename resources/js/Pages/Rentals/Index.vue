<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import RentalForm from '@/Components/sidebar/Rental/RentalForm.vue'
import RentalEdit from '@/Components/sidebar/Rental/RentalEdit.vue'
import RentalList from '@/Components/sidebar/Rental/RentalList.vue'

defineOptions({ layout: MainLayout })

const props = defineProps<{
  rentals: {
    data: Array<{
      id: number
      status: string
      pickup_date: string
      return_date: string
      actual_returned_at: string | null
      internal_notes: string | null
      created_at: string
      customer: {
        id: number
        first_name: string
        last_name: string
        contact_number: string | null
      }
      items: Array<{
        id: number
        status: string
        rental_fee: string
        security_deposit: string
        product: { id: number; name: string; item_code: string; is_draft: boolean; description: string | null; images?: Array<{ id: number; file_path: string; thumbnail_path: string | null; label: string | null; is_primary: boolean; sort_order: number }> }
      }>
      payments: Array<{
        id: number
        amount: string
        type: string
        method: string
        reference_number: string | null
        created_at: string
        receipts?: Array<{ id: number; file_path: string; notes: string | null }>
      }>
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  products: Array<{ id: number; item_code: string; name: string; custom_rental_fee: string | null; custom_security_deposit: string | null; status: string }>
  activeTab: string
}>()

const editingRental = ref<typeof props.rentals.data[0] | null>(null)

const onEdit = (rental: typeof props.rentals.data[0]) => {
  editingRental.value = rental
}
</script>

<template>
  <Head title="Rentals" />

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
      <RentalForm v-if="!editingRental" :products="products" />
      <RentalEdit v-else :rental="editingRental" :products="products" @cancel="editingRental = null" />
    </div>
    <div class="lg:col-span-2">
      <RentalList :rentals="rentals" :active-tab="activeTab" @edit="onEdit" />
    </div>
  </div>
</template>
