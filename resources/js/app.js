import './bootstrap';
import Alpine from 'alpinejs';
import 'bootstrap/dist/js/bootstrap.min.js';
import Sortable from 'sortablejs';
import 'trumbowyg';

import chatWidget from './components/chat';
import likeButton from './components/like';
import modal from './components/modal';
import monitorWidget from './components/monitor';
import quote from './components/quote';
import sortableList from './components/sortable';

window.Alpine = Alpine;
window.Sortable = Sortable;

Alpine.data('chatWidget', chatWidget);
Alpine.data('likeButton', likeButton);
Alpine.data('modal', modal);
Alpine.data('monitorWidget', monitorWidget);
Alpine.data('quote', quote);
Alpine.data('sortableList', sortableList);

Alpine.start();