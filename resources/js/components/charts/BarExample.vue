<script>
import { Bar, mixins } from 'vue-chartjs';
import { CustomTooltips } from '@coreui/coreui-plugin-chartjs-custom-tooltips';

export default {
  extends: Bar,
  // props: ['height'],
  mixins: [mixins.reactiveProp],
  data() {
    return {
      opt: {
        scales: {
          xAxes: [{
            maxBarThickness: 60,
          }],
          yAxes: [{
            ticks: {
              beginAtZero: true
            },
            scaleLabel: {
              display: true,
              labelString: 'Frequency'
            }
          }]
        },
        responsive: true,
        maintainAspectRatio: false,
        // tooltips: {
        //   enabled: false,
        //   custom: CustomTooltips,
        //   intersect: true,
        //   mode: 'index',
        //   position: 'nearest',
        //   callbacks: {
        //     labelColor: function (tooltipItem, chart) {
        //       return { backgroundColor: chart.data.datasets[tooltipItem.datasetIndex].backgroundColor };
        //     }
        //   }
        // }
      }
    };
  },
  mounted() {
    this.renderChart(
      this.chartData,
      this.opt
    );
  }
};
</script>
