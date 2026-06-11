<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { X, ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps<{
  images: string[]
  startIndex?: number
}>()

const emit = defineEmits<{
  close: []
}>()

const currentIndex = ref(props.startIndex ?? 0)

watch(() => props.startIndex, (val) => {
  currentIndex.value = val ?? 0
})

watch(() => props.images, (val) => {
  if (val.length === 0) currentIndex.value = 0
})

const show = computed(() => props.images.length > 0)
const currentSrc = computed(() => props.images[currentIndex.value] ?? null)
const hasPrev = computed(() => currentIndex.value > 0)
const hasNext = computed(() => currentIndex.value < props.images.length - 1)

const goPrev = () => {
  if (hasPrev.value) currentIndex.value--
}

const goNext = () => {
  if (hasNext.value) currentIndex.value++
}

const close = () => {
  if (show.value) emit('close')
}

const onKeydown = (e: KeyboardEvent) => {
  if (!show.value) return
  if (e.key === 'Escape') close()
  if (e.key === 'ArrowLeft') goPrev()
  if (e.key === 'ArrowRight') goNext()
}

watch(show, (val) => {
  document.body.style.overflow = val ? 'hidden' : ''
})

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => {
  document.removeEventListener('keydown', onKeydown)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="close">
      <div class="absolute inset-0 bg-black/70" />

      <div class="relative z-10 flex items-center gap-3 max-w-4xl w-full">
        <button
          v-if="hasPrev"
          type="button"
          @click="goPrev"
          class="shrink-0 bg-slate-800/80 hover:bg-slate-700 text-white rounded-full p-2 transition-colors"
        >
          <ChevronLeft class="w-6 h-6" />
        </button>
        <div v-else class="w-10 shrink-0" />

        <div class="relative min-w-0 flex-1">
          <button
            type="button"
            @click="close"
            class="absolute -top-3 -right-3 z-20 bg-slate-800 hover:bg-slate-700 text-white rounded-full p-1 shadow-lg transition-colors"
          >
            <X class="w-5 h-5" />
          </button>
          <img
            v-if="currentSrc"
            :key="currentIndex"
            :src="currentSrc"
            class="w-full max-h-[85vh] rounded-lg shadow-2xl object-contain"
          />
          <div class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-slate-900/70 text-white text-xs px-2.5 py-1 rounded-full">
            {{ currentIndex + 1 }} / {{ images.length }}
          </div>
        </div>

        <button
          v-if="hasNext"
          type="button"
          @click="goNext"
          class="shrink-0 bg-slate-800/80 hover:bg-slate-700 text-white rounded-full p-2 transition-colors"
        >
          <ChevronRight class="w-6 h-6" />
        </button>
        <div v-else class="w-10 shrink-0" />
      </div>
    </div>
  </Teleport>
</template>
