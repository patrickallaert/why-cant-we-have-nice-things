import Tooltip from 'bootstrap.native/lib/tooltip-native';

// Setup tooltips on each abbr element
const tooltips = document.querySelectorAll('abbr');
for (let i = 0; i < tooltips.length; i++) {
    new Tooltip(tooltips[i], {});
}
