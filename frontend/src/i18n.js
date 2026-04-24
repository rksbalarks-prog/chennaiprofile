import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import enTranslation from '../locales/en/translation.json';
import taTranslation from '../locales/ta/translation.json';

const resources = {
  en: {
    translation: enTranslation,
  },
  ta: {
    translation: taTranslation,
  },
};

const savedLanguage = localStorage.getItem('language') || 'en';

i18n
  .use(initReactI18next)
  .init({
    resources,
    lng: savedLanguage,
    fallbackLng: 'en',
    interpolation: {
      escapeValue: false,
    },
  });

// Set initial language attribute on HTML element
document.documentElement.lang = savedLanguage;
document.documentElement.setAttribute('lang', savedLanguage);

// Listen for language changes
i18n.on('languageChanged', (lng) => {
  document.documentElement.lang = lng;
  document.documentElement.setAttribute('lang', lng);
  localStorage.setItem('language', lng);
});

export default i18n;
