/**
 * Transform a NodeList to an Array
 *
 * @param {NodeList|string} nodeList
 *
 * @return {Array}
 */
export default function nodelistToArray(nodeList) {
    if (typeof nodeList === 'string') {
        nodeList = document.querySelectorAll(nodeList);
    }

    return Reflect.apply(Array.prototype.slice, nodeList, [0]);
}
