import 'highlight.js/styles/github.css';
import Highlight from 'highlight.js/lib/highlight';
import cpp from 'highlight.js/lib/languages/cpp';

Highlight.registerLanguage('cpp', cpp);
Highlight.registerLanguage('php', php);
Highlight.initHighlightingOnLoad();
