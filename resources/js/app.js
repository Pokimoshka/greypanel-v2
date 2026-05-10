import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import 'bootstrap';
import 'highlight.js/styles/github-dark.css';

import MarkdownEditor from './editor/MarkdownEditor';
import './theme-editor.js';

import onlineWidget from './components/onlineWidget';
import lastTopicsWidget from './components/lastTopicsWidget';
import lastBansWidget from './components/lastBansWidget';
import topDonatorsWidget from './components/topDonatorsWidget';
import collapsibleWidget from './components/collapsibleWidget';
import chatWidget from './components/chat';
import likeButton from './components/like';
import modal from './components/modal';
import monitorWidget from './components/monitor';
import quote from './components/quote';
import sortableList from './components/sortable';
import markdownEditor from './components/markdownEditor';
import banActions from './components/banActions';
import replyForm from './components/replyForm';
import toast from './components/toast';

import './utils/toast-global.js';

Alpine.data('onlineWidget', onlineWidget);
Alpine.data('lastTopicsWidget', lastTopicsWidget);
Alpine.data('lastBansWidget', lastBansWidget);
Alpine.data('topDonatorsWidget', topDonatorsWidget);
Alpine.data('collapsibleWidget', collapsibleWidget);
Alpine.data('chatWidget', chatWidget);
Alpine.data('likeButton', likeButton);
Alpine.data('modal', modal);
Alpine.data('monitorWidget', monitorWidget);
Alpine.data('quote', quote);
Alpine.data('sortableList', sortableList);
Alpine.data('markdownEditor', markdownEditor);
Alpine.data('banActions', banActions);
Alpine.data('replyForm', replyForm);
Alpine.data('toast', toast);

Alpine.plugin(collapse);

document.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
});
