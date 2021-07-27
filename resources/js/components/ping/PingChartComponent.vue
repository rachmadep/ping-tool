<template>
    <div>
        <line-example
            ref="lineChart"
            :chart-data="chartDataLine"
            :styles="chartContainerStyle"
            :y-axis-value="yAxisValue"
            :x-axis-value="xAxisValue"
            :chart-date="chartDate"
        />
    </div>
</template>

<script>
    import LineExample from '../charts/LineExample';
    export default {
        name: 'PingChartComponent',
        components: { LineExample },
        mounted() {
            console.log('Component mounted.')
        },
        data() {
            return {
                showHistogram: false,
                selectedPayloads: null,
                xAxisValue: 'HH:mm:ss',
                configPayloadsDate: {
                    mode: 'single',
                    wrap: true, // set wrap to true only when using 'input-group'
                    altFormat: 'j M Y',
                    altInput: true,
                    maxDate: 'today',
                    disableMobile: 'true'
                }
            };
        },
        computed: {
            chartDataLine() {
                //dataset for line chart / timeseries
                return {
                    datasets: [
                    {
                        label: 'Payloads',
                        backgroundColor: hexToRgba('#fff', 0),
                        borderColor: hexToRgba('#E46651', 100),
                        pointBackgroundColor: hexToRgba('#E46651', 100),
                        borderWidth: 1,
                        pointRadius: 0,
                        data: this.createDatasetTimeSeries(this.selectedPayloads),
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: 'red'
                    }
                    ]
                };
            },
        }
    }
</script>
