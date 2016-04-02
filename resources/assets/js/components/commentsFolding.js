import each from '../helpers/each';

each('.comment__header', header => {
    header.onclick = event => event.currentTarget.parentNode.classList.toggle('comment--folded');
});
