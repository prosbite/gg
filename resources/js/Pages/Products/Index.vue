<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import ProductForm from '@/Components/sidebar/eCommerce/ProductForm.vue'
import ProductEdit from '@/Components/sidebar/eCommerce/ProductEdit.vue'
import ProductList from '@/Components/sidebar/eCommerce/ProductList.vue'

defineOptions({ layout: MainLayout })

const props = defineProps<{
  products: {
    data: Array<{
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
      tags: Array<{ id: number; name: string }>
      images?: Array<{ id: number; file_path: string; thumbnail_path: string | null; label: string | null; is_primary: boolean; sort_order: number }>
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  categories: Array<{ id: number; name: string; tag_types: Array<{ id: number }> }>
  allTagTypes: Array<{ id: number; name: string; tags: Array<{ id: number; name: string }> }>
}>()

const editingProduct = ref<typeof props.products.data[0] | null>(null)

const onEdit = (product: typeof props.products.data[0]) => {
  editingProduct.value = product
}
</script>

<template>
  <Head title="Products" />

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
      <ProductForm v-if="!editingProduct" :categories="categories" :all-tag-types="allTagTypes" />
      <ProductEdit v-else :product="editingProduct" :categories="categories" :all-tag-types="allTagTypes" @cancel="editingProduct = null" />
    </div>
    <div class="lg:col-span-2">
      <ProductList :products="products" @edit="onEdit" />
    </div>
  </div>
</template>
