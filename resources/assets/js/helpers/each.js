/**
 * Executes a forEeach on a QuerySelector
 *
 * @param  {NodeList|string}   selector
 * @param  {Function}   callback
 *
 * @return {void}
 */
export default function each(selector, callback) {
    if (typeof(selector) === 'string') {
        selector = document.querySelectorAll(selector);
    }

    [].forEach.call(selector, callback);
}
