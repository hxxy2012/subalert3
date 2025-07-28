<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>数据统计</h2>
<h3>用户增长趋势（最近6个月）</h3>
<canvas id="userChart" width="400" height="200"></canvas>
<h3>订阅新增趋势（最近6个月）</h3>
<canvas id="subChart" width="400" height="200"></canvas>
<h3>用户活跃趋势（登录次数，最近6个月）</h3>
<canvas id="loginChart" width="400" height="200"></canvas>
<h3>订阅类型分布</h3>
<canvas id="typeChart" width="400" height="200"></canvas>
<h3>提醒状态分布</h3>
<canvas id="reminderChart" width="400" height="200"></canvas>
<h3>订阅价格统计</h3>
<p>最高价格：<?php echo htmlspecialchars($priceStats['max_price'] ?? 0); ?> 元</p>
<p>最低价格：<?php echo htmlspecialchars($priceStats['min_price'] ?? 0); ?> 元</p>
<p>平均价格：<?php echo number_format((float)($priceStats['avg_price'] ?? 0), 2); ?> 元</p>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const userLabels = <?php echo json_encode(array_keys($userData)); ?>;
const userValues = <?php echo json_encode(array_values($userData)); ?>;
const subLabels = <?php echo json_encode(array_keys($subData)); ?>;
const subValues = <?php echo json_encode(array_values($subData)); ?>;
const loginLabels = <?php echo json_encode(array_keys($loginData)); ?>;
const loginValues = <?php echo json_encode(array_values($loginData)); ?>;
const typeLabels = <?php echo json_encode(array_keys($typeData)); ?>;
const typeValues = <?php echo json_encode(array_values($typeData)); ?>;
const reminderLabels = <?php echo json_encode(array_keys($reminderData)); ?>;
const reminderValues = <?php echo json_encode(array_values($reminderData)); ?>;
new Chart(document.getElementById('userChart'), {
    type: 'line',
    data: {
        labels: userLabels,
        datasets: [{
            label: '新增用户数',
            data: userValues,
            borderColor: 'rgba(75,192,192,1)',
            backgroundColor: 'rgba(75,192,192,0.2)',
            fill: true,
        }]
    },
    options: {scales: {y: {beginAtZero: true}}}
});
new Chart(document.getElementById('subChart'), {
    type: 'bar',
    data: {
        labels: subLabels,
        datasets: [{
            label: '新增订阅数',
            data: subValues,
            backgroundColor: 'rgba(255,159,64,0.5)',
            borderColor: 'rgba(255,159,64,1)',
            borderWidth: 1
        }]
    },
    options: {scales: {y: {beginAtZero: true}}}
});

// Login trend chart
new Chart(document.getElementById('loginChart'), {
    type: 'line',
    data: {
        labels: loginLabels,
        datasets: [{
            label: '登录次数',
            data: loginValues,
            borderColor: 'rgba(153,102,255,1)',
            backgroundColor: 'rgba(153,102,255,0.2)',
            fill: true,
        }]
    },
    options: {scales: {y: {beginAtZero: true}}}
});

// Subscription type distribution (pie chart)
new Chart(document.getElementById('typeChart'), {
    type: 'pie',
    data: {
        labels: typeLabels,
        datasets: [{
            data: typeValues,
            backgroundColor: ['rgba(255,99,132,0.5)','rgba(54,162,235,0.5)','rgba(255,206,86,0.5)','rgba(75,192,192,0.5)','rgba(153,102,255,0.5)'],
            borderColor: ['rgba(255,99,132,1)','rgba(54,162,235,1)','rgba(255,206,86,1)','rgba(75,192,192,1)','rgba(153,102,255,1)'],
            borderWidth: 1
        }]
    },
    options: {
        plugins: {legend: {position: 'top'}},
    }
});

// Reminder status distribution (bar chart)
new Chart(document.getElementById('reminderChart'), {
    type: 'bar',
    data: {
        labels: reminderLabels,
        datasets: [{
            label: '数量',
            data: reminderValues,
            backgroundColor: 'rgba(255,205,86,0.5)',
            borderColor: 'rgba(255,205,86,1)',
            borderWidth: 1
        }]
    },
    options: {scales: {y: {beginAtZero: true}}}
});
</script>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>