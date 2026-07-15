<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import lottie from 'lottie-web';
import type { AnimationItem } from 'lottie-web';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = (page.props.name as string) ?? 'Digital Bookkeeping';

const lottieContainer = ref<HTMLElement | null>(null);
let anim: AnimationItem | null = null;

onMounted(() => {
    if (!lottieContainer.value) {
        return;
    }

    anim = lottie.loadAnimation({
        container: lottieContainer.value,
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: '/animations/login-hero.json',
    });
});

onBeforeUnmount(() => anim?.destroy());

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <!-- Brand panel -->
        <div
            class="relative hidden h-full flex-col justify-between overflow-hidden p-10 text-white lg:flex"
        >
            <div
                class="absolute inset-0 bg-gradient-to-br from-teal-950 via-emerald-900 to-teal-700"
            />
            <div
                class="absolute inset-0 [background-image:radial-gradient(circle_at_1px_1px,white_1px,transparent_0)] [background-size:24px_24px] opacity-20"
            />
            <div
                class="absolute -top-24 -right-24 size-96 rounded-full bg-emerald-400/20 blur-3xl"
            />

            <Link
                :href="home()"
                class="relative z-20 flex items-center gap-2 text-lg font-semibold"
            >
                <AppLogoIcon class="size-8 fill-current text-white" />
                {{ name }}
            </Link>

            <div
                ref="lottieContainer"
                class="relative z-20 mx-auto w-full max-w-2xl scale-125"
                aria-hidden="true"
            />

            <div class="relative z-20 max-w-md space-y-3">
                <p class="text-2xl leading-snug font-medium">
                    Kontrol keuangan, inventory, dan akuntansi multi-outlet
                    dalam satu tempat.
                </p>
                <p class="text-sm text-emerald-100/70">
                    Backoffice digital untuk Laba Rugi, Neraca, dan arus kas
                    yang akurat.
                </p>
            </div>
        </div>

        <!-- Form panel -->
        <div
            class="relative flex min-h-dvh items-center justify-center overflow-hidden p-6 sm:p-10"
        >
            <!-- Ambient background -->
            <div
                class="absolute inset-0 bg-gradient-to-b from-background via-background to-muted/40"
            />
            <div
                class="absolute -top-32 right-0 size-80 rounded-full bg-primary/10 blur-3xl"
            />
            <div
                class="absolute -bottom-32 left-0 size-80 rounded-full bg-emerald-400/10 blur-3xl"
            />

            <div class="relative z-10 w-full max-w-[400px]">
                <div
                    class="rounded-2xl border border-border/60 bg-card/80 p-8 shadow-sm shadow-black/5 backdrop-blur-sm sm:p-10"
                >
                    <div class="flex flex-col items-center gap-4">
                        <div class="flex flex-col space-y-1.5 text-center">
                            <h1
                                class="text-2xl font-semibold tracking-tight"
                                v-if="title"
                            >
                                {{ title }}
                            </h1>
                            <p
                                class="text-sm text-muted-foreground"
                                v-if="description"
                            >
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <slot />
                    </div>
                </div>

                <p class="mt-6 text-center text-xs text-muted-foreground">
                    &copy; {{ name }}. Backoffice keuangan multi-outlet.
                </p>
            </div>
        </div>
    </div>
</template>
