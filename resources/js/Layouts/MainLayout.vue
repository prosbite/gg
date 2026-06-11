<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, useForm } from '@inertiajs/vue3'
import {
  Menu, Search, Moon, Sun, Bell, Mail, User, LogOut
} from 'lucide-vue-next'
import Sidebar from '@/Components/sidebar/Layout/Sidebar.vue'
import { Link } from '@inertiajs/vue3'

const isCollapsed = ref(false)
const isMobileOpen = ref(false)
const isProfileOpen = ref(false)
const profileDropdownRef = ref<HTMLElement | null>(null)

const user = computed(() => usePage().props.auth?.user)

const isDarkMode = ref(false)
const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  document.documentElement.classList.toggle('dark')
}

const form = useForm({})

const logout = () => {
  form.post(route('logout'))
}

const toggleSidebar = () => {
  if (window.innerWidth < 1024) {
    isMobileOpen.value = !isMobileOpen.value
    return
  }

  isCollapsed.value = !isCollapsed.value
}

const closeMobileSidebar = () => {
  isMobileOpen.value = false
}

const handleClickOutside = (event: MouseEvent) => {
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(event.target as Node)) {
    isProfileOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>

<template>
  <div class="flex h-screen bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200">
    <!-- Mobile overlay -->
    <div
      v-if="isMobileOpen"
      class="fixed inset-0 z-30 bg-black/50 lg:hidden"
      @click="closeMobileSidebar"
    />

    <Sidebar
      :is-collapsed="isCollapsed"
      :is-mobile-open="isMobileOpen"
      @close-mobile="closeMobileSidebar"
    />

    <!-- Main content area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Header -->
      <header class="sticky top-0 z-20 h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center justify-between h-full px-4 lg:px-6">
          <!-- Left side -->
          <div class="flex items-center gap-4">
            <button
              @click="toggleSidebar"
              class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
              aria-label="Toggle sidebar"
            >
              <Menu class="w-5 h-5" />
            </button>
            <div class="hidden sm:flex items-center bg-slate-100 dark:bg-slate-700 rounded-xl px-3 py-1.5 gap-2 min-w-[200px]">
              <Search class="w-4 h-4 text-slate-400" />
              <input type="text" placeholder="Search..." class="bg-transparent border-none outline-none text-sm w-full placeholder-slate-400 dark:placeholder-slate-500" />
            </div>
          </div>
          <div class="flex items-center gap-1 sm:gap-2">
            <button @click="toggleDarkMode" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" aria-label="Toggle theme">
              <Sun v-if="!isDarkMode" class="w-5 h-5 text-amber-500" />
              <Moon v-else class="w-5 h-5 text-blue-400" />
            </button>
            <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
              <span class="text-base">🇺🇸</span>
            </button>
            <button class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
              <Mail class="w-5 h-5" />
              <span class="absolute top-1 right-1 w-2 h-2 bg-blue-500 rounded-full" />
            </button>
            <button class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
              <Bell class="w-5 h-5" />
              <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">3</span>
            </button>
            <!-- Profile dropdown -->
            <div ref="profileDropdownRef" class="relative">
              <button
                @click.stop="isProfileOpen = !isProfileOpen"
                class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors pr-2"
              >
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                  {{ user?.name?.charAt(0) ?? 'U' }}
                </div>
                <span v-if="user" class="hidden sm:block text-sm font-medium">{{ user.name }}</span>
                <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </button>
              <!-- Dropdown menu -->
              <div
                v-if="isProfileOpen"
                class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50"
              >
                <Link
                  href="/profile"
                  class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                >
                  <User class="w-4 h-4" />
                  Profile
                </Link>
                <button
                  @click="logout"
                  class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors w-full text-left"
                >
                  <LogOut class="w-4 h-4" />
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>
      <main class="flex-1 overflow-auto p-4 lg:p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
