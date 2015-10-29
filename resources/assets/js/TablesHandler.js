import {each, nodelistToArray} from './helpers';

export default class TablesHandler {

    cellIndex = 0;
    th        = '';
    order     = null;

    /**
     * Enable the TableSorter
     */
    enable() {
        // Bind sorting
        each('th', heading => {
            heading.onclick = this.onClickEvent.bind(this);
        });

        // Bind search if present
        const search = document.getElementById('search');
        if (search) {
            search.onkeyup = this.search.bind(this);
        }
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// SEARCH ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Filter the results of the table
     *
     * @param {Event} event
     */
    search(event) {
        each('tbody tr', row => {
            const text    = row.textContent || row.innerText;
            const matches = text.match(new RegExp(event.target.value, 'i'));
            row.classList.toggle('filtered', !matches);
        });
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// SORTING ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Sort two rows
     *
     * @param {Element} first
     * @param {Element} second
     *
     * @returns {number}
     */
    sort(first, second) {
        let valueA = this.getText(first);
        let valueB = this.getText(second);

        // Get numeric value if necessary
        const numericValue = parseInt(valueA, 10);
        if (numericValue) {
            valueA = numericValue;
            valueB = parseInt(valueB, 10);
        }

        return this.spaceship(valueA, valueB);
    }

    /**
     * Toggle the sorting order
     */
    toggle() {
        const newOrder = this.order !== true;

        this.th.classList.remove(this.order ? 'asc' : 'desc');
        this.th.classList.add(newOrder ? 'asc' : 'desc');
        this.order = newOrder;
    }

    /**
     * Reset the sorting order
     */
    reset() {
        this.order = null;
        this.th.classList.remove('asc');
        this.th.classList.remove('desc');
    }

    /**
     * Sort the table
     *
     * @param {Event} event
     */
    onClickEvent(event) {
        if (this.th && this.cellIndex !== event.target.cellIndex) {
            this.reset();
        }

        // Assign current header and its index
        this.th        = event.target;
        this.cellIndex = this.th.cellIndex;
        const tbody    = this.th.offsetParent.querySelector('tbody');

        let rows = nodelistToArray(tbody.rows);
        if (rows) {
            rows = rows.sort(this.sort.bind(this));
            if (this.order) {
                rows.reverse();
            }

            this.toggle();
            tbody.innerHtml = '';
            rows.forEach(row => tbody.appendChild(row));
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Extract the text of a row
     *
     * @param {object} row
     *
     * @returns {string}
     */
    getText(row) {
        return row.cells.item(this.cellIndex).textContent.toLowerCase();
    }

    /**
     * Half-assed spaceship operator in JS
     *
     * @param {number} a
     * @param {number} b
     *
     * @returns {number}
     */
    spaceship(a, b) {
        switch (true) {
            case a > b:
                return 1;
            case a < b:
                return -1;
            default:
                return 0;
        }
    }
}
