import './bootstrap';

import initSongsModule from './modules/songs';
import initGiftsModule from './modules/gifts';
import initCountdownModule from './modules/countdown';
import initRsvpModule from './modules/rsvp';
// import initGuestPhotosModule from './modules/guest_photos'; // si ya lo separa

document.addEventListener('DOMContentLoaded', () => {
    initSongsModule();
    initGiftsModule();
    initCountdownModule();
    initRsvpModule();
    // initGuestPhotosModule();
});
