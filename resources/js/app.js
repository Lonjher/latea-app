import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

document.addEventListener('alpine:init', () => {
    Alpine.data('salesChart', (config) => ({
        chart: null,

        get data() {
            return typeof config.data === 'function' ? config.data() : config.data;
        },
        get type() {
            return typeof config.type === 'function' ? config.type() : config.type;
        },

        async init() {
            // Import ApexCharts hanya saat komponen ini diinisialisasi (Lazy Load)
            if (!window.ApexCharts) {
                const module = await import('apexcharts');
                window.ApexCharts = module.default;
            }

            this.renderChart();

            Livewire.on('chart-updated', () => {
                this.$nextTick(() => {
                    this.renderChart();
                });
            });

            const observer = new MutationObserver(() => this.renderChart());
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },

        renderChart() {
            if (this.chart) {
                this.chart.destroy();
            }

            if (!this.$refs.chart || !this.data || !window.ApexCharts) return;

            const options = this.getChartOptions();
            this.chart = new ApexCharts(this.$refs.chart, options);
            this.chart.render();
        },

        getChartOptions() {
            const isDark = document.documentElement.classList.contains('dark');
            const categories = (this.data || []).map(item => item.label);

            let series = [];

            if (this.type === 'candlestick') {
                series = [{
                    name: 'Harga (OHLC)',
                    data: (this.data || []).map(item => ({
                        x: item.label,
                        y: [item.o, item.h, item.l, item.c]
                    }))
                }];
            } else {
                series = [{
                    name: 'Revenue',
                    data: (this.data || []).map(item => item.revenue)
                }];
            }

            return {
                series: series,
                chart: {
                    type: this.type,
                    height: 320,
                    toolbar: { show: false },
                    background: 'transparent',
                    foreColor: isDark ? '#a8a29e' : '#57534e',
                    animations: { enabled: true }
                },
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                stroke: {
                    curve: 'smooth',
                    width: this.type === 'line' ? 2 : 1
                },
                plotOptions: {
                    candlestick: {
                        colors: {
                            upward: '#22c55e',
                            downward: '#ef4444'
                        }
                    },
                    bar: {
                        borderRadius: 4,
                        columnWidth: '55%',
                    }
                },
                colors: ['#4f46e5'],
                xaxis: {
                    categories: categories,
                    labels: { style: { fontSize: '11px' } }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => {
                            if (this.type === 'candlestick') {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            }
                            return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(val);
                        },
                        style: { fontSize: '11px' }
                    }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
                    }
                },
                grid: {
                    borderColor: isDark ? '#27272a' : '#e7e5e4',
                    strokeDashArray: 4
                }
            };
        }
    }));
});
