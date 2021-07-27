<script>
import { Line, mixins } from 'vue-chartjs';
import 'chartjs-plugin-zoom';
// const { reactiveProp } = mixins;
import { CustomTooltips } from '@coreui/coreui-plugin-chartjs-custom-tooltips';
import { hexToRgba } from '@coreui/coreui/dist/js/coreui-utilities';

export default {
  extends: Line,
  // props: ['width'],
  props: {
    xAxisValue: String,
    yAxisValue: String,
    chartDate: {
      type: Object,
      default: function () {
        return { min: null, max: null };
      }
    }
  },
  mixins: [mixins.reactiveProp],
  components: {
    hexToRgba,
    CustomTooltips
  },
  data() {
    return {

    };
  },
  computed: {
    opt() {
      return {
        responsive: true,
        responsiveAnimationDuration: 500,
        maintainAspectRatio: false,
        scales: {
          xAxes: [{
            type: 'time',
            display: true,
            time: {
              displayFormats: {
                hour: this.xAxisValue
              }
              // max: new Date('2019-08-10')
            },
            scaleLabel: {
              display: true,
              labelString: 'Time'
            }
          }],
          yAxes: [{
            display: true,
            scaleLabel: {
              display: true,
              labelString: this.yAxisValue
            }
            // ticks: {
            //   beginAtZero: true
            // }
          }]
        },
        elements: {
          line: {
            tension: 0
          }
        },
        tooltips: {
          mode: 'index',
          intersect: false
        },
        hover: {
          mode: 'index',
          intersect: false
        },
        pan: {
          enabled: true,
          mode: 'x',
          rangeMax: {
            //max date on chartData
            x: this.chartDate.max
          },
          rangeMin: {
            //min date on chartData
            x: this.chartDate.min
          }
        },
        zoom: {
          enabled: true,
          mode: 'x',
          // drag: true,
          rangeMax: {
            //max date on chartData
            x: this.chartDate.max
          },
          rangeMin: {
            //min date on chartData
            x: this.chartDate.min
          }
        }
      };
    }
  },
  mounted() {
    this.renderChart(
      this.chartData,
      this.opt
    );
  },
  watch: {
    yAxisValue: {
      handler(newOption, oldOption) {
        this.renderChart(this.chartData, this.opt);
      },
      deep: true
    },
    chartDate: {
      handler(newOption, oldOption) {
        this.renderChart(this.chartData, this.opt);
      },
      deep: true
    }
  },
  methods: {
    resetZoom() {
      this.$data._chart.resetZoom();
    }
  }
};
</script>
