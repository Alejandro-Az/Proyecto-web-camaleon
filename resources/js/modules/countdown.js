export default function initCountdownModule() {
  const el = document.querySelector('[data-module="countdown"]');
  if (!el) return;

  const target = el.getAttribute('data-countdown-target');
  if (!target) return;

  const targetDate = new Date(target);

  const daysEl = el.querySelector('[data-countdown-days]');
  const hoursEl = el.querySelector('[data-countdown-hours]');
  const minutesEl = el.querySelector('[data-countdown-minutes]');
  const secondsEl = el.querySelector('[data-countdown-seconds]');
  const expiredEl = el.querySelector('[data-countdown-expired]');

  const pad2 = (n) => String(Math.max(0, n)).padStart(2, '0');

  function render(msLeft) {
    if (msLeft <= 0) {
      const grid = el.querySelector('.grid');
      if (grid) grid.classList.add('hidden');
      if (expiredEl) expiredEl.hidden = false;
      return;
    }

    const totalSeconds = Math.floor(msLeft / 1000);
    const days = Math.floor(totalSeconds / (60 * 60 * 24));
    const hours = Math.floor((totalSeconds % (60 * 60 * 24)) / (60 * 60));
    const minutes = Math.floor((totalSeconds % (60 * 60)) / 60);
    const seconds = totalSeconds % 60;

    if (daysEl) daysEl.textContent = String(days);
    if (hoursEl) hoursEl.textContent = pad2(hours);
    if (minutesEl) minutesEl.textContent = pad2(minutes);
    if (secondsEl) secondsEl.textContent = pad2(seconds);
  }

  function tick() {
    const now = new Date();
    const msLeft = targetDate.getTime() - now.getTime();
    render(msLeft);
  }

  tick();
  setInterval(tick, 1000);
}
