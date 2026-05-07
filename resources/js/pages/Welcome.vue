<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Activity, MapPin, TriangleAlert } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import LanguageTabs from '@/components/LanguageTabs.vue';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: false,
    },
);

const { t } = useI18n();
</script>

<template>
    <Head :title="'EcoPulse'" />

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <!-- Header -->
        <header class="flex items-center justify-between border-b px-6 py-3">
            <div class="flex items-center gap-2">
                <div
                    class="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-md border bg-white dark:border-transparent"
                >
                    <AppLogoIcon class="size-5" />
                </div>
                <span class="font-semibold">EcoPulse</span>
            </div>

            <div class="flex items-center gap-3">
                <LanguageTabs />

                <template v-if="$page.props.auth.user">
                    <Button size="sm" as-child>
                        <Link :href="dashboard()">{{ t('pages.welcome.go_to_dashboard') }}</Link>
                    </Button>
                </template>
                <template v-else>
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="login()">{{ t('pages.welcome.login') }}</Link>
                    </Button>
                    <Button size="sm" as-child v-if="canRegister">
                        <Link :href="register()">{{ t('pages.welcome.register') }}</Link>
                    </Button>
                </template>
            </div>
        </header>

        <!-- Hero -->
        <main class="flex flex-1 flex-col items-center justify-center px-6 py-20 text-center">
            <div
                class="mb-6 flex size-20 items-center justify-center overflow-hidden rounded-2xl border bg-white shadow-md dark:border-transparent"
            >
                <AppLogoIcon class="size-12" />
            </div>

            <h1 class="mb-3 text-4xl font-bold tracking-tight lg:text-5xl">
                EcoPulse
            </h1>

            <p class="mb-2 max-w-2xl text-sm font-medium text-foreground/80">
                {{ t('pages.welcome.title') }}
            </p>

            <p class="mb-10 max-w-lg text-base text-muted-foreground">
                {{ t('pages.welcome.subtitle') }}
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3">
                <template v-if="$page.props.auth.user">
                    <Button size="lg" as-child>
                        <Link :href="dashboard()">{{ t('pages.welcome.go_to_dashboard') }}</Link>
                    </Button>
                </template>
                <template v-else>
                    <Button size="lg" as-child>
                        <Link :href="login()">{{ t('pages.welcome.login') }}</Link>
                    </Button>
                    <Button size="lg" variant="outline" as-child v-if="canRegister">
                        <Link :href="register()">{{ t('pages.welcome.register') }}</Link>
                    </Button>
                </template>
            </div>
        </main>

        <!-- Features -->
        <section class="border-t bg-muted/30 px-6 py-16">
            <div class="mx-auto grid max-w-4xl gap-5 sm:grid-cols-3">
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="mb-4 flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-950/40">
                        <Activity class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mb-1.5 font-semibold">
                        {{ t('pages.welcome.features.telemetry.title') }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ t('pages.welcome.features.telemetry.description') }}
                    </p>
                </div>

                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="mb-4 flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-950/40">
                        <MapPin class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mb-1.5 font-semibold">
                        {{ t('pages.welcome.features.zones.title') }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ t('pages.welcome.features.zones.description') }}
                    </p>
                </div>

                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="mb-4 flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-950/40">
                        <TriangleAlert class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mb-1.5 font-semibold">
                        {{ t('pages.welcome.features.incidents.title') }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ t('pages.welcome.features.incidents.description') }}
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>
