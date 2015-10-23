import $ from 'jquery';
import 'tablesorter';
import 'tablesorter/dist/js/widgets/widget-filter.min';
import 'bootstrap/js/collapse';

import 'bootswatch/flatly/bootstrap.css';
import '../sass/styles.scss';

$('.table').tablesorter({
    widgets:       ['filter'],
    widgetOptions: {
        filter_external:      '.layout-search',
        filter_columnFilters: false,
        filter_searchDelay:   100,
    },
});
