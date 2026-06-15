<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
  canResetPassword: { type: Boolean },
  status: { type: String },
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Log in" />

  <div class="relative min-h-screen flex items-center justify-center px-4 py-8 overflow-hidden bg-gradient-to-br from-stone-900 via-slate-800 to-amber-900">
    <div class="absolute inset-0 bg-[url('/images/glitsglamlogin.png')] bg-cover bg-center opacity-40" />
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/40" />

    <div class="relative z-10 w-full max-w-md">
      <div class="text-center mb-8">
        <img src="/images/glitsglamlogo.png" alt="Glitz & Glam" class="h-24 sm:h-28 w-auto mx-auto drop-shadow-xl" />
      </div>

      <div class="bg-white/10 backdrop-blur-xl rounded-3xl border border-white/20 shadow-2xl px-6 sm:px-10 py-10">
        <div v-if="status" class="mb-6 text-sm font-medium text-amber-300 bg-amber-900/30 rounded-lg px-4 py-3 text-center">
          {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label for="email" class="block text-sm font-medium text-amber-100/90 mb-1.5 tracking-wide">Email</label>
            <input
              id="email"
              type="email"
              v-model="form.email"
              required
              autofocus
              autocomplete="username"
              class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm text-white placeholder-white/40 shadow-inner backdrop-blur-sm transition-all duration-200 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/40"
              placeholder="you@example.com"
            />
            <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-300">{{ form.errors.email }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-amber-100/90 mb-1.5 tracking-wide">Password</label>
            <input
              id="password"
              type="password"
              v-model="form.password"
              required
              autocomplete="current-password"
              class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm text-white placeholder-white/40 shadow-inner backdrop-blur-sm transition-all duration-200 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/40"
              placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
            />
            <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-300">{{ form.errors.password }}</p>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer group">
              <input
                type="checkbox"
                v-model="form.remember"
                class="h-4 w-4 rounded border-white/30 bg-white/10 text-amber-400 shadow-sm focus:ring-2 focus:ring-amber-400/40 focus:ring-offset-0"
              />
              <span class="text-sm text-amber-100/80 group-hover:text-white transition-colors">Remember me</span>
            </label>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 px-4 py-2.5 text-sm font-semibold text-stone-900 shadow-lg shadow-amber-500/30 transition-all duration-200 hover:from-amber-300 hover:to-amber-400 hover:shadow-amber-400/40 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-transparent disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing" class="inline-flex items-center gap-2">
              <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              Signing in...
            </span>
            <span v-else>Sign In</span>
          </button>

          <div class="text-center pt-2">
            <Link
              v-if="canResetPassword"
              :href="route('password.request')"
              class="text-sm text-amber-200/70 hover:text-amber-200 transition-colors underline underline-offset-2 decoration-amber-400/30"
            >
              Forgot your password?
            </Link>
          </div>
        </form>
      </div>

      <p class="text-center mt-6 text-xs text-white/40">&copy; {{ new Date().getFullYear() }} Glitz &amp; Glam. All rights reserved.</p>
    </div>
  </div>
</template>
