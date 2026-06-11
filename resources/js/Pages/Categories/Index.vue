<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import CategoryForm from '@/Components/sidebar/eCommerce/CategoryForm.vue'
import CategoryEdit from '@/Components/sidebar/eCommerce/CategoryEdit.vue'
import CategoryList from '@/Components/sidebar/eCommerce/CategoryList.vue'

defineOptions({ layout: MainLayout })

const props = defineProps<{
  categories: {
    data: Array<{
      id: number
      name: string
      prefix: string
      default_rental_fee: string
      default_security_deposit: string
      tag_types: Array<{ id: number; name: string }>
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  tagTypes: Array<{ id: number; name: string }>
}>()

const editingCategory = ref<typeof props.categories.data[0] | null>(null)

const onEdit = (category: typeof props.categories.data[0]) => {
  editingCategory.value = category
}
</script>

<template>
  <Head title="Categories" />

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
      <CategoryForm v-if="!editingCategory" :tag-types="tagTypes" />
      <CategoryEdit v-else :category="editingCategory" :tag-types="tagTypes" @cancel="editingCategory = null" />
    </div>
    <div class="lg:col-span-2">
      <CategoryList :categories="categories" @edit="onEdit" />
    </div>
  </div>
</template>
