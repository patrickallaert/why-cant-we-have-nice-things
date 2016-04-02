import 'bootstrap.native/lib/collapse-native';
import 'font-awesome/css/font-awesome.css';
import '../sass/flatly.scss';
import '../sass/styles.scss';

import './components/tables';
import './components/tooltips';

if (document.querySelector('.request')) {
    require.ensure([], () => {
        require('./components/highlight');
        require('./components/requestNavigation');
        require('./components/commentsFolding');
    });
}

if (document.querySelector('canvas')) {
    require.ensure([], () => {
        require('./components/charts');
    });
}
