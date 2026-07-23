/**
 * Hotel website simulation auth bridge (optional customer session).
 * Redesign is controlled by HMS role in the builder — no in-template Staff login UI.
 */
(function () {
  'use strict';

  const csrf = () => {
    const m = document.querySelector('meta[name="csrf-token"]');
    if (m && m.content) return m.content;
    const x = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
    return x ? decodeURIComponent(x[1]) : (window.__HMS_CSRF__ || '');
  };

  const routes = window.__HMS_HOTEL_AUTH_ROUTES__ || {};

  async function api(url, method, body) {
    const res = await fetch(url, {
      method: method || 'GET',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf(),
      },
      body: body ? JSON.stringify(body) : undefined,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || data.message || 'Request failed');
    return data;
  }

  function applyAuth(auth) {
    window.__HMS_HOTEL_AUTH__ = auth || { authenticated: false };

    if (window.parent && window.parent !== window) {
      window.parent.postMessage({
        source: 'hms-template',
        type: 'hotel-auth-changed',
        auth: auth,
      }, '*');
    }

    window.dispatchEvent(new CustomEvent('hms-hotel-auth', { detail: { auth: auth } }));
  }

  const HotelAuth = {
    me: async function () {
      const data = await api(routes.me, 'GET');
      applyAuth(data);
      return data;
    },
    staffLogin: async function (email, password) {
      const data = await api(routes.staffLogin, 'POST', { email, password });
      applyAuth(data.auth);
      return data;
    },
    customerLogin: async function (email, password) {
      const data = await api(routes.customerLogin, 'POST', { email, password });
      applyAuth(data.auth);
      return data;
    },
    customerSignup: async function (name, email, password) {
      const data = await api(routes.customerSignup, 'POST', { name, email, password });
      applyAuth(data.auth);
      return data;
    },
    logout: async function () {
      const data = await api(routes.logout, 'POST', {});
      applyAuth(data.auth);
      return data;
    },
    applyAuth,
  };

  window.HMSHotelAuth = HotelAuth;

  function boot() {
    if (!routes.me) return;
    HotelAuth.me().catch(() => applyAuth({ authenticated: false }));
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { setTimeout(boot, 500); });
  } else {
    setTimeout(boot, 500);
  }
})();
