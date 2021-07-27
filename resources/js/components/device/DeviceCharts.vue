<template>
  <!-- Modal charts payload -->
  <b-modal
    id="modal-charts-payloads"
    ref="modal-charts-payloads"
    size="xl"
    centered
    title="Charts Payloads"
    ok-only
    @show="setSelectedPayloads"
  >
    <div
      v-if="isBusyPayloads"
      class="d-flex justify-content-center my-5"
    >
      <b-spinner label="Loading..."></b-spinner>
    </div>
    <div v-else>
      <b-form-group
        label="Select Key"
        label-for="selectPayloads"
        :label-cols-sm="3"
      >
        <b-input-group>
          <b-input-group-prepend is-text>
            <b-form-checkbox
              id="checkbox-1"
              name="checkbox-1"
              v-model="showHistogram"
              :disabled="selectedPayloads !== 'rssi' && selectedPayloads !== 'frequency'"
            >Show histogram</b-form-checkbox>
          </b-input-group-prepend>
          <b-form-select
            id="selectPayloads"
            v-model="selectedPayloads"
            :options="payloadsKey"
            value="Please select"
          ></b-form-select>
        </b-input-group>
        <b-form-text>
          Histogram can be enabled in
          <strong>rssi</strong> and
          <strong>frequency</strong> data
        </b-form-text>
      </b-form-group>
      <b-form-group
        label="Select Date"
        label-for="selectDate"
        :label-cols-sm="3"
      >
        <b-input-group>
          <b-input-group-prepend>
            <b-button
              variant="primary"
              @click="toggleMode"
            >Mode {{configPayloadsDate.mode}}</b-button>
          </b-input-group-prepend>
          <flat-pickr
            v-if="configPayloadsDate.mode == 'range'"
            id="selectDate"
            name="date"
            v-model="payloadsDateRange"
            :config="configPayloadsDate"
            class="form-control"
            placeholder="Select date range"
            @on-change="onChangeDateRange"
          ></flat-pickr>
          <flat-pickr
            v-else
            id="selectDate"
            name="date"
            v-model="payloadsDate"
            :config="configPayloadsDate"
            class="form-control"
            placeholder="Select date"
            @on-change="onChangeDate"
          ></flat-pickr>
        </b-input-group>
      </b-form-group>
    </div>
    <line-example
      ref="lineChart"
      :chart-data="chartDataLine"
      :styles="chartContainerStyle"
      :y-axis-value="yAxisValue"
      :x-axis-value="xAxisValue"
      :chart-date="chartDate"
    />
    <div class="d-flex justify-content-center">

      <b-list-group
        horizontal
        class="mb-3 font-weight-bold"
      >
        <b-list-group-item>Max : {{chartValue.max}} {{yAxisValue != 'Value'? yAxisValue:''}}</b-list-group-item>
        <b-list-group-item>Min : {{chartValue.min}} {{yAxisValue != 'Value'? yAxisValue:''}}</b-list-group-item>
        <b-list-group-item>Avg : {{chartValue.avg.toFixed(2)}} {{yAxisValue != 'Value'? yAxisValue:''}}</b-list-group-item>
      </b-list-group>
    </div>
    <b-alert
      variant="warning"
      show
    >
      <div class="d-md-flex justify-content-between align-items-center">
        <div>
          You can pan and zoom on line chart
        </div>
        <b-button
          class="d-none d-md-block"
          variant="secondary"
          size="sm"
          @click="$refs.lineChart.resetZoom();"
        >Reset View</b-button>
        <b-button
          class="d-block d-md-none btn-block"
          variant="secondary"
          size="sm"
          @click="$refs.lineChart.resetZoom();"
        >Reset View</b-button>
      </div>
    </b-alert>
    <div v-if="showHistogram == true">
      <hr />
      <bar-example
        :chart-data="chartDataBar"
        :styles="chartContainerStyle"
      />
    </div>
    <div class="d-flex justify-content-center">
      <!-- load more button for single day payload -->
      <b-button
        v-if="switchPayloadsDateRange == false && payloadsMeta.current_page != payloadsMeta.last_page"
        size="sm"
        variant="secondary"
        :disabled="isBusyAppendPayloads"
        @click.prevent="appendPayloads()"
      >
        <i
          class="fa fa-refresh"
          :class="{'fa-spin' : isBusyAppendPayloads}"
        ></i>
        Load more
      </b-button>
      <!-- load more button for date range payload -->
      <b-button
        v-if="switchPayloadsDateRange == true && payloadsMetaDateRange.current_page != payloadsMetaDateRange.last_page"
        size="sm"
        variant="primary"
        :disabled="isBusyAppendPayloads"
        @click.prevent="appendPayloadsDateRange()"
      >
        <i
          class="fa fa-refresh"
          :class="{'fa-spin' : isBusyAppendPayloads}"
        ></i>
        Load more
      </b-button>
    </div>
  </b-modal>
</template>

