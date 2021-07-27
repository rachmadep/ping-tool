<template>
    <div class="mt-3">
        <b-card
            :title="titleText"
            class="mb-2"
        >
            <line-chart :chart-data="datacollection" :options="options"></line-chart>
            <button class="btn" :style="{ 'background-color': toggleColor }" v-on:click="togglePause" type="button" :disabled="toggleDisabled">
                {{toggleText}}
            </button>
            <button class="btn btn-primary" v-on:click="refreshData" type="button" >
                Refresh
            </button>
            <button class="btn btn-danger" v-on:click="close" type="button" >
                Close
            </button>
        </b-card>
    </div>
</template>

<script>
    import LineChart from './LineChart.js'

    export default {
        components: {
            LineChart
        },
        props: {
            hostname: String,
        },
        data () {
            return {
                isPaused: false,
                toggleText: 'Pause',
                toggleColor: '#ffed4a',
                toggleDisabled: false,
                // hostname: 'google.com',
                datacollection: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Latency',
                            backgroundColor: '#38c172',
                            data: []
                        }
                    ]
                },
                options: {
                    responsive: true,
                    title: {
                        display: false
                    },
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }],
                        xAxes: [{
                            type: 'time',
                            time: {
                                unit: 'minute',
                                tooltipFormat: 'h:mm'
                                }
                        }],
                    }
                }
            }
        },
        mounted () {
            this.getPing()
            // setInterval(() => {
            //     if (!this.isPaused) {
            //         this.getPing();
            //     }
            // }, 5000);
        },
        computed: {
            titleText() {
                return this.hostname
            }
        },
        methods: {
            async getPing() {
                try {
                    return await axios.get(`/chart`, {
                        params: {
                            hostname: this.hostname
                        }
                    }).then((response) => {
                        console.log(response.data)
                        this.addData(response.data.time, response.data.latency)
                        setTimeout(() => {  
                                if (!this.isPaused) {
                                    this.getPing();
                                }
                            }, 2000
                        )
                        
                    });
                    
                } catch (error) {
                    
                }
            },
            addData(label, data) {
                this.datacollection = {
                    ...this.datacollection,
                    labels: this.datacollection.labels.concat(label)
                }
                this.datacollection.datasets[0] = {
                    ...this.datacollection.datasets[0],
                    data: this.datacollection.datasets[0].data.concat(data)
                }

                if (this.datacollection.labels.length > 120) {
                    this.datacollection.labels.shift();
                    this.datacollection.datasets[0].data.shift();
                }
                console.log(this.datacollection)
            },
            togglePause() {
                this.isPaused = !this.isPaused
                this.toggleDisabled = true;
                if (this.isPaused) {
                    this.toggleText = 'Resume'
                    this.toggleColor = '#3490dc'
                } else {
                    this.toggleText = 'Pause'
                    this.toggleColor = '#ffed4a'
                    this.getPing()
                }
                setTimeout(() => this.toggleDisabled = false, 5000);

                console.log(this.isPaused);
            },
            refreshData() {
                this.datacollection = {
                    labels: [],
                    datasets: [
                        {
                            label: 'Latency',
                            backgroundColor: '#38c172',
                            data: []
                        }
                    ]
                }
            },

            close () {
                this.isPaused = true
                // destroy the vue listeners, etc
                this.$destroy();

                // remove the element from the DOM
                this.$el.parentNode.removeChild(this.$el);
            }
        },
        watch: {
            
        }
    }
</script>

<style>
</style>