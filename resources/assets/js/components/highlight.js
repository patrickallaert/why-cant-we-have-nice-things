import 'highlight.js/styles/github.css';
import Highlight from 'highlight.js/lib/highlight.js';

// Import languages commonly used in RFCs
Highlight.registerLanguage('cpp', require('highlight.js/lib/languages/cpp'));
Highlight.registerLanguage('diff', require('highlight.js/lib/languages/diff'));
Highlight.registerLanguage('javascript', require('highlight.js/lib/languages/javascript'));
Highlight.registerLanguage('json', require('highlight.js/lib/languages/json'));
Highlight.registerLanguage('php', require('highlight.js/lib/languages/php'));
Highlight.registerLanguage('python', require('highlight.js/lib/languages/python'));

Highlight.initHighlightingOnLoad();
