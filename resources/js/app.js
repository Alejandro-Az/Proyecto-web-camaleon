import './bootstrap';

import initSongsModule from './modules/songs';
import initGiftsModule from './modules/gifts';
// import initGuestPhotosModule from './modules/guest_photos'; // si ya lo separa

document.addEventListener('DOMContentLoaded', () => {
    initSongsModule();
    initGiftsModule();
    // initGuestPhotosModule();
});
