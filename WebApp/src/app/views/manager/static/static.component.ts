import { Component, OnInit } from '@angular/core';
import { Chart } from 'angular-highcharts';

@Component({
  selector: 'app-static',
  templateUrl: './static.component.html',
  styleUrls: ['./static.component.css']
})
export class StaticComponent implements OnInit {

  public chart;
  constructor() { }

  ngOnInit() {
    this.initChart();
  }

  initChart() {
    let chart = new Chart({
      title: {
          text: 'Solar Employment Growth by Sector, 2010-2016'
      },
      yAxis: {
          title: {
              text: 'Number of Employees'
          }
      },
      xAxis: {
          tickInterval: 1
      },
      credits: {
        enabled: false
      },
      legend: {
          layout: 'vertical',
          align: 'left',
          verticalAlign: 'top',
          itemMarginBottom: 8,
      },

      plotOptions: {
          series: {
              pointStart: 2015
          }
      },

      series: [{
          name: 'Installation',
          data: [43934, 52503, 57177, 69658, 97031, 119931, 137133, 154175]
      }, {
          name: 'Manufacturing',
          data: [24916, 24064, 29742, 29851, 32490, 30282, 38121, 40434]
      }, {
          name: 'Sales & Distribution',
          data: [11744, 17722, 16005, 19771, 20185, 24377, 32147, 39387]
      }, {
          name: 'Project Development',
          data: [null, null, 7988, 12169, 15112, 22452, 34400, 34227]
      }, {
          name: 'Other',
          data: [12908, 5948, 8105, 11248, 8989, 11816, 18274, 18111]
      }]
    });


    this.chart = chart;

    //chart.ref$.subscribe(console.log);
  }

  addPoint() {
    if (this.chart) {
      this.chart.addPoint(Math.floor(Math.random() * 10));
    } else {
      alert('init chart, first!');
    }
  }
  //
  // addSerie() {
  //   this.chart.addSerie({
  //     name: 'Line ' + Math.floor(Math.random() * 10),
  //     data: [
  //       Math.floor(Math.random() * 10),
  //       Math.floor(Math.random() * 10),
  //       Math.floor(Math.random() * 10),
  //       Math.floor(Math.random() * 10),
  //       Math.floor(Math.random() * 10),
  //       Math.floor(Math.random() * 10),
  //       Math.floor(Math.random() * 10),
  //       Math.floor(Math.random() * 10),
  //       Math.floor(Math.random() * 10)
  //     ]
  //   });
  // }
  //
  // removePoint() {
  //   this.chart.removePoint(this.chart.ref.series[0].data.length - 1);
  // }
  //
  // removeSerie() {
  //   this.chart.removeSerie(this.chart.ref.series.length - 1);
  // }



}
