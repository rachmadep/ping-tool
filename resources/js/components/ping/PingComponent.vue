<template>
    <div class="col-lg-8 col-md-10">
        <b-card>
            <b-form inline @submit.prevent="submitPing">
                <label class="mr-sm-3" for="inputHostname">Enter a hostname</label>
                <b-form-input
                    id="inputHostname"
                    v-model="hostname"
                    class="mb-2 mr-sm-3 mb-sm-0 col-sm-9"
                    placeholder="ex: google.com"
                ></b-form-input>
                <b-button type="submit" variant="primary">PING</b-button>
            </b-form>
        </b-card>
        
        <div v-for="item in PingItems" :key="item.id">
            <ping-chart :hostname="item.hostname"></ping-chart>
        </div>
    </div>
    
</template>

<script>
    import PingChart from './PingChart.vue'
    export default {
        components: {
            PingChart
        },
        data() {
            return {
                hostname: "",
                PingItems: [
                    {hostname: 'google.com'},
                ]
            }
        },
        mounted() {
            console.log('Component mounted.')
        },
        computed: {
            
        },
        methods: {
            submitPing() {
                console.log('Input value: ', this.hostname)
                if(this.hostname === "") {
                    return;
                }

                this.addPingChart(this.hostname)
            },
            addPingChart(newHostname) {
                this.PingItems.push({hostname: newHostname});
            }
        },
    }
</script>
