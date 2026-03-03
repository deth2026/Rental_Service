import { createI18n } from 'vue-i18n';
import en from './en';
import kh from './kh';

// Get stored language or default to English
const getStoredLanguage = () => {
  try {
    const stored = localStorage.getItem('app_language');
    if (stored && (stored === 'en' || stored === 'kh')) {
      return stored;
    }
  } catch (e) {
    console.warn('Failed to get stored language:', e);
  }
  return 'en';
};

const i18n = createI18n({
  legacy: false,
  locale: getStoredLanguage(),
  fallbackLocale: 'en',
  messages: {
    en,
    kh,
  },
});

// Helper function to change language
export const setLanguage = (lang) => {
  if (lang === 'en' || lang === 'kh') {
    i18n.global.locale.value = lang;
    localStorage.setItem('app_language', lang);
    document.documentElement.lang = lang;
  }
};

// Helper function to get current language
export const getCurrentLanguage = () => {
  return i18n.global.locale.value;
};

export default i18n;
