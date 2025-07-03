<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
    const chartData = @json($chartData);

    const years = chartData.map(item => item.year);
    const maxPrices = chartData.map(item => item.max_price);
    const minPrices = chartData.map(item => item.min_price);

    const createCombinedChart = (ctxId, label, data, color) => {
        const ctx = document.getElementById(ctxId).getContext('2d');
        return new Chart(ctx, {
            type: 'bar', // 基本は bar
            data: {
                labels: years,
                datasets: [
                    {
                        label: `${label}（棒）`,
                        data: data,
                        backgroundColor: color,
                        datalabels: {
                            display: true, // ✅ 表示する
                            anchor: 'end',
                            align: 'top'
                        }
                    },
                    {
                        label: `${label}（線）`,
                        data: data,
                        type: 'line',
                        borderColor: color,
                        backgroundColor: color,
                        fill: false,
                        tension: 0.2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        datalabels: {
                            display: false // ❌ 非表示にする
                        }
                    }
                ]

            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    datalabels: {
                        color: '#000',
                        font: {
                            weight: 'bold',
                            size: 10,
                        },
                        formatter: value => {
                            return value > 0 ? `${value} 万円` : ''; // nullや0はラベルなし
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        title: { display: true, text: '価格 (万円)' }
                    },
                    x: {
                        title: { display: true, text: '年式' }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    };

    // 最低価格チャート
    createCombinedChart('minPriceChart', '最低価格', minPrices, 'rgba(255, 99, 132, 0.7)');

    // 最高価格チャート
    createCombinedChart('maxPriceChart', '最高価格', maxPrices, 'rgba(54, 162, 235, 0.7)');
</script>
