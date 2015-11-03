import {each} from '../helpers';
import TablesHandler from '../TablesHandler';

each('table', table => {
    const tables = new TablesHandler(table);
    tables.enable();
});
