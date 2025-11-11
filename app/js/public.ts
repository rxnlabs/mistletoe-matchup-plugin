import Pusher from 'pusher-js';

(function(){
  const cfg = (window as any).SnowDraft || {};
  if (cfg?.pusher?.key) {
    const pusher = new Pusher(cfg.pusher.key, { cluster: cfg.pusher.cluster || 'us2', authEndpoint: (cfg.restBase || '') + 'pusher/auth', auth: { headers: { 'X-WP-Nonce': cfg.nonce } } });
    // Example: subscribe to a league channel if present on page via data attribute
    const leagueEl = document.querySelector('[data-snowdraft-league]') as HTMLElement | null;
    const leagueId = leagueEl ? leagueEl.dataset.snowdraftLeague : undefined;
    if (leagueId) {
      const channel = pusher.subscribe(`private-snowdraft-league-${leagueId}`);
      channel.bind('draft.started', (data: any) => {
        console.info('Draft started', data);
      });
      channel.bind('pick.made', (data: any) => {
        console.info('Pick made', data);
      });
    }
  }
})();
