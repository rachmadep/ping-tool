<template>
  <div class="wrapper">
    <b-row>
      <b-col>
        <!-- DEVICE INFORMATION -->
        <b-card-group deck>
          <!-- DEVICE DETAIL SECTION -->
          <b-card
            header-tag="header"
            footer-tag="footer"
          >
            <div slot="header">
              <div class="d-flex justify-content-between align-items-center">
                <span>
                  <i class="fa fa-microchip"></i>
                  <strong>&nbsp;Device detail</strong>
                </span>
                <span>
                  <b-button
                    v-if="$can('update device')"
                    id="btn-edit-devices"
                    v-b-modal.modal-edit-device
                    v-b-tooltip.hover
                    variant="secondary"
                    size="sm"
                    title="Edit device data"
                    @click="$root.$emit('bv::hide::tooltip')"
                  >
                    <i class="fa fa-cog"></i>
                  </b-button>
                </span>
              </div>
            </div>

            <div
              v-if="isBusyDeviceDetail"
              class="d-flex justify-content-center my-5"
            >
              <b-spinner label="Loading..."></b-spinner>
            </div>
            <template v-if="!isBusyDeviceDetail">
              <h4 class="mb-3">{{devicesDetail.name || devicesDetail.device_id}}</h4>
              <b-list-group class="mb-2">
                <b-list-group-item>
                  Device ID : {{devicesDetail.device_id || 'null'}}
                </b-list-group-item>
                <b-list-group-item>Timezone : {{devicesDetail.timezone || 'null'}}</b-list-group-item>
                <b-list-group-item>Type : {{this.$options.filters.titleCase(devicesDetail.type) || 'null'}}</b-list-group-item>
                <b-list-group-item>
                  <b-link
                    v-b-modal.modal-devices-info
                    v-b-tooltip.hover
                    title="See devices info"
                    size="md"
                    class="m-0 py-0"
                    @click="$root.$emit('bv::hide::tooltip')"
                  >
                    <i class="fa fa-exclamation-circle text-info">&nbsp;</i>More info
                  </b-link>
                </b-list-group-item>
              </b-list-group>
            </template>
          </b-card>
        <!-- DEVICE MAP SECTION -->
          <b-card no-body>
            <b-alert
              :show="mapLatLong[0] == 0 || mapLatLong[1] == 0"
              variant="warning"
              class="mb-0"
            >Latitude & Longitude not set</b-alert>
            <div class="map-wrapper d-flex justify-content-center align-items-center">
              <div v-if="isBusyPayloads">
                <b-spinner label="Loading..."></b-spinner>
              </div>
              <l-map
                v-if="!isBusyPayloads"
                ref="myMap"
                style="height: 100%; width: 100%"
                :zoom="mapZoom"
                :center="mapLatLong"
              >
                <l-tile-layer :url="mapUrl"></l-tile-layer>
                <l-marker :lat-lng="mapLatLong">
                  <l-popup>
                    <div class="font-weight-bold">{{devicesDetail.name || devicesDetail.device_id}}</div>
                    <div>Latitude : {{mapLatLong[0]}}</div>
                    <div>Longitude : {{mapLatLong[1]}}</div>
                  </l-popup>
                </l-marker>
              </l-map>
            </div>
          </b-card>
        <!-- DEVICE STATUS SECTION -->
          <b-col
            lg="3"
            class="p-0 m-0 mt-lg-0 mt-3"
          >
            <template v-if="!isBusyPayloads && Object.keys(deviceLatestPayload).length!=0">
            <!-- CONNECTION STATUS -->
              <b-card
                :no-body="true"
                class="mb-2"
              >
                <b-card-body class="p-3 clearfix ">
                  <i
                    class="fa fa-link p-3 font-2xl mr-3 float-left"
                    :class="[deviceConnection? 'bg-success' : 'bg-danger']"
                  ></i>
                  <div class="text-muted text-uppercase font-weight-bold font-xs">Connection</div>
                  <div
                    class="h5 mb-0 mt-2"
                    :class="[deviceConnection? 'text-success' : 'text-danger']"
                  >
                    <template v-if="deviceConnection">
                      <span class="badge badge-success">ON</span>
                    </template>
                    <template v-else>
                      <span class="badge badge-danger">OFF</span>
                    </template>
                  </div>
                </b-card-body>
                <em
                  v-if="deviceLatestPayload.hasOwnProperty('datetime')"
                  slot="footer"
                  title="Latest uplinks sent by device"
                  v-b-tooltip
                >{{deviceLatestPayload.datetime | formatDateFromNow}}</em>
                <em
                  v-else
                  slot="footer"
                  title="Latest uplinks sent by device"
                  v-b-tooltip
                >{{devicesUplinks[0].date+'T'+deviceLatestPayload.time | formatDateFromNow}}</em>
              </b-card>
            <!-- VALVE SWITCH -->
              <b-card
                v-if="deviceLatestPayload !== null && devicesDetail.type == 'actuator'"
                :no-body="true"
                class="mb-2"
              >
                <b-card-body class="p-3 clearfix">
                  <i
                    v-if="devicesDetail.is_busy"
                    class="fa p-3 font-2xl mr-3 float-left fa-circle-notch fa-spin"
                  ></i>
                  <i
                    v-else
                    class="fa p-3 font-2xl mr-3 float-left"
                    :class="[valveStatus == true? 'fa-toggle-on bg-success' : 'fa-toggle-off bg-danger']"
                  ></i>
                  <div class="text-muted text-uppercase font-weight-bold font-xs">Switch</div>
                  <div class="h5 text-primary mb-0 mt-2">
                    <template v-if="devicesDetail.is_busy">
                      <span class="badge badge-warning">BUSY</span>
                    </template>
                    <toggle-button
                      v-else
                      :value="valveStatus"
                      :color="{checked: '#4DBD74', unchecked: '#C8CED3', disabled: '#eee'}"
                      :sync="true"
                      :labels="{checked: 'ON', unchecked: 'OFF'}"
                      :width="70"
                      :height="30"
                      :font-size="15"
                      :margin="3"
                      :disabled="$can('create commands') == false"
                      @change="sendCommands()"
                    />
                  </div>
                  <hr v-if="$can('create commands')">
                  <b-button
                    id="btn-device-commands"
                    v-b-modal.modal-device-commands
                    v-b-tooltip.hover
                    variant="primary"
                    v-if="$can('create commands')"
                    title="Attach device commands"
                    @click="$root.$emit('bv::hide::tooltip')"
                  >
                    <i class="fa fa-cog"></i> Device Commands
                  </b-button>
                </b-card-body>
              </b-card>
            <!-- LATEST PAYLOAD -->
              <b-card
                v-if="deviceLatestPayload !== null && devicesDetail.type !== 'actuator'"
                :no-body="true"
                class="mb-2"
              >
                <b-card-body class="p-3 clearfix">
                  <div class="text-muted text-uppercase font-weight-bold font-xs">Latest Payload</div>
                  <div class="text-dark mb-0 mt-2">
                    <template v-if="Object.keys(deviceLatestPayloadFiltered).length != 0">
                      <div
                        v-for="(item, key) in deviceLatestPayloadFiltered"
                        :key="key"
                      >
                        <span class="font-weight-bold">{{key | parsePayload}}</span>&nbsp;
                        <i class="fa fa-angle-double-right"></i>
                        {{item}}
                      </div>
                    </template>
                    <template v-else>
                      <div>
                        No Payload
                      </div>
                    </template>
                  </div>
                </b-card-body>
              </b-card>
            <!-- END LATEST PAYLOAD -->
            </template>
          </b-col>
        <!-- END DEVICE STATUS SECTION -->
        </b-card-group>
      </b-col>
    </b-row>
    <hr />
  <!-- DEVICE UPLINKS SECTION -->
      <b-alert v-if="switchPayloadsDateRange" variant="warning" show >
        <div class="d-md-flex justify-content-between align-items-center">
          <div class="my-2">
            You're in date range mode.
          </div>
          <b-button
            variant="danger"
            size="sm"
            @click="changeMode = 'single'; switchPayloadsDateRange = false"
          >Switch to single date mode</b-button>
        </div>
      </b-alert>
      <b-row>
      <!-- DEVICE UPLINKS -->
        <b-col md="4">
          <b-card
            no-body
            header-tag="header"
            footer-tag="footer"
          >
            <div slot="header">
              <div class="d-flex justify-content-between align-items-center">
                <span>
                  <i class="fa fa-calendar"></i>
                  <strong>&nbsp;Uplinks date</strong>
                </span>
                <b-dropdown
                  size="sm"
                  variant="secondary"
                  class="my-0 py-0"
                >
                  <template slot="button-content">Options</template>
                  <b-dropdown-header>Date mode</b-dropdown-header>
                  <b-dropdown-item-button
                    @click="changeMode = 'single'; switchPayloadsDateRange = false"
                    :active="changeMode == 'single'"
                  >Single</b-dropdown-item-button>
                  <b-dropdown-item-button
                    @click="changeMode = 'range'; payloadsDateRange? switchPayloadsDateRange = true : ''"
                    :active="changeMode == 'range'"
                  >Range</b-dropdown-item-button>
                </b-dropdown>
              </div>
            </div>
            <b-alert
              :show="devicesUplinks.length==0"
              variant="danger"
              class="p-3 m-3"
            >
              No uplinks data
            </b-alert>
            <div v-show="changeMode == 'single'">
              <div
                class="d-flex justify-content-center my-5"
                v-if="isBusyUplinks"
              >
                <b-spinner label="Loading..."></b-spinner>
              </div>
              <template v-if="!isBusyUplinks && devicesUplinks.length!=0">
              <!-- PAGINATION AND LIST UPLINKS -->
                <div class="px-3 pt-3 mb-0 pb-0">
                  <b-pagination
                    v-show="uplinksMeta.total > 10"
                    v-model="currentPageUplinks"
                    limit="3"
                    align="fill"
                    size="md"
                    first-text="First"
                    last-text="Last"
                    hide-goto-end-buttons
                    :total-rows="uplinksMeta.total"
                    :per-page="uplinksMeta.per_page"
                  ></b-pagination>
                  <span class="d-flex justify-content-end">Page {{uplinksMeta.current_page}} of {{uplinksMeta.last_page}}</span>
                </div>
                <!-- dynamic height based on uplinks date count -->
                <div
                  class="overflow-auto px-3 pb-3 mt-3"
                  :class="[uplinksMeta.total > 10 ? 'height-overflow' : 'height-default']"
                >
                  <b-list-group ref="uplinks-data">
                    <b-list-group-item
                      v-for="(item, index) in devicesUplinks"
                      :key="item.date"
                      href="#"
                      class="flex-column align-items-start"
                      :active="item.date === payloadsDate"
                      @click="payloadsDate = item.date"
                    >
                      <div class="d-flex justify-content-between">
                        <h5 class="mb-1">{{item.date | formatDate}}</h5>
                        <small v-if="index==0 && item.date == dateNow ">Today</small>
                        <small v-else>{{item.date | formatDateFromNow}}</small>
                      </div>
                      <p class="mb-1">Payloads Count : {{item.payloads_count}}</p>
                      <small class="badge badge-warning">Port : {{item.port}}</small>
                    </b-list-group-item>
                  </b-list-group>
                </div>
              </template>
            </div>
            <div v-show="changeMode == 'range'">
              <div
                v-if="!isBusyUplinks && devicesUplinks.length!=0"
                class="m-3"
              >
                <h4>
                  Date Range Mode
                </h4>
                <small class="text-muted">View payload in multiple date. Select start date & end date on calendar</small>
                <div class="my-2">
                  <b-input-group id="payloadsDateRange">
                    <flat-pickr
                      name="date"
                      v-model="payloadsDateRange"
                      :config="configPayloadsDateRange"
                      class="form-control"
                      placeholder="Select date range"
                      @on-change="onChangeDateRange"
                    ></flat-pickr>
                    <b-input-group-append>
                      <b-button
                        variant="primary"
                        type="button"
                        title="Toggle"
                        data-toggle
                      >
                        <i class="fa fa-calendar">
                          <span
                            aria-hidden="true"
                            class="sr-only"
                          >Toggle</span>
                        </i>
                      </b-button>
                    </b-input-group-append>
                  </b-input-group>
                  <span
                    class="small text-danger"
                    v-if="!showSwitchDateRange"
                  >Select two dates to fetch data</span>
                </div>
              </div>
            </div>
            <div
              v-if="changeMode == 'single'"
              slot="footer"
            >{{uplinksMeta.total || 'null'}} uplinks</div>
          </b-card>
        </b-col>

      <!-- DEVICE PAYLOADS -->
        <b-col md="8">
          <b-card
            v-if="(changeMode == 'single') || (changeMode == 'range' && devicesPayloadsDateRange.length != 0)"
            header-tag="header"
            footer-tag="footer"
          >
            <div slot="header">
              <div class="d-flex justify-content-between">
                <span class="font-weight-bold">
                  <i class="fa fa-database"></i>&nbsp;
                  <span v-if="!switchPayloadsDateRange">Payloads ({{payloadsDate | formatDate}})</span>
                  <span v-else-if="!payloadsDateRangeProcessed.endDate">Payloads ({{payloadsDateRangeProcessed.startDate | formatDate}})</span>
                  <span v-else>Payloads ({{payloadsDateRangeProcessed.startDate | formatDate}} - {{payloadsDateRangeProcessed.endDate | formatDate}})</span>
                </span>
                <template v-if="!isBusyUplinks">
                  <span></span>
                  <span v-if="payloadsDate == dateNow && !switchPayloadsDateRange">
                    <i
                      class="fa fa-refresh fa-spin"
                      title="Auto Refresh Payload"
                    ></i>
                  </span>
                  <span
                    v-else-if="!switchPayloadsDateRange"
                    class="text-muted"
                  >{{payloadsDate | formatDateFromNow}}</span>
                </template>
              </div>
            </div>
            <b-alert
              :show="devicesPayloads.length==0"
              variant="danger"
            >
              No payloads data
            </b-alert>
            <template v-if="devicesPayloads.length !=0">
              <b-row class="mb-3">
                <b-col sm="9">
                  <b-button
                    v-b-modal.modal-charts-payloads
                    size="sm"
                    variant="success"
                    title="View charts based on uplinks data"
                    class="mr-2"
                  >
                    <i class="fa fa-line-chart"></i> View Charts
                  </b-button>
                  <b-button
                    v-if="(switchPayloadsDateRange == false && payloadsMeta.current_page != payloadsMeta.last_page) || (switchPayloadsDateRange == true && payloadsMetaDateRange.current_page != payloadsMetaDateRange.last_page)"
                    variant="dark"
                    size="sm"
                    :disabled="isBusyAppendPayloads"
                    @click="loadAllPayloadsToExport()"
                  >
                    <i
                      class="fa fa-refresh"
                      :class="{'fa-spin' : isBusyAppendPayloads}"
                    ></i>
                    <span>&nbsp;Load to export&nbsp;</span>
                    <span v-if="!switchPayloadsDateRange">{{payloadsMeta.current_page}} / {{payloadsMeta.last_page}}</span>
                    <span v-if="switchPayloadsDateRange">{{payloadsMetaDateRange.current_page}} / {{payloadsMetaDateRange.last_page}}</span>
                  </b-button>
                  <span
                    v-else
                    title="Export data from the table below"
                    v-b-tooltip
                  >
                    <download-excel
                      :data="exportJsonData"
                      :name="exportName"
                      class="btn btn-dark btn-sm mr-2"
                    >
                      <i class="fa fa-file-excel-o"></i>
                      Export Data
                    </download-excel>
                  </span>
                </b-col>
                <b-col sm="3">
                  <b-form-select
                    size="sm"
                    v-model="perPagePayloads"
                    :options="pageOptions"
                    v-b-tooltip.hover
                    title="Payloads data per page"
                    class="mt-2 mt-sm-0"
                  ></b-form-select>
                </b-col>
              </b-row>
              <b-table
                id="table-transition-example"
                primary-key="time"
                :tbody-transition-props="transProps"
                :busy="isBusyPayloads"
                bordered
                striped
                hover
                responsive
                show-empty
                empty-text="There are no payloads to show"
                :fields="fieldsPayloads"
                :items="devicesPayloadProcessed"
                :current-page="currentPagePayloads"
                :per-page="perPagePayloads"
              >
                <div slot="table-busy">
                  <loading-table></loading-table>
                </div>

                <template
                  slot="date"
                  slot-scope="row"
                >{{row.item.date | formatDate}}</template>
              </b-table>
              <b-row>
                <b-col>
                  <b-pagination
                    limit="5"
                    align="fill"
                    size="md"
                    v-model="currentPagePayloads"
                    :total-rows="devicesPayloadProcessed.length"
                    :per-page="perPagePayloads"
                    class="mt-2"
                  ></b-pagination>
                </b-col>
              </b-row>
              <!-- load more button for single day payload -->
              <b-button
                v-if="switchPayloadsDateRange == false && payloadsMeta.current_page != payloadsMeta.last_page"
                size="sm"
                block
                variant="secondary"
                :disabled="isBusyAppendPayloads"
                @click.prevent="appendPayloads(payloadsDate,payloadsMeta.current_page+1)"
              >
                <i
                  class="fa fa-refresh"
                  :class="{'fa-spin' : isBusyAppendPayloads}"
                ></i>
                Load more {{payloadsMeta.current_page}} / {{payloadsMeta.last_page}}
              </b-button>
              <!-- load more button for date range payload -->
              <b-button
                v-if="switchPayloadsDateRange == true && payloadsMetaDateRange.current_page != payloadsMetaDateRange.last_page"
                size="sm"
                block
                variant="secondary"
                :disabled="isBusyAppendPayloads"
                @click.prevent="appendPayloadsDateRange(payloadsDateRangeProcessed,payloadsMetaDateRange.current_page+1)"
              >
                <i
                  class="fa fa-refresh"
                  :class="{'fa-spin' : isBusyAppendPayloads}"
                ></i>
                Load more {{payloadsMetaDateRange.current_page}} / {{payloadsMetaDateRange.last_page}}
              </b-button>
            </template>
            <div slot="footer">
              <template v-if="!switchPayloadsDateRange">
                <animated-number :number="payloadsMeta.to"></animated-number>
                from {{payloadsMeta.total}} payloads
              </template>
              <template v-else>
                {{devicesPayloadsDateRange.length}} payloads
              </template>
            </div>
          </b-card>
          <b-card v-else>
            <b-alert
              show
              variant="danger"
              class="mb-0"
            >Select date on the uplinks panel to fetch payloads</b-alert>
          </b-card>
        </b-col>
      </b-row>

  <!-- ENDDEVICE UPLINKS SECTION -->
  <!-- MODAL COMPONENT -->
    <device-info :devices-detail="devicesDetail"></device-info>
    <device-edit
      :initial-device="devicesDetail"
      :device-types="deviceTypes"
      @finish-update="getDevicesDetail"
    ></device-edit>
    <device-command
      :device="devicesDetail"
      @finish-update="getDevicesDetail"
    ></device-command>
    <device-charts
      :is-busy-payloads="isBusyPayloads"
      :is-busy-append-payloads="isBusyAppendPayloads"
      :fields-payloads="fieldsPayloads"
      :payloads-meta="payloadsMeta"
      :payloads-meta-date-range="payloadsMetaDateRange"
      :devices-payload-processed="devicesPayloadProcessed"
      :switch-payloads-date-range.sync="switchPayloadsDateRange"
      :payloads-date.sync="payloadsDate"
      :payloads-date-range.sync="payloadsDateRange"
      @append-payloads="appendPayloads(payloadsDate,payloadsMeta.current_page+1)"
      @append-payloads-date-range="appendPayloadsDateRange(payloadsDateRangeProcessed,payloadsMetaDateRange.current_page+1)"
    ></device-charts>
  <!-- END MODAL COMPONENT -->
  </div>
