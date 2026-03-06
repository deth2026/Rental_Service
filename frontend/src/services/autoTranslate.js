const GOOGLE_CONTAINER_ID = 'google_translate_element';
const GOOGLE_SCRIPT_ID = 'google-translate-script';
const GOOGLE_STYLE_ID = 'google-translate-style';
const RESET_FLAG_KEY = 'auto_translate_reset_en_once';

let googleScriptPromise = null;
let googleWidgetReadyPromise = null;

const languageMap = {
  en: 'en',
  kh: 'km',
};

const ensureHiddenContainer = () => {
  if (typeof document === 'undefined') return;

  let container = document.getElementById(GOOGLE_CONTAINER_ID);
  if (!container) {
    container = document.createElement('div');
    container.id = GOOGLE_CONTAINER_ID;
    container.style.position = 'fixed';
    container.style.left = '-9999px';
    container.style.top = '0';
    container.style.width = '1px';
    container.style.height = '1px';
    container.style.overflow = 'hidden';
    container.style.opacity = '0';
    container.setAttribute('aria-hidden', 'true');
    document.body.appendChild(container);
  }
};

const ensureGoogleStyles = () => {
  if (typeof document === 'undefined') return;
  if (document.getElementById(GOOGLE_STYLE_ID)) return;

  const style = document.createElement('style');
  style.id = GOOGLE_STYLE_ID;
  style.textContent = `
    .goog-te-banner-frame.skiptranslate { display: none !important; }
    body { top: 0 !important; }
    .goog-logo-link, .goog-te-gadget span { display: none !important; }
    .goog-te-gadget { font-size: 0 !important; }
  `;
  document.head.appendChild(style);
};

const setGoogTransCookie = (targetLang) => {
  if (typeof document === 'undefined' || typeof window === 'undefined') return;

  const value = `/en/${targetLang}`;
  const maxAge = 60 * 60 * 24 * 30;
  const hostname = window.location.hostname;

  document.cookie = `googtrans=${value};path=/;max-age=${maxAge}`;

  if (hostname && hostname.includes('.')) {
    document.cookie = `googtrans=${value};path=/;domain=.${hostname};max-age=${maxAge}`;
  }
};

const clearGoogTransCookie = () => {
  if (typeof document === 'undefined' || typeof window === 'undefined') return;

  const hostname = window.location.hostname;
  document.cookie = 'googtrans=;path=/;expires=Thu, 01 Jan 1970 00:00:00 GMT';
  if (hostname && hostname.includes('.')) {
    document.cookie = `googtrans=;path=/;domain=.${hostname};expires=Thu, 01 Jan 1970 00:00:00 GMT`;
  }
};

const loadGoogleTranslateScript = () => {
  if (typeof window === 'undefined' || typeof document === 'undefined') {
    return Promise.resolve();
  }

  if (window.google?.translate?.TranslateElement) {
    return Promise.resolve();
  }

  if (googleScriptPromise) return googleScriptPromise;

  googleScriptPromise = new Promise((resolve, reject) => {
    const existingScript = document.getElementById(GOOGLE_SCRIPT_ID);
    if (existingScript) {
      existingScript.addEventListener('load', () => resolve(), { once: true });
      existingScript.addEventListener('error', () => reject(new Error('Failed to load Google Translate script')), { once: true });
      return;
    }

    const callbackName = '__googleTranslateInitCallback';
    window[callbackName] = () => resolve();

    const script = document.createElement('script');
    script.id = GOOGLE_SCRIPT_ID;
    script.async = true;
    script.src = `https://translate.google.com/translate_a/element.js?cb=${callbackName}`;
    script.onerror = () => reject(new Error('Failed to load Google Translate script'));
    document.head.appendChild(script);
  });

  return googleScriptPromise;
};

const ensureGoogleWidget = async () => {
  if (typeof window === 'undefined' || typeof document === 'undefined') return;
  if (googleWidgetReadyPromise) return googleWidgetReadyPromise;

  googleWidgetReadyPromise = (async () => {
    ensureHiddenContainer();
    ensureGoogleStyles();
    await loadGoogleTranslateScript();

    if (!window.google?.translate?.TranslateElement) return;

    if (!window.__googleTranslateWidgetReady) {
      new window.google.translate.TranslateElement(
        {
          pageLanguage: 'en',
          includedLanguages: 'en,km',
          autoDisplay: false,
          layout: window.google.translate.TranslateElement.InlineLayout.SIMPLE,
        },
        GOOGLE_CONTAINER_ID
      );
      window.__googleTranslateWidgetReady = true;
    }
  })();

  return googleWidgetReadyPromise;
};

const triggerLanguageSelection = (targetLangCode) => {
  const select = document.querySelector('.goog-te-combo');
  if (!select) return false;

  select.value = targetLangCode;
  select.dispatchEvent(new Event('change'));
  return true;
};

export const applyAutoTranslate = async (lang) => {
  if (typeof window === 'undefined' || typeof document === 'undefined') return;

  const targetLangCode = languageMap[lang] || 'en';

  if (targetLangCode === 'en') {
    clearGoogTransCookie();
    setGoogTransCookie('en');

    const translatedClass = document.documentElement.classList.contains('translated-ltr')
      || document.documentElement.classList.contains('translated-rtl');
    const hasGoogleFrame = !!document.querySelector('iframe.goog-te-banner-frame');
    const didResetAlready = sessionStorage.getItem(RESET_FLAG_KEY) === '1';

    if ((translatedClass || hasGoogleFrame) && !didResetAlready) {
      sessionStorage.setItem(RESET_FLAG_KEY, '1');
      window.location.reload();
      return;
    }

    sessionStorage.removeItem(RESET_FLAG_KEY);
  } else {
    sessionStorage.removeItem(RESET_FLAG_KEY);
  }

  setGoogTransCookie(targetLangCode);

  try {
    await ensureGoogleWidget();

    if (triggerLanguageSelection(targetLangCode)) return;

    // Widget select may appear asynchronously after script init.
    let attempts = 0;
    const maxAttempts = 20;
    const timer = window.setInterval(() => {
      attempts += 1;
      if (triggerLanguageSelection(targetLangCode) || attempts >= maxAttempts) {
        window.clearInterval(timer);
      }
    }, 150);
  } catch (error) {
    console.warn('Auto translation unavailable:', error);
  }
};
