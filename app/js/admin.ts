import Pusher from 'pusher-js';

(function(){
  const cfg = (window as any).SnowDraft || {};
  // Basic admin bootstrap
  console.debug('SnowDraft admin loaded', cfg);
  if (cfg?.pusher?.key) {
    const pusher = new Pusher(cfg.pusher.key, { cluster: cfg.pusher.cluster || 'us2', authEndpoint: (cfg.restBase || '') + 'pusher/auth', auth: { headers: { 'X-WP-Nonce': cfg.nonce } } });
    // Example admin-only debug
    pusher.connection.bind('connected', () => {
      console.debug('Pusher connected (admin)');
    });
  }
})();