</template>

<script>
import AnimatedNumber from '../AnimatedNumber';
import { format, differenceInMinutes } from 'date-fns';
import FlatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';

import DeviceInfo from './DeviceInfo';
import DeviceEdit from './DeviceEdit';
import DeviceCommand from './DeviceCommand';
import DeviceCharts from './DeviceCharts';

export default {
  name: 'DevicesDetail',
  components: { 
    FlatPickr, 
    AnimatedNumber, 
    DeviceInfo, 
    DeviceEdit,
    DeviceCommand,
    DeviceCharts 
    },
  props: {
    deviceId: String,
    deviceTypes: Array
  },
  data() {
    return {
      devicesDetail: {},
      devicesUplinks: [],
      allUplinksDate: [],
      devicesPayloads: [],
      devicesPayloadsDateRange: [],
      deviceLatestPayload: {},
      payloadsDate: '',
      uplinksMeta: '',
      payloadsMeta: {},
      payloadsMetaDateRange: {},
      isBusyPayloads: false,
      isBusyDeviceDetail: false,
      isBusyUplinks: false,
      isBusyAppendPayloads: false,
      transProps: {
        name: 'flip-list'
      },
      currentPageUplinks: 1,
      preventAccess: false,
      payloadsDateRange: null,
      switchPayloadsDateRange: false,
      currentPagePayloads: 1,
      perPagePayloads: 50,
      pageOptions: [50, 100, 150],
      changeMode: 'single',
      mapUrl: 'http://{s}.tile.osm.org/{z}/{x}/{y}.png',
      mapZoom: 15
    };
  },
  computed: {
    mapLatLong() {
      if (this.devicesDetail.latitude && this.devicesDetail.longitude) {
        return [this.devicesDetail.latitude, this.devicesDetail.longitude];
      } else {
        return [0, 0];
      }
    },
    deviceConnection() {
      var diffInMinutes;
      if (this.deviceLatestPayload.hasOwnProperty('datetime')) {
        diffInMinutes = differenceInMinutes(new Date(), this.deviceLatestPayload.datetime);
      } else {
        diffInMinutes = differenceInMinutes(new Date(), this.devicesUplinks[0].date + 'T' + this.deviceLatestPayload.time);
      }
      //if latest payload more than 60 minutes from now
      //asuumed as offline
      if (diffInMinutes > 60) {
        return false;
      } else {
        return true;
      }
    },
    valveStatus() {
      // copy valve value from latest payload
      let payload = this.deviceLatestPayload;
      if (payload.hasOwnProperty('valve')) {
        if (payload.valve == 1) {
          return true;
        } else {
          return false;
        }
      } else {
        return null;
      }
    },
    deviceLatestPayloadFiltered() {
      //return object which is NOT included in list below
      const commonKeys = ['rssi', 'batt', 'counter', 'charge', 'frequency', 'datetime', 'time', 'cvolt'];

      return Object.keys(this.deviceLatestPayload)
        .filter(key => !commonKeys.includes(key))
        .reduce((obj, key) => {
          obj[key] = this.deviceLatestPayload[key];
          return obj;
        }, {});
    },
    dateNow() {
      // check if today
      return format(new Date(), 'YYYY-MM-DD');
    },
    configPayloadsDateRange() {
      return {
        mode: 'range',
        wrap: true, // set wrap to true only when using 'input-group'
        altFormat: 'j M Y',
        altInput: true,
        maxDate: 'today',
        enable: this.allUplinksDate
      };
    },
    showSwitchDateRange() {
      // to change switch status
      if (this.payloadsDateRangeProcessed != null || this.switchPayloadsDateRange == true) {
        if (this.payloadsDateRangeProcessed.startDate != undefined && this.payloadsDateRangeProcessed.endDate != undefined) {
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    },
    payloadsDateRangeProcessed() {
      // get start date and end date from flatpickr string
      if (this.payloadsDateRange !== null && this.payloadsDateRange != '') {
        let arrRangeDate = this.payloadsDateRange.split(' to ');
        return {
          startDate: arrRangeDate[0],
          endDate: arrRangeDate[1]
        };
      } else {
        return null;
      }
    },
    devicesPayloadProcessed() {
      // convert soil_vmc float to fixed 2 digit decimal
      // final payloads data
      let payloadsArr = [];
      if (this.switchPayloadsDateRange) {
        payloadsArr = this.devicesPayloadsDateRange;
      } else {
        payloadsArr = this.devicesPayloads;
      }
      return payloadsArr.map(item => {
        // remove datetime from object
        const { datetime, ...arr } = item;
        // fix soil_mvc values
        if (arr['soil_vmc'] != undefined) arr['soil_vmc'] = Number(arr['soil_vmc'].toFixed(2));
        if (arr['solar_par'] != undefined) arr['solar_par'] = Number(arr['solar_par'].toFixed(2));
        return arr;
      });
    },

    fieldsPayloads() {
      // create array payloads and reorder
      if (this.devicesPayloadProcessed.length != 0) {
        let arr = Object.keys(this.devicesPayloadProcessed[0]);
        // time-date always first
        if (arr.includes('date')) {
          // date only in date range mode
          arr.splice(0, 0, arr.splice(arr.findIndex(val => val == 'date'), 1)[0]);
          arr.splice(1, 0, arr.splice(arr.findIndex(val => val == 'time'), 1)[0]);
        } else {
          arr.splice(0, 0, arr.splice(arr.findIndex(val => val == 'time'), 1)[0]);
        }
        if (arr.includes('valve')) {
          //valve available in valve
          arr.splice(2, 0, arr.splice(arr.findIndex(val => val == 'valve'), 1)[0]);
        }
        //the last table
        arr.splice(arr.length - 1, 0, arr.splice(arr.findIndex(val => val == 'batt'), 1)[0]);
        arr.splice(arr.length - 2, 0, arr.splice(arr.findIndex(val => val == 'frequency'), 1)[0]);
        arr.splice(arr.length - 3, 0, arr.splice(arr.findIndex(val => val == 'rssi'), 1)[0]);
        arr.splice(arr.length - 4, 0, arr.splice(arr.findIndex(val => val == 'counter'), 1)[0]);
        if (arr.includes('charge')) {
          // charge not available in valve
          arr.splice(arr.length - 5, 0, arr.splice(arr.findIndex(val => val == 'charge'), 1)[0]);
        }
        return arr;
      } else {
        return [];
      }
    },
    exportJsonData() {
      // export data to excel
      let payloadsArr = [];
      if (this.switchPayloadsDateRange) {
        payloadsArr = this.devicesPayloadsDateRange;
      } else {
        payloadsArr = this.devicesPayloads;
      }
      return payloadsArr.map(item => {
        // remove date & time from object
        const { date, time, ...arr } = item;
        arr.datetime = format(arr.datetime, 'YYYY/MM/DD HH:mm');
        if (arr['soil_vmc'] != undefined) arr['soil_vmc'] = Number(arr['soil_vmc'].toFixed(2));
        if (arr['solar_par'] != undefined) arr['solar_par'] = Number(arr['solar_par'].toFixed(2));
        return arr;
      });
    },
    exportName() {
      if (this.switchPayloadsDateRange) {
        return `${this.devicesDetail.device_id} (${this.payloadsDateRangeProcessed.startDate} - ${this.payloadsDateRangeProcessed.endDate})`;
      } else {
        return `${this.devicesDetail.device_id} (${this.payloadsDate})`;
      }
    }
  },

  methods: {
    async getDevicesDetail() {
      this.isBusyDeviceDetail = true;
          // get devices detail for admin
      try {
        const getDevicesDetail = await axios.get(`/api/devices/${this.deviceId}`);
        this.devicesDetail = getDevicesDetail.data.data;
        this.isBusyDeviceDetail = false;
        return true;
      } catch (error) {
        console.log(error)
        return false;
      }
    },
    async sendCommands() {
      // default 'downlink' = object 'commands'
      // if downlink == switch, get command from switch - single command at once
      let commandId = null;
      let commandText = null;
      if (this.valveStatus == false) {
        commandId = 2;
        commandText = 'Turn On';
      } else {
        commandId = 1;
        commandText = 'Turn Off';
      }
      let downlink = [{
        'command_id': commandId,
        'device_id': this.devicesDetail.id
      }];
      // send commands to API
      try {
        // const scheduleCommands = await Axios.scheduleCommands({ 'commands': downlink });
        const scheduleCommands = await axios.post('/api/commands_schedules', { 'commands': downlink });
        if (scheduleCommands.data.status == 'accepted') {
          // firebase.addLog({
          //   desc: `<span class="badge badge-info">Send Command</span> - Send commands ${commandId == 2 ? '<span class="text-success">' + commandText + '</span>' : '<span class="text-danger">' + commandText + '</span>'} to ${this.devicesDetail.device_id}`
          // });
          this.$toast.success({ title: "Command accepted", message: `${commandText} ${this.devicesDetail.device_id}` });
        }
      } catch (error) {
        this.$toast.error({ title: "Failed send command", message: "Something went wrong. Please try again." });
      }
    },
    async getDevicesUplinks() {
      //get all uplinks date
      try {
        this.isBusyUplinks = true;
        const getDevicesUplinks = await axios.get(`/api/uplinks`, {
      		params: {
            device_id: this.devicesDetail.device_id,
            page: this.currentPageUplinks
      		}
        });
        this.isBusyUplinks = false;
        this.devicesUplinks = getDevicesUplinks.data.data;
        this.uplinksMeta = getDevicesUplinks.data.meta;
        if (this.devicesUplinks.length == 0) {
          return false;
        }
        return true;
      } catch (error) {
        this.$toast.error({
          title: "Whoops!",
          message: "Failed fetch device uplink"
        });
        return false;
      }
    },
    async getUplinksDate() {
      //get all uplinks date
      let page = 1;
      let arrUplinks = [];
      var maxPage = 1;
      do {
        try {
          
          const getDevicesUplinks = await axios.get(`/api/uplinks`, {
            params: {
              device_id: this.devicesDetail.device_id,
              page: this.page
            }
          });
          arrUplinks.push(getDevicesUplinks.data.data);
          maxPage = getDevicesUplinks.data.meta.last_page;
        } catch (error) {
          if (error.response.status == 404) {
            this.$toast.error({title: "Whoops!", message: "Device uplinks not found"});
          } else {
            this.$toast.error({title: "Whoops!", message: "Failed fetch device uplinks date"});
          }
        }
        page++;
      }
      while (page <= maxPage);

      this.allUplinksDate = arrUplinks
        // convert nested array to one-level array
        .reduce((a, b) => a.concat(b), [])
        // return only date
        .map(item => item.date);
    },

    async getPayload(date, page = 1) {
      // set loading on uplinks component when payloads is loading
      let loader = this.$loading.show({
        container: this.$refs['uplinks-data'],
        zIndex: 999,
        loader: 'bars'
      });
      // get first page payload data
      try {
        this.isBusyPayloads = true;
        const getPayload = await axios.get(`/api/uplinks/${this.devicesDetail.device_id}/payloads/${date}`, {
      		params: {
      			page: page
      		}
        })
        this.devicesPayloads = getPayload.data.data;
        this.payloadsMeta = getPayload.data.meta;
        this.payloadsDate = date;
        // copy to latest payload var when selected date = latest date
        // newest payload is used to store valve status
        if (date == this.devicesUplinks[0].date && this.currentPageUplinks == 1) {
          this.deviceLatestPayload = (getPayload.data.data)[0];
        }
      } catch (error) {
        console.log(error)
        this.$toast.error({
          title: "Whoops!",
          message: "Failed fetch device payloads"
        });
      }
      this.isBusyPayloads = false;
      loader.hide();
    },
    onChangeDateRange(selectedDates, dateStr) {
      // selectedDates is full date format
      // dateStr is YYYY-MM-DD format
      if (dateStr != '') {
        this.changeMode = 'range';
      }
      this.payloadsDateRange = dateStr;
      if (selectedDates !== null && selectedDates != '') {
        if (selectedDates[0] != undefined && selectedDates[1] != undefined) {
          // format selected to YYYY-MM-DD
          let startSelectedDates = format(selectedDates[0], 'YYYY-MM-DD');
          let endSelectedDates = format(selectedDates[1], 'YYYY-MM-DD');
          // fetch data
          this.getPayloadsDateRange(startSelectedDates, endSelectedDates);
          this.switchPayloadsDateRange = true;
        } else {
          this.$toast.error({title: "Error parameter", message: "Please select start and end date to use date range mode"});
        }
      }
    },
    async getPayloadsDateRange(startDate, endDate, page = 1) {
      //get first page payload data for date range
      try {
        this.isBusyPayloads = true;
        const getPayloadsDateRange = await axios.get('api/uplinks', {
          params: {
            device_id: this.devicesDetail.device_id,
            start_date: startDate,
            end_date: endDate,
            page: page,
            load_payloads: 1
          }
        })
        if (getPayloadsDateRange.data.data.length == 0) {
          //if no data on payloadsDateRange, switch to single
          this.$toast.error({title: "Error parameter", message: "Payloads on selected date not found"});
          this.switchPayloadsDateRange = false;
          this.isBusyPayloads = false;
          this.payloadsDateRange = null;
          return;
        }
        // parse payload from API
        let arrPayloadsDateRange = getPayloadsDateRange.data.data.map(val => {
          val.payloads.map(payload => {
            payload.date = val.date;
            return payload;
          });
          return val.payloads.reverse();
        });
        //convert nested array to one-level array
        this.devicesPayloadsDateRange = arrPayloadsDateRange.reduce((a, b) => a.concat(b), []);
        this.payloadsMetaDateRange = getPayloadsDateRange.data.meta;
        this.isBusyPayloads = false;
      } catch (error) {
        this.$toast.error({title: "Payload Date Range", message: "Failed fetch data"});
      }
    },
    onChangeDateRange(selectedDates, dateStr) {
      // selectedDates is full date format
      // dateStr is YYYY-MM-DD format
      if (dateStr != '') {
        this.changeMode = 'range';
      }
      this.payloadsDateRange = dateStr;
      if (selectedDates !== null && selectedDates != '') {
        if (selectedDates[0] != undefined && selectedDates[1] != undefined) {
          // format selected to YYYY-MM-DD
          let startSelectedDates = format(selectedDates[0], 'YYYY-MM-DD');
          let endSelectedDates = format(selectedDates[1], 'YYYY-MM-DD');
          // fetch data
          this.getPayloadsDateRange(startSelectedDates, endSelectedDates);
          this.switchPayloadsDateRange = true;
        } else {
          this.$toast.error({title: "Failed Change Date Range", message: "Please select start and end date to use date range mode"});
        }
      }
    },
    
    async appendPayloads(date, page = 2) {
      //fetch payload from page 2 and append to the first payload page
      try {
        this.isBusyAppendPayloads = true;
        // const appendPayloads = await Axios.getPayload(this.devicesDetail.device_id, date, page);
        const appendPayloads = await axios.get(`/api/uplinks/${this.devicesDetail.device_id}/payloads/${date}`, {
      		params: {
      			page: page
      		}
        })
        this.devicesPayloads = this.devicesPayloads.concat(appendPayloads.data.data);
        this.payloadsMeta = appendPayloads.data.meta;
        this.isBusyAppendPayloads = false;
      } catch (error) {
        this.$toast.error({ title: "Whoops!", message: "Failed fetch more payloads" });
      }
    },
    async appendPayloadsDateRange(date, page = 2) {
      //fetch payload from page 2 and append to the first payload page
      try {
        this.isBusyAppendPayloads = true;
        // const appendPayloadsDateRange = await Axios.getPayloadsDateRange(this.devicesDetail.device_id, date.startDate, date.endDate, page);
        const appendPayloadsDateRange = await axios.get('api/uplinks', {
          params: {
            device_id: this.devicesDetail.device_id,
            start_date: date.startDate,
            end_date: date.endDate,
            page: page,
            load_payloads: 1
          }
        })
        if (appendPayloadsDateRange.data.data.length == 0) {
          this.$toast.error({ title: "Error parameter", message: "Payloads on selected date not found" });
          this.switchPayloadsDateRange = false;
          this.isBusyAppendPayloads = false;
          return;
        }
        // parse payload from API
        let arrPayloadsDateRange = appendPayloadsDateRange.data.data.map(val => {
          val.payloads.map(payload => {
            payload.date = val.date;
            return payload;
          });
          return val.payloads.reverse();
        });
        //convert nested array to one-level array
        //then concat them to prior payloads
        this.devicesPayloadsDateRange = this.devicesPayloadsDateRange.concat(arrPayloadsDateRange.reduce((a, b) => a.concat(b), []));
        this.payloadsMetaDateRange = appendPayloadsDateRange.data.meta;
        this.isBusyAppendPayloads = false;
      } catch (error) {
        this.$toast.error({ title: "Whoops!", message: "Failed fetch data" });
      }
    },

    async loadAllPayloadsToExport() {
      // get all 'load more'
      if (this.switchPayloadsDateRange == true) {
        do {
          await this.appendPayloadsDateRange(this.payloadsDateRangeProcessed, this.payloadsMetaDateRange.current_page + 1);
        }
        while (this.payloadsMetaDateRange.current_page < this.payloadsMetaDateRange.last_page);
      } else {
        do {
          await this.appendPayloads(this.payloadsDate, this.payloadsMeta.current_page + 1);
        }
        while (this.payloadsMeta.current_page < this.payloadsMeta.last_page);
      }
    },
    subscribeEcho() {
      if (this.devicesDetail) {
        Echo.channel(`device.${this.devicesDetail.device_id}`)
          //listen new payloads
          .listen('UplinkReceived', data => {
            //update latest device payload for valve status (object)
            console.log(data)
            this.deviceLatestPayload = data.payload;
            //if on first uplinks page
            //increment payloads_count on latest uplink
            if (this.currentPageUplinks == 1) {
              let count = this.devicesUplinks.find(item => item.date === data.date);
              count.payloads_count++;
            }
            //if payloads table opened
            //update device payload for data table
            if (this.payloadsDate === data.date) {
              this.devicesPayloads.unshift(data.payload);
            }
          })
          //listen busy state
          .listen('DeviceIsBusy', data => {
            this.devicesDetail.is_busy = data.is_busy;
          });
      }
    },
    
  },
  watch: {
    currentPageUplinks() {
      //request uplink if pagiantion change
      this.getDevicesUplinks();
    },
    payloadsDate() {
      // fetch payload when payloads date changed
      if (this.payloadsDate != null) {
        this.getPayload(this.payloadsDate);
        this.switchPayloadsDateRange = false;
      }
    },
    switchPayloadsDateRange() {
      //change mode depend on switch mode
      if (this.switchPayloadsDateRange == true) {
        this.changeMode = 'range';
      } else {
        this.changeMode = 'single';
      }
    }
  },
  async created() {
    let first = await this.getDevicesDetail();
    if (first) {
      let second = await this.getDevicesUplinks();
      this.getUplinksDate();
      if (second) {
        //fetch latest uplinks payload on first load
        await this.getPayload(this.devicesUplinks[0].date);
        this.subscribeEcho();
      }
    }
  },
  mounted() {
    // this.$nextTick(() => {
    //   // this.$refs.myMap.mapObject.ANY_LEAFLET_MAP_METHOD();
    // });
  },

  async beforeRouteUpdate(to, from, next) {
    //dynamic route matching
    //update id, then reload data using new id
    this.isBusyPayloads = true;
    this.isBusyDeviceDetail = true;
    this.isBusyUplinks = true;
    next();
    let first = await this.getDevicesDetail();
    if (first) {
      let second = await this.getDevicesUplinks();
      this.getUplinksDate();
      if (second) {
        //fetch latest uplinks payload on first load
        await this.getPayload(this.devicesUplinks[0].date);
        this.subscribeEcho();
      }
    }
  },
  
  destroyed() {
    // leave channel when leave page
    Echo.leaveChannel(`device.${this.devicesDetail.device_id}`);
  }
};
</script>

<style scoped>
.height-default {
  height: auto;
}
.height-overflow {
  height: 500px;
}
.map-wrapper {
  height: 100%;
  width: 100%;
}
@media (max-width: 575.98px) {
  .map-wrapper {
    height: 300px;
    width: 100%;
  }
}
</style>

