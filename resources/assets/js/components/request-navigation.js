const navigation = document.querySelector('.request__navigation ul');
const titles     = Reflect.apply(
    Array.prototype.slice,
    document.querySelectorAll('h1, h2, h3, h4, h5'), [0]
);

// Simulate position: sticky
let stuck        = false;
const stickPoint = navigation.offsetTop;
window.onscroll  = () => {
    const distance = navigation.offsetTop - window.pageYOffset;
    const offset   = window.pageYOffset;
    const width    = `${navigation.parentNode.offsetWidth - 35}px`;

    if ((distance <= 0) && !stuck) {
        navigation.classList.add('sticky');
        navigation.style.width = width;
        stuck                  = true;
    } else if (stuck && (offset <= stickPoint)) {
        navigation.classList.remove('sticky');
        navigation.style.width = 'auto';
        stuck                  = false;
    }
};

// Gather links on the page
const links = [];
titles.forEach(title => {
    const id    = title.getAttribute('id');
    const name  = title.innerText.trim();
    const level = title.tagName.toLowerCase();
    if (!id || !name) {
        return;
    }

    links.push(`<li class="level-${level}"><a href="#${id}">${name}</a></li>`);
});

// Create dynamic Table of contents
navigation.innerHTML = links.join('');
