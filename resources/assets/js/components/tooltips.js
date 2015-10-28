import Tooltip from 'bootstrap.native/lib/tooltip-native';
import each from '../helpers/each';

// Setup tooltips on each abbr element
each('abbr', tooltip => {
    new Tooltip(tooltip, {});
});
