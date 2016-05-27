import 'bootstrap.native/lib/collapse-native';
import 'font-awesome/scss/font-awesome.scss';
import '../sass/flatly.scss';
import '../sass/styles.scss';
import './components/tables';
import './components/tooltips';

if (document.querySelector('.request, .comment__header')) {
    require.ensure([], () => {
        require('./components/commentsFolding');

        if (document.querySelector('.request')) {
            require('./components/requestNavigation');
            require('./components/highlight');
        }
    });
}

if (document.querySelector('canvas')) {
    require.ensure([], () => {
        require('./components/charts');
    });
}
