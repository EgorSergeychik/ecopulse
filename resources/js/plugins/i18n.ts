import { createI18n } from 'vue-i18n';
import en from '@/locales/en.json';
import uk from '@/locales/uk.json';
import type { Language } from '@/types';

export type LanguageConfig = {
    code: Language;
    name: string;
    flag: string;
};

export const SUPPORTED_LANGUAGES: LanguageConfig[] = [
    { code: 'en', name: 'English', flag: '🇬🇧' },
    { code: 'uk', name: 'Українська', flag: '🇺🇦' },
];

const LANGUAGE_STORAGE_KEY = 'app_language';
const DEFAULT_LOCALE: Language = 'en';

function getInitialLocale(): Language {
    if (typeof window !== 'undefined') {
        const stored = localStorage.getItem(LANGUAGE_STORAGE_KEY) as Language | null;
        if (stored && SUPPORTED_LANGUAGES.some((l) => l.code === stored)) {
            return stored;
        }
    }
    return DEFAULT_LOCALE;
}

const i18n = createI18n({
    legacy: false,
    locale: getInitialLocale(),
    fallbackLocale: DEFAULT_LOCALE,
    messages: { en, uk },
});

export default i18n;
