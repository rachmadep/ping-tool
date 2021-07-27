<script>
import { Line, mixins } from 'vue-chartjs';
import 'chartjs-plugin-zoom';
const { reactiveProp } = mixins;

export default {
  extends: Line,
  props: {
    yAxisValue: Object,
    chartDate: {
      type: Object,
      default: function () {
        return { min: null, max: null };
      }
    },
    options: {
      type: Object,
      default: null
    }
  },
  mixins: [reactiveProp],

  mounted() {
    this.renderChart(
      this.chartData,
      this.options
    );
  },

  methods: {
    resetZoom() {
      this.$data._chart.resetZoom();
    }
  },

  watch: {
    yAxisValue: {
      handler(newOption, oldOption) {
        this.renderChart(this.chartData, this.options);
      },
      deep: true
    },
    chartDate: {
      handler(newOption, oldOption) {
        this.renderChart(this.chartData, this.options);
      },
      deep: true
    }
  }
};
</script>
