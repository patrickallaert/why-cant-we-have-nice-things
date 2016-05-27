/* globals chartData */
import Chart from 'chart.js';

new Chart(document.getElementById('chart'), {
    type: 'line',
    data: chartData,
    options: {
        pointDot: false,
        datasetStrokeWidth: 5,
        scales: {
            xAxes: [{
                gridLines: {
                    display: false,
                },
            }],
            yAxes: [{
                gridLines: {
                    display: false,
                },
                ticks: {
                    beginAtZero: true,
                },
            }],
        },
    },
});
