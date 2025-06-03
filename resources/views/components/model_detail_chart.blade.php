<script>
    const chartData = @json($chartData);

    const sortedYears = chartData.map(item => item.year);
    const maxPrices = chartData.map(item => item.max_price);
    const minPrices = chartData.map(item => item.min_price);

    const chartConfig = (label, data, borderColor) => ({
        type: 'line',
        data: {
            labels: sortedYears,
            datasets: [{
                label: label,
                data: data,
                borderColor: borderColor,
                fill: false,
                tension: 0.2,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    title: { display: true, text: '価格 (万円)' }
                },
                x: {
                    title: { display: true, text: '年式' }
                }
            }
        }
    });

    new Chart(document.getElementById('maxPriceChart'), chartConfig('最高価格', maxPrices, 'blue'));
    new Chart(document.getElementById('minPriceChart'), chartConfig('最低価格', minPrices, 'red'));
</script>
