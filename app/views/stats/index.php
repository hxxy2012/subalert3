<h2>支出统计分析</h2>

<h3>最近6个月支出趋势</h3>
<canvas id="monthlyChart" width="400" height="200"></canvas>

<h3>各服务类型支出分布</h3>
<canvas id="typeChart" width="400" height="200"></canvas>

<h3>订阅周期分布</h3>
<canvas id="cycleChart" width="400" height="200"></canvas>

<p style="margin-top:20px;"><a href="/?r=stats-export" class="btn">导出CSV</a></p>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const monthlyLabels = <?php echo json_encode(array_keys($monthly)); ?>;
const monthlyValues = <?php echo json_encode(array_values($monthly)); ?>;
const typeLabels = <?php echo json_encode(array_keys($types)); ?>;
const typeValues = <?php echo json_encode(array_values($types)); ?>;
const cycleLabels = <?php echo json_encode(array_keys($cycles)); ?>;
const cycleValues = <?php echo json_encode(array_values($cycles)); ?>;

// Monthly expense line chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: '支出（元）',
            data: monthlyValues,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Type distribution pie chart
new Chart(document.getElementById('typeChart'), {
    type: 'pie',
    data: {
        labels: typeLabels,
        datasets: [{
            data: typeValues,
            backgroundColor: [
                'rgba(255, 99, 132, 0.5)',
                'rgba(54, 162, 235, 0.5)',
                'rgba(255, 206, 86, 0.5)',
                'rgba(75, 192, 192, 0.5)',
                'rgba(153, 102, 255, 0.5)'
            ],
        }]
    }
});

// Cycle distribution bar chart
new Chart(document.getElementById('cycleChart'), {
    type: 'bar',
    data: {
        labels: cycleLabels,
        datasets: [{
            label: '数量',
            data: cycleValues,
            backgroundColor: 'rgba(255, 159, 64, 0.5)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>