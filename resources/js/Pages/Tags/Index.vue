<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import TagForm from '@/Components/sidebar/eCommerce/TagForm.vue'
import TagEdit from '@/Components/sidebar/eCommerce/TagEdit.vue'
import TagList from '@/Components/sidebar/eCommerce/TagList.vue'
import TagTypeForm from '@/Components/sidebar/eCommerce/TagTypeForm.vue'
import TagTypeEdit from '@/Components/sidebar/eCommerce/TagTypeEdit.vue'
import TagTypeList from '@/Components/sidebar/eCommerce/TagTypeList.vue'

defineOptions({ layout: MainLayout })

const props = defineProps<{
  tags: {
    data: Array<{
      id: number
      name: string
      slug: string
      type: number
      tag_type: { id: number; name: string } | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  tagTypes: Array<{ id: number; name: string }>
  tagTypeList: {
    data: Array<{
      id: number
      name: string
      tags_count: number
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
}>()

const activeTab = ref<'tags' | 'tag-types'>('tags')

const editingTag = ref<typeof props.tags.data[0] | null>(null)
const editingTagType = ref<typeof props.tagTypeList.data[0] | null>(null)

const onEditTag = (tag: typeof props.tags.data[0]) => {
  editingTag.value = tag
}

const onEditTagType = (tagType: typeof props.tagTypeList.data[0]) => {
  editingTagType.value = tagType
}
</script>

<template>
  <Head title="Tags" />

  <div class="space-y-6">
    <div class="flex gap-1 border-b border-slate-200 dark:border-slate-700">
      <button @click="activeTab = 'tags'" :class="['px-4 py-2 text-sm font-medium transition-colors rounded-t-lg border-b-2 -mb-px', activeTab === 'tags' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300']">
        Tags
      </button>
      <button @click="activeTab = 'tag-types'" :class="['px-4 py-2 text-sm font-medium transition-colors rounded-t-lg border-b-2 -mb-px', activeTab === 'tag-types' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300']">
        Tag Types
      </button>
    </div>

    <div v-if="activeTab === 'tags'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-1">
        <TagForm v-if="!editingTag" :tag-types="tagTypes" />
        <TagEdit v-else :tag="editingTag" :tag-types="tagTypes" @cancel="editingTag = null" />
      </div>
      <div class="lg:col-span-2">
        <TagList :tags="tags" @edit="onEditTag" />
      </div>
    </div>

    <div v-if="activeTab === 'tag-types'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-1">
        <TagTypeForm v-if="!editingTagType" />
        <TagTypeEdit v-else :tag-type="editingTagType" @cancel="editingTagType = null" />
      </div>
      <div class="lg:col-span-2">
        <TagTypeList :tag-type-list="tagTypeList" @edit="onEditTagType" />
      </div>
    </div>
  </div>
</template>
