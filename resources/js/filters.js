import Vue from 'vue';
import {
  parseISO,
  format,
  formatDistanceToNow,
  isSameDay
} from 'date-fns';

Vue.filter('formatDateSchedule', function (value) {
  // format date for Schedule Page
  // e.g. Today - 12:34
  if (!value) return '';
  value = parseISO(value);
  if (isSameDay(value, new Date)) {
    return `Today - ${format(value, 'HH:mm')}`;
  } else {
    return format(value, 'dd MMM yyyy - HH:mm');
  }
});

Vue.filter('formatDateISO', function (value) {
  // format date to ISO string
  // e.g. 2019-12-12T00:00:00.000+07:00
  if (!value) return '';
  // if value has'n been parsed, then parse to Date
  if (!(value instanceof Date)) {
    value = parseISO(value);
  }
  return format(value, 'yyyy-MM-dd\'T\'HH:mm:ss.SSSxxx');
});

Vue.filter('formatDateFromNow', function (value) {
  // format date to relative date
  // e.g. 2 days ago
  if (!value) return '';
  // if value has'n been parsed, then parse to Date
  if (!(value instanceof Date)) {
    value = parseISO(value);
  }
  return formatDistanceToNow(value, {
    addSuffix: true,
    includeSeconds: true
  });
});

Vue.filter('formatDate', function (value) {
  // format date to standard
  // e.g. 2 september 2019
  if (!value) return '';
  // if value has'n been parsed, then parse to Date
  if (!(value instanceof Date)) {
    value = parseISO(value);
  }
  return format(value, 'dd MMM yyyy');
});

Vue.filter('formatDateTime', function (value) {
  // format date to date and time
  // e.g. 2 september 2019 - 14:44
  if (!value) return '';
  // if value has'n been parsed, then parse to Date
  if (!(value instanceof Date)) {
    value = parseISO(value);
  }
  return format(value, 'dd MMM yyyy - HH:mm');
});

Vue.filter('formatTime', function (value) {
  // format date to date and time
  // e.g. 14:44:33
  if (!value) return '';
  // if value has'n been parsed, then parse to Date
  if (!(value instanceof Date)) {
    value = parseISO(value);
  }
  return format(value, 'HH:mm:ss');
});

Vue.filter('titleCase', function (value) {
  // format title key
  // e.g. 'title_case' to 'Title Case'
  if (!value) return '';
  let splitStr = value.toLowerCase().split('_');
  for (let i = 0; i < splitStr.length; i++) {
    splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
  }
  return splitStr.join(' ');
});

Vue.filter('capitalize', function (value) {
  // format text to capital
  // e.g. 'devices' to 'Devices'
  if (!value) return '';
  return value.charAt(0).toUpperCase() + value.slice(1);
});

Vue.filter('parsePayloadKey', function (value) {
  // payload key dictionary
  if (!value) return '';
  value = value.toString();
  switch (value) {
    case 'soil_dp':
      return { name: 'Soil Dielectric Permittivity', unit: '', icon: 'wi-flood' };
    case 'soil_wt':
      return { name: 'Soil Water Tension', unit: 'cbar', icon: 'wi-barometer' };
    case 'soil_wt1':
      return { name: 'Soil Water Tension 1', unit: 'cbar', icon: 'wi-barometer' };
    case 'soil_wt2':
      return { name: 'Soil Water Tension 2', unit: 'cbar', icon: 'wi-barometer' };
    case 'soil_vmc':
      return { name: 'Soil Volumetric Water Content', unit: '%', icon: 'wi-raindrop' };
    case 'soil_ec':
      return { name: 'Soil Electrical Conductivity', unit: 'dS/m', icon: 'wi-earthquake' };
    case 'soil_temp':
      return { name: 'Soil Temperature', unit: '°C', icon: 'wi-thermometer' };
    case 'solar_uv':
      return { name: 'Ultraviolet Sensor', unit: 'UVR', icon: 'wi-day-sunny' };
    case 'solar_par':
      return { name: 'Photosynthetically Active Radiation', unit: 'µmol m-2 s-1', icon: 'wi-hot' };
    case 'rtd_temp':
      return { name: 'RTD Temperature', unit: '°C', icon: 'wi-thermometer' };
    case 'rssi':
      return { name: 'Received Signal Strength Indicator', unit: 'dB' };
    case 'snr':
      return { name: 'Signal Noise Ratio', unit: 'dB' };
    case 'water_flow':
      return { name: 'Water Flow', unit: 'Liter', icon: 'wi-raindrops' };
    case 'water_vol':
      return { name: 'Water Volume', unit: 'Liter', icon: 'wi-raindrop' };
    case 'water_temp':
      return { name: 'Water Temperature', unit: '°C', icon: 'wi-thermometer' };
    case 'pH':
      return { name: 'PH', unit: '', icon: 'wi-thermometer-internal' };
    case 'redox':
      return { name: 'Redox', unit: '', icon: 'fa-flask' };
    case 'batt':
      return { name: 'Battery', unit: 'Volt', icon: 'fa-battery-full' };
    case 'air_hum':
      return { name: 'Air Humidity', unit: '%', icon: 'wi-humidity' };
    case 'air_temp':
      return { name: 'Air Temp', unit: '°C', icon: 'wi-thermometer' };
    case 'avg_wind_speed':
      return { name: 'Avg Wind Speed', unit: 'm/s', icon: 'wi-strong-wind' };
    case 'solar_rad':
      return { name: 'Solar Radiation', unit: 'W/m2', icon: 'wi-hot' };
    case 'sun_hour':
      return { name: 'Sun Hour', unit: 'Hours', icon: 'wi-day-sunny' };
    case 'supply_volt':
      return { name: 'Supply Volt', unit: 'Volt', icon: 'fa-bolt' };
    case 'read_retry':
      return { name: 'Read Retry', unit: '', icon: 'fa-repeat' };
    case 'wind_dir':
      return { name: 'Wind Direction', unit: '°', icon: 'wi-wind wi-towards-nne' };
    case 'wind_speed':
      return { name: 'Wind Speed', unit: 'm/s', icon: 'wi-strong-wind' };
    case 'frequency':
      return { name: 'Frequency', unit: 'MHz', icon: 'fa-bullseye' };
    case 'cvolt':
      return { name: 'Cvolt', unit: 'Volt', icon: 'fa-bolt' };
    case 'precipIntensity':
      return { name: 'Precipitation Intensity', unit: 'mm', icon: 'wi-sleet' };
    case 'precipTotal':
      return { name: 'Precipitation Total', unit: 'mm', icon: 'wi-sleet' };
    case 'sensor_status':
      return { name: 'Sensor Status', unit: '', icon: 'fa-cog' };
    case 'charge':
      return { name: 'Charge', unit: 'mA', icon: 'fa-plug' };
    case 'valve':
      return { name: 'Valve', unit: '', icon: 'fa-shower' };
    case 'sludge_level':
      return { name: 'Sludge Level', unit: 'mg/l', icon: 'wi-thermometer-internal' };
    case 'temp':
      return { name: 'Temperature', unit: '°C', icon: 'wi-thermometer' };
    case 'water_level':
      return { name: 'Water Level', unit: 'm', icon: 'wi-flood' };
    default:
      return {
        // generate default values
        name: (function () {
          let val = value.split('_').join(' ');
          return val.charAt(0).toUpperCase() + val.slice(1);
        })(),
        unit: '',
        icon: 'fa-star'
      };
  }
});