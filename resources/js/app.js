import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

import './credits-manager';
import './director-compare';
import './star-rating';

Alpine.start();

import TomSelect from 'tom-select';

document.querySelectorAll('.tom-select').forEach(el => new TomSelect(el, { plugins: ['remove_button'] }));