<script>
import LineExample from '../charts/LineExample';
import BarExample from '../charts/BarExample';
import { hexToRgba } from '@coreui/coreui/dist/js/coreui-utilities';
import FlatPickr from 'vue-flatpickr-component';
export default {
  name: 'DeviceCharts',
  components: { LineExample, BarExample, FlatPickr },
  props: {
    isBusyPayloads: Boolean,
    isBusyAppendPayloads: Boolean,
    fieldsPayloads: Array,
    switchPayloadsDateRange: Boolean,
    payloadsMeta: Object,
    payloadsMetaDateRange: Object,
    devicesPayloadProcessed: Array,
    payloadsDate: String,
    payloadsDateRange: String
  },
  data() {
    return {
      showHistogram: false,
      selectedPayloads: null,
      xAxisValue: 'HH:mm',
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
    payloadsKey() {
      //get keys from fieldpayloads
      //then filter to remove 'time' key
      //then map to value-text for bootstrap select
      let arr = this.fieldsPayloads.filter(val => {
        return val != 'time' && val != 'date';
      });
      return arr.map(item => {
        return {
          value: item,
          text: this.$options.filters.parsePayload(item)
        };
      });
    },
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
    chartDataBar() {
      //dataser for bar chart / histogram
      return {
        labels: this.createDatasetHistogram(this.selectedPayloads).arrFrequency,
        datasets: [
          {
            label: this.selectedPayloads,
            backgroundColor: 'rgb(54, 162, 235)',
            data: this.createDatasetHistogram(this.selectedPayloads).arrData
          }
        ]
      };
    },
    chartContainerStyle() {
      //style for chart container
      return {
        height: '50vh',
        position: 'relative'
      };
    },
    yAxisValue() {
      //map value for y axis in chart js
      switch (this.selectedPayloads) {
        case 'batt':
          return 'Volts';
        case 'frequency':
          return 'Hz';
        case 'cvolt':
          return 'Volts';
        case 'soil_wt':
          return 'kPa / cbar';
        case 'soil_temp':
          return 'Celcius';
        case 'soil_ec':
          return 'dS/m';
        case 'rtd_temp':
          return 'Celcius';
        case 'rssi':
          return 'dBm';
        case 'snr':
          return 'Decibels';
        case 'water_flow':
          return 'Liters';
        case 'water_vol':
          return 'Liters';
        default:
          return 'Value';
      }
    },
    chartValue() {
      let payloadValue = this.devicesPayloadProcessed.map(value => parseFloat(value[this.selectedPayloads]));
      let max = Math.max(...payloadValue);
      let min = Math.min(...payloadValue);
      //average
      let sum = payloadValue.reduce((previous, current) => current += previous, 0);
      let avg = sum / payloadValue.length;
      return { max, min, avg };
    },
    chartDate() {
      // create max min date object, for chartjs-plugin-zoom
      if (this.chartDataLine.datasets[0].data.length != 0) {
        let max = this.chartDataLine.datasets[0].data[0].x;
        let min = this.chartDataLine.datasets[0].data[this.chartDataLine.datasets[0].data.length - 1].x;
        return { max, min };
      }
    }
  },
  methods: {
    setSelectedPayloads() {
      //set selected payload to the first payload key
      this.selectedPayloads = this.payloadsKey[0].value;
    },
    createDatasetTimeSeries(val) {
      // create x,y data for line chart
      if (this.switchPayloadsDateRange) {
        let data = this.devicesPayloadProcessed.map(item => {
          return {
            x: new Date(item.date + 'T' + item.time),
            y: item[val]
          };
        });
        return data;
      } else {
        let data = this.devicesPayloadProcessed.map(item => {
          return {
            x: new Date(this.payloadsDate + 'T' + item.time),
            y: item[val]
          };
        });
        return data;
      }
    },
    createDatasetHistogram(val) {
      //create frequency data for bar chart
      let arr = this.devicesPayloadProcessed.map(item => {
        return item[val];
      });
      var a = [], b = [], prev;
      //sort number ascending
      arr.sort(function (a, b) { return a - b; });
      for (var i = 0; i < arr.length; i++) {
        if (arr[i] !== prev) {
          a.push(arr[i]);
          b.push(1);
        } else {
          b[b.length - 1]++;
        }
        prev = arr[i];
      }
      return { arrFrequency: a, arrData: b };
    },
    appendPayloads() {
      this.$emit('append-payloads');
    },
    appendPayloadsDateRange() {
      this.$emit('append-payloads-date-range');
    },
    toggleMode() {
      // set config flatpickr to range or single date
      if (this.configPayloadsDate.mode == 'single') {
        // if date range hasn't selected yet, DON'T change to 'mode switch range = true'
        // but always set config flatpickr
        if (this.payloadsDateRange) {
          this.$emit('update:switchPayloadsDateRange', true);
        }
        this.$set(this.configPayloadsDate, 'mode', 'range');
      } else {
        this.$emit('update:switchPayloadsDateRange', false);
        this.$set(this.configPayloadsDate, 'mode', 'single');
      }
    },
    onChangeDateRange(selectedDates, dateStr) {
      // sync to parent props payloadsDateRange
      // change parent from child
      this.$emit('update:payloadsDateRange', dateStr);
    },
    onChangeDate(selectedDates, dateStr) {
      // sync to parent props payloadsDate
      // change parent from child
      this.$emit('update:payloadsDate', dateStr);
    }
  },
  watch: {
    selectedPayloads() {
      //hide histogram when change payloads-key
      this.showHistogram = false;
    },
    switchPayloadsDateRange() {
      // change x-axis and config flatpickr based on mode
      if (this.switchPayloadsDateRange) {
        this.xAxisValue = 'D MMM / HH:mm';
        this.$set(this.configPayloadsDate, 'mode', 'range');
      } else {
        this.xAxisValue = 'HH:mm';
        this.$set(this.configPayloadsDate, 'mode', 'single');
      }
    }
  }
};
</script>