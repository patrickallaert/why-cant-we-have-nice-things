import nodelistToArray from './nodelistToArray';

/**
 * Executes a forEeach on a QuerySelector
 *
 * @param  {NodeList|string}   selector
 * @param  {Function}   callback
 *
 * @return {void}
 */
export default function each(selector, callback) {
    const nodeList = nodelistToArray(selector);

    return Reflect.apply(Array.prototype.forEach, nodeList, [callback]);
}
