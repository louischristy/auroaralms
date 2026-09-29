import Alpine from 'alpinejs';
import htmx from 'htmx.org';
import { registerAiComponents } from './ai-tools.js';

window.Alpine = Alpine;
window.htmx = htmx;

registerAiComponents(Alpine);

Alpine.start();
