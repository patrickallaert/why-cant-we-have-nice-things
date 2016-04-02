import 'highlight.js/styles/github.css';
import Highlight from 'highlight.js/lib/highlight';
import cpp from 'highlight.js/lib/languages/cpp';
import diff from 'highlight.js/lib/languages/diff';
import javascript from 'highlight.js/lib/languages/javascript';
import json from 'highlight.js/lib/languages/json';
import php from 'highlight.js/lib/languages/php';
import python from 'highlight.js/lib/languages/python';

// Import languages commonly used in RFCs
Highlight.registerLanguage('cpp', cpp);
Highlight.registerLanguage('diff', diff);
Highlight.registerLanguage('javascript', javascript);
Highlight.registerLanguage('json', json);
Highlight.registerLanguage('php', php);
Highlight.registerLanguage('python', python);

Highlight.initHighlightingOnLoad();
