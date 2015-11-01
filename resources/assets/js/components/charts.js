/* globals chartData */
import Chart from 'chart.js';

Chart.defaults.global = {
    ...Chart.defaults.global, ...{
        responsive:       true,
        scaleBeginAtZero: true,
    },
};

const context = document.getElementById('chart').getContext('2d');
const options = {
    pointDot:           false,
    scaleShowGridLines: false,
    datasetStrokeWidth: 5,
};

new Chart(context).Line(chartData, options);
