import { Icon } from '@iconify/vue';
import '../sass/app.scss';

import { createApp } from 'vue/dist/vue.esm-bundler';
import Antd from 'ant-design-vue';
import mixins from './mixin';
// vue echarts
import VueECharts from 'vue-echarts'

const app = createApp({});

app.mixin(mixins);

const files =  import.meta.glob('./src/**/*.vue', {eager: true});

for(const path in files){
  app.component(path.split('/').pop().split('.')[0], files[path].default);
}

app.component('Icon', Icon);
app.component('v-chart', VueECharts);

app.use(Antd);

app.mount('#vue-app');