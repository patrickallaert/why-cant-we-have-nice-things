import 'highlight.js/styles/github.css';
import Highlight from 'highlight.js/lib/highlight';
import php from 'highlight.js/lib/languages/php';

Highlight.registerLanguage('php', php);
Highlight.initHighlightingOnLoad();
