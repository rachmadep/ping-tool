<template>
    <div class="mt-3">
        <b-card class="mb-2">
            <line-chart :chart-data="datacollection" :options="options" style="height: 300px"></line-chart>
            <div class="d-flex justify-content-end">
                <b-button pill class="btn text-white" :style="{ 'background-color': toggleColor }" v-on:click="togglePause" type="button" :disabled="toggleDisabled">
                    <b-icon-play-circle-fill v-if="isPaused"></b-icon-play-circle-fill> 
                    <b-icon-pause-circle-fill v-else></b-icon-pause-circle-fill> 
                    <span>{{toggleText}}</span>
                </b-button>
                <b-button pill class="btn btn-primary ml-2" v-on:click="refreshData" type="button" >
                    <b-icon-arrow-counterclockwise></b-icon-arrow-counterclockwise> 
                    <span>Refresh</span>
                </b-button>
                <b-button pill class="btn btn-danger ml-2" v-on:click="close" type="button" >
                    <b-icon-x-circle-fill></b-icon-x-circle-fill> 
                    <span>Close</span>
                </b-button>
            </div>
        </b-card>
    </div>
</template>

<script>
    import LineChart from '../LineChart.js'

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
                toggleColor: '#ffa64a',
                toggleDisabled: false,
                datacollection: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Latency',
                            borderColor: 'rgb(0 162 224)',
                            backgroundColor: 'rgb(170 236 120 / 45%)',
                            borderWidth: 2,
                            radius: 2,
                            data: []
                        }
                    ]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: this.hostname,
                        align: 'center',
                        fontSize: 16,
                        fontStyle: 'bold'
                    },
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },

                    scales: {
                        yAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'ms'
                            },
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
                        // console.log(response.data)
                        this.addData(response.data.time, response.data.latency)
                        setTimeout(() => {  
                                if (!this.isPaused) {
                                    this.getPing();
                                }
                            }, 2000
                        )
                        
                    });
                    
                } catch (error) {
                    console.log(error)
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
            },
            togglePause() {
                this.isPaused = !this.isPaused
                this.toggleDisabled = true;
                if (this.isPaused) {
                    this.toggleText = 'Resume'
                    this.toggleColor = '#3490dc'
                } else {
                    this.toggleText = 'Pause'
                    this.toggleColor = '#ffa64a'
                    this.getPing()
                }
                setTimeout(() => this.toggleDisabled = false, 2000);

                console.log(this.isPaused);
            },
            refreshData() {
                this.datacollection = {
                    labels: [],
                    datasets: [
                        {
                            label: 'Latency',
                            borderColor: 'rgb(0 162 224)',
                            backgroundColor: 'rgb(170 236 120 / 45%)',
                            borderWidth: 2,
                            radius: 2,
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
button > span {
    vertical-align: middle;
}
</style>