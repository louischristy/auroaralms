document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('deptChartData');
    if (!el) return;
    var canvas = document.getElementById('deptChart');
    if (!canvas) return;
    try {
        var labels = JSON.parse(el.getAttribute('data-labels'));
        var values = JSON.parse(el.getAttribute('data-values'));
        var colors = JSON.parse(el.getAttribute('data-colors'));
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Completion %',
                    data: values,
                    backgroundColor: colors
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, max: 100 } }
            }
        });
    } catch(e) {
        console.error('Chart init error:', e);
    }
});
