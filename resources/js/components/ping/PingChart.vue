<template>
    <div class="mt-3">
        <b-card class="mb-2">
            <div class="row">
                <div class="col-md-11 px-p">
                    <line-chart :chart-data="datacollection" :options="options" style="height: 300px"></line-chart>
                </div>
                <div class="col-md-1 ml-auto">
                    <div class="d-flex flex-column bd-highlight float-left">
                        <div class="p-2 bd-highlight mb-5 button-top">
                            <b-button pill class="float-left btn btn-danger" v-on:click="close" type="button" >
                                <b-icon-x-circle-fill></b-icon-x-circle-fill> 
                                <span>Close</span>
                            </b-button>
                        </div>
                        <div class="p-2 bd-highlight mb-5 mt-1 button-middle">
                            <b-button pill class="float-left btn btn-primary" v-on:click="refreshData" type="button" >
                                <b-icon-arrow-counterclockwise></b-icon-arrow-counterclockwise> 
                                <span>Refresh</span>
                            </b-button>
                        </div>
                        <div class="p-2 bd-highlight mt-1 button-bottom">
                            <b-button pill class="float-left btn text-white" :style="{ 'background-color': toggleColor }" v-on:click="togglePause" type="button" :disabled="toggleDisabled">
                                <b-icon-play-circle-fill v-if="isPaused"></b-icon-play-circle-fill> 
                                <b-icon-pause-circle-fill v-else></b-icon-pause-circle-fill> 
                                <span>{{toggleText}}</span>
                            </b-button>
                        </div>
                        
                    </div>
                </div>
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
                            backgroundColor: 'rgb(141 216 84 / 45%)',
                            borderWidth: 2,
                            radius: 1,
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
                            }, 3000
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

                if (this.datacollection.labels.length > 150) {
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
                            backgroundColor: 'rgb(141 216 84 / 45%)',
                            borderWidth: 2,
                            radius: 1,
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

.button-top {
    top: 0;
    position: absolute;
    right: 0;
}

.button-middle {
    position: absolute;
    right: 0;
    top: 50%;
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
}
.button-bottom {
    position: absolute;
    right: 0;
    bottom: 0;
}
</style>