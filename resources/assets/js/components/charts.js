import Chart from 'chart.js';

Chart.defaults.global = {
    ...Chart.defaults.global, ...{
        scaleShowLabels: false,
        showScale:       false,
        responsive:      true,
    }
};

const context = document.getElementById('chart').getContext('2d');
const options = {
    pointDot:           false,
    scaleShowGridLines: false,
    datasetStrokeWidth: 5,
};

const chart = new Chart(context).Line(chartData, options);