<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { SUPPORTED_LANGUAGES } from '@/plugins/i18n';

const { locale } = useI18n();

const LANGUAGE_STORAGE_KEY = 'app_language';

function setLanguage(lang: string) {
    locale.value = lang;
    localStorage.setItem(LANGUAGE_STORAGE_KEY, lang);
    document.cookie = `language=${lang};path=/;max-age=${365 * 24 * 60 * 60};SameSite=Lax`;
}

const { t } = useI18n();

</script>

<template>
    <div
        class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800"
    >
        <button
            v-for="{ code, name, flag } in SUPPORTED_LANGUAGES"
            :key="code"
            @click="setLanguage(code)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                locale === code
                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <span class="text-base">{{ flag }}</span>
            <span class="ml-1.5 text-sm">{{ name }}</span>
        </button>
    </div>
</template>
