@extends ('layouts.app')

@section ('content')

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card">

                <div class="card-body">
                    <form action="">
                        
                    </form>

                    <div class="row">
                        <div class="col-md-12">
                            <canvas id="myChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@parent
<script
    src="https://code.jquery.com/jquery-3.6.0.min.js"
    integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
    crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
{{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>


{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@0.1.1"></script>

<script>
    var ctx = document.getElementById("myChart");
    
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Speed',
                data: [],
                fill: false,
                backgroundColor: 'green',
                borderColor: 'green'
            }]
        },
        options: {
            responsive: true,
            title: {
            display: false
            },
            legend: {
            display: false
            },
            scales: {
            yAxes: [],
            xAxes: [{
                type: 'time',
                time: {
                    unit: 'minute',
                    tooltipFormat: 'h:mm'
                    }
                }],
            }
        }
    });
    var updateChart = function() {
        $.ajax({
        url: "{{ route('ping.chart') }}",
        type: 'GET',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            console.log(data);
            addData(myChart, data.time, data.latency)
        },
        error: function(data){
            console.log(data);
        }
        });
    }

    function addData(myChart, label, data) {
        myChart.data.labels.push(label);
        myChart.data.datasets.forEach((dataset) => {
            dataset.data.push(data);
        });
        if (myChart.data.labels.length > 180) {
            myChart.data.labels.shift();
            myChart.data.datasets.forEach((dataset) => {
                dataset.data.shift();
            });
        }

        myChart.update();
    }
    
    updateChart();
    setInterval(() => {
        updateChart();
    }, 5000);
</script>

@endsection
