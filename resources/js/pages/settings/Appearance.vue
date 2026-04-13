<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import LanguageTabs from '@/components/LanguageTabs.vue';
import { edit } from '@/routes/appearance';

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'settings.appearance.head',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="t('settings.appearance.head')" />

    <h1 class="sr-only">{{ t('settings.appearance.theme.title') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="t('settings.appearance.theme.title')"
            :description="t('settings.appearance.theme.description')"
        />
        <AppearanceTabs />
    </div>

    <h1 class="sr-only">{{ t('settings.appearance.language.title') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="t('settings.appearance.language.title')"
            :description="t('settings.appearance.language.description')"
        />
        <LanguageTabs />
    </div>
</template>
