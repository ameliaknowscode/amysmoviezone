import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

import './credits-manager';

Alpine.start();

import TomSelect from 'tom-select';

document.querySelectorAll('.tom-select').forEach(el => new TomSelect(el, { plugins: ['remove_button'] }));
