<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center bg-cover bg-center bg-no-repeat pt-6 sm:justify-center sm:pt-0"
        style="background-image: url('/images/login-bg.jpeg');">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-blue-800/40 z-0"></div>

        <div class="relative z-10 mt-6 w-full overflow-hidden bg-white px-6 py-8 shadow-md sm:max-w-md sm:rounded-lg">
            <div class="flex justify-center mb-6">
                <router-link to="">
                    <img src="\images\logo\logoHK.png" alt="" class="block h-20 w-auto fill-current">
                </router-link>
            </div>
            <slot />
        </div>
    </div>
</template>