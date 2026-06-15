<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    size: {
        type: String,
        default: undefined,
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);
const showSlot = ref(false);
let closeTimer = null;

watch(
    () => props.show,
    (val) => {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }

        if (val) {
            document.body.style.overflow = 'hidden';
            showSlot.value = true;
            return;
        }

        document.body.style.overflow = '';
        closeTimer = setTimeout(() => {
            showSlot.value = false;
            closeTimer = null;
        }, 200);
    },
    { immediate: true },
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) close();
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    if (closeTimer) clearTimeout(closeTimer);
    document.body.style.overflow = '';
});

const maxWidthClass = computed(() => {
    if (props.size) return props.size;
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show || showSlot" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-black/50 transition-opacity duration-200"
                :class="show ? 'opacity-100' : 'opacity-0'"
                @click="close"
            />

            <!-- Modal Content -->
            <div class="relative">
                <button @click="close" class="absolute -top-3 -right-3 z-50 w-8 h-8 rounded-full bg-red-400 text-white flex items-center justify-center shadow-lg hover:bg-red-600 transition-colors">
                    <X class="w-4 h-4" />
                </button>
                <div
                    class="z-10 w-full transform rounded-xl bg-white shadow-xl transition-all duration-200 dark:bg-slate-800 overflow-y-auto max-h-[85vh] sm:max-h-[95vh]"
                    :class="[maxWidthClass, show ? 'scale-100 opacity-100' : 'scale-95 opacity-0']"
                >
                <div v-if="$slots.header" class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <slot name="header" />
                </div>

                <div class="px-6 py-5">
                    <slot v-if="$slots.body" name="body" />
                    <slot v-else-if="showSlot" />
                </div>

                <div v-if="$slots.footer" class="border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                    <slot name="footer" />
                </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
