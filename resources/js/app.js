import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import TomSelect from 'tom-select';

document.querySelectorAll('.tom-select').forEach(el => new TomSelect(el, { plugins: ['remove_button'] }));
