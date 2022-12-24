<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-12">
        <form class="row gx-3 gy-2 align-items-center">
          <div class="col-sm-3">
            <label class="visually-hidden" for="specificSizeInputName">First Name</label>
            <input id="first_name" v-model="form.first_name" type="text" class="form-control" name="first_name"
                   placeholder="First name">
          </div>
          <div class="col-sm-3">
            <label class="visually-hidden" for="specificSizeInputName">Last Name</label>
            <input id="last_name" type="text" v-model="form.last_name" class="form-control" name="last_name"
                   placeholder="Last Name">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary" form="" @click="searchForm()">Search</button>
          </div>
        </form>
      </div>
      <div class="col-md-12 mt-2">
        <div v-if="errors.length" class="alert-danger p-3 mb-2">
          <b>Please correct the following error(s):</b>
          <ul>
            <li v-for="error in errors">{{ error }}</li>
          </ul>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="table-responsive">
          <table class="table">
            <thead>
            <tr>
              <th scope="col">First Name</th>
              <th scope="col">Last Name</th>
              <th scope="col">Age</th>
              <th scope="col">Address</th>
              <th scope="col">Score</th>
            </tr>
            </thead>
            <tbody v-if="!loading">
            <tr v-for="person in persons">
              <td>{{ person.first_name }}</td>
              <td>{{ person.last_name }}</td>
              <td>{{ person.age }}</td>
              <td>{{ person.address }}</td>
              <td>{{ person.score }}</td>
            </tr>
            </tbody>
          </table>
        </div>
        <div v-if="loading">
          <img src="/images/loader.gif" alt="" class="m-auto">
        </div>
      </div>
      <div class="col-md-6">
        <div ref="chartContainer" style="height: 500px">
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'
import * as am5 from '@amcharts/amcharts5';
import * as am5xy from '@amcharts/amcharts5/xy';
import am5themes_Animated from '@amcharts/amcharts5/themes/Animated';

export default {
  layout: 'basic',

  metaInfo() {
    return {title: this.$t('home')}
  },
  mounted() {
    this.loadCharts();
  },
  data: () => ({
    title: window.config.appName,
    errors: [],
    form: {
      first_name: '',
      last_name: '',
    },
    persons: [],
    loading: false,
    chartData: []
  }),
  computed: mapGetters({
    authenticated: 'auth/check'
  }),
  beforeDestroy() {
    if (this.root) {
      this.root.dispose();
    }
  },
  methods: {
    async searchForm() {
      try {
        this.errors = []
        if (this.form.first_name === '') {
          this.errors.push('First name is required.')
        }
        if (this.form.last_name === '') {
          this.errors.push('Last name required.')
        }
        this.loading = true;
        const {data} = await axios.get('/api/people-search', {
          params: this.form
        });
        this.persons = data.data;
        this.loading = false;
        this.loadCharts();
      } catch (e) {
        this.loading = false;
        if (!axios.isCancel(e)) {
          console.error(e)
        }
      }
    },
    loadCharts() {
      if (this.persons.length === 0) {
        return
      }
      // data for charts
      let data = [];
      this.persons.forEach((item, index) => {
        data.push({category: item.full_name, value1: item.score, value2: item.age});
      });

      let root = am5.Root.new(this.$refs.chartContainer);

      root.setThemes([am5themes_Animated.new(root)]);

      let chart = root.container.children.push(
        am5xy.XYChart.new(root, {
          panY: false,
          layout: root.verticalLayout
        })
      );

      // Create Y-axis
      let yAxis = chart.yAxes.push(
        am5xy.ValueAxis.new(root, {
          renderer: am5xy.AxisRendererY.new(root, {})
        })
      );

      // Create X-Axis
      let xAxis = chart.xAxes.push(
        am5xy.CategoryAxis.new(root, {
          renderer: am5xy.AxisRendererX.new(root, {}),
          categoryField: "category"
        })
      );
      xAxis.data.setAll(data);

      // Create series
      let series1 = chart.series.push(
        am5xy.ColumnSeries.new(root, {
          name: "Series",
          xAxis: xAxis,
          yAxis: yAxis,
          valueYField: "value1",
          categoryXField: "category"
        })
      );
      series1.data.setAll(data);

      let series2 = chart.series.push(
        am5xy.ColumnSeries.new(root, {
          name: "Series",
          xAxis: xAxis,
          yAxis: yAxis,
          valueYField: "value2",
          categoryXField: "category"
        })
      );
      series2.data.setAll(data);

      // Add legend
      let legend = chart.children.push(am5.Legend.new(root, {}));
      legend.data.setAll(chart.series.values);

      // Add cursor
      chart.set("cursor", am5xy.XYCursor.new(root, {}));

      this.root = root;
    }
  }

}
</script>

<style scoped>

</style>
