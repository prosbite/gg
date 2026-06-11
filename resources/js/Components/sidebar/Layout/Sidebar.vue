<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { X, LayoutDashboard, Users, Tag, FolderTree, Package, CalendarClock } from 'lucide-vue-next'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

defineProps<{
  isCollapsed: boolean
  isMobileOpen: boolean
}>()

const emit = defineEmits<{
  closeMobile: []
  toggleCollapse: []
}>()

const navigation = [
  { label: 'Dashboard', icon: LayoutDashboard, route: '/dashboard' },
  { label: 'Customers', icon: Users, route: '/customers' },
  { label: 'Rentals', icon: CalendarClock, route: '/rentals' },
  { label: 'Products', icon: Package, route: '/products' },
  { label: 'Categories', icon: FolderTree, route: '/categories' },
  { label: 'Tags', icon: Tag, route: '/tags' },
]

const isActive = (route: string) => {
  const url = page.url

  if (route === '/dashboard') {
    return url === '/dashboard' || url === '/'
  }

  return url.startsWith(route)
}
</script>

<template>
  <aside
    :class="[
      isMobileOpen ? 'w-64 translate-x-0' : '-translate-x-full',
      isCollapsed ? 'lg:w-16' : 'lg:w-64',
      'fixed lg:relative z-40 h-full bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 overflow-y-auto transition-all duration-300 ease-in-out lg:translate-x-0'
    ]"
  >
    <!-- Logo area -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-slate-200 dark:border-slate-700">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">G</div>
        <span v-if="!isCollapsed" class="font-semibold text-lg whitespace-nowrap">GretAdmin</span>
      </div>
      <button
        v-if="isMobileOpen || !isCollapsed"
        class="lg:hidden p-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700"
        @click="emit('closeMobile')"
      >
        <X class="w-5 h-5" />
      </button>
    </div>

    <!-- Navigation -->
    <nav class="p-2 space-y-1">
      <Link
        v-for="item in navigation"
        :key="item.label"
        :href="item.route"
        :class="[
          'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group',
          isActive(item.route)
            ? 'bg-blue-600 text-white shadow-sm'
            : 'text-slate-600 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400'
        ]"
      >
        <component :is="item.icon" class="w-5 h-5 shrink-0" />
        <span v-if="!isCollapsed" class="whitespace-nowrap">{{ item.label }}</span>
      </Link>
    </nav>
  </aside>
</template>

<style scoped>
aside::-webkit-scrollbar { width: 4px; }
aside::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 2px; }
aside::-webkit-scrollbar-track { background: transparent; }
</style>
