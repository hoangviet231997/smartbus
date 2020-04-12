import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { Chart } from 'angular-highcharts';
import { ManagerDevicesService } from '../../../api/services';
import { RevenueChart } from '../../../api/models/revenue-chart';
import { interval, Observable } from 'rxjs';
import { TranslateService } from '@ngx-translate/core';
import swal from 'sweetalert2';
import * as moment from 'moment';

@Component({
  selector: 'app-device-chart',
  templateUrl: './device-chart.component.html',
  styleUrls: ['./device-chart.component.css']
})
export class DeviceChartComponent implements OnInit, AfterViewInit {

  @ViewChild('ticketTypeChartModal') ticketTypeChartModal;
  @ViewChild('ticketTypeGoodsChartModal') ticketTypeGoodsChartModal;
  @ViewChild('ticketTypeOtherChartModal') ticketTypeOtherChartModal;
  @ViewChild('revenueChartModal') revenueChartModal;
  @ViewChild('depositChartModal') depositChartModal;

  public count_price = [];
  public value_price = [];
  public bus_station_name = [];
  public xLabel = [];
  public totalPrice = [];

  public isLoadingTypeTicket = false;
  public isLoadingDeposit = false;
  public isLoadingRevenue = false;

  public ticketTypeChart;
  public ticketTypeOtherChart;
  public ticketTypeGoodsChart;
  public depositChart;
  public revenueChart;

  private intervalRevenue;

  constructor(
    private apiDevices: ManagerDevicesService,
    private translate: TranslateService
  ){}

  ngOnInit() { }

  ngOnDestroy() {
    clearInterval(this.intervalRevenue);
  }

  ngAfterViewInit() {}

  openTicketTypeChart(shiftId = 0) {

    clearInterval(this.intervalRevenue);

    this.isLoadingTypeTicket = false;
    this.count_price = [];
    this.value_price = [];
    this.xLabel = [];

    this.ticketTypeChart = new Chart({
      chart: {
        type: 'line',
      },
      title: {
        text: this.translate.instant('LBL_DEV_TYPETICKET_CHART')
      },
      credits: {
        enabled: false
      },
      yAxis: {
        title: {
          text: this.translate.instant('LBL_DEV_TYPETICKET') + " (VNĐ)"
        }
      },
      xAxis: {
        categories: this.xLabel
      },
      tooltip: {
        shared: false,

        useHTML: true,
        borderWidth: 3,
        headerFormat: '<b style="color: #fff;"></b><br/>',
        formatter: function () {
          var s = '<b>' + this.x + ' - ' + this.total.bus_station_name + '</b>';
          s += '<br/>' + this.total.title;
          return s;
        }
      },
      plotOptions: {
        line: {
          dataLabels: {
            enabled: true
          },
          enableMouseTracking: true
        }
      },
      series: []
    });
    this.ticketTypeChartModal.show();

    //get revenue by tickets type
    this.apiDevices.managerDeviceGetRevenueByShiftId({
      shiftId: shiftId,
      typeOpt: 1
    }).subscribe(
      resp => {
        let titleChart = resp.title_chart;
        //push data count amonut
        for (let index = 0; index < resp['msg'].length; index++) {
          const element = resp['msg'][index];
          var type = '';
          element.type == "qrcode" ? type = this.translate.instant("LBL_RCT_DETAIL_QRCODE") : "";
          element.type == "charge" ? type = this.translate.instant("LBL_RCT_DETAIL_SWIPE") : "";
          element.type == "pos" ? type = this.translate.instant("LBL_RCT_DETAIL_CASH") : "";
          let customTutip = {
            y: element.price,
            total: {
              title: titleChart + ' - <b>' + element.price.toLocaleString() + '</b> - '+type,
              bus_station_name: element.bus_station_name
            }
          };
          this.value_price.push(customTutip);
          this.xLabel.push(element.datetime);
        }

        this.ticketTypeChart.addSerie({
          name: titleChart,
          data: this.value_price
        });
        this.isLoadingTypeTicket = true;

        // get data in interval
        this.intervalRevenue = setInterval(() => {

          this.apiDevices.managerDeviceGetRevenueByShiftId({
            shiftId: shiftId,
            typeOpt: 1
          }).subscribe(
            data => {
              const currentLenght = this.value_price.length;
              const newLenght = data['msg'].length;

              if (currentLenght < newLenght) {
                for (let index = currentLenght; index < newLenght; index++) {
                  const element = data['msg'][index];
                  element.type == "qrcode" ? type = this.translate.instant("LBL_RCT_DETAIL_QRCODE") : "";
                  element.type == "charge" ? type = this.translate.instant("LBL_RCT_DETAIL_SWIPE") : "";
                  element.type == "pos" ? type = this.translate.instant("LBL_RCT_DETAIL_CASH") : "";
                  let customTutip_setInterval = {
                    y: element.price,
                    total: {
                      title: titleChart + ' - <b>' + element.price.toLocaleString() + '</b> -'+type,
                      bus_station_name: element.bus_station_name
                    }
                  };
                  this.xLabel.push(element.datetime);
                  this.ticketTypeChart.addPoint(customTutip_setInterval);
                }
              }
            }
          );
        }, 30000);
      },
      err => {
        swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA') });
        this.ticketTypeChartModal.hide();
        clearInterval(this.intervalRevenue);
      }
    );
  }

  openRevenueChart(shiftId = 0) {

    clearInterval(this.intervalRevenue);

    this.isLoadingRevenue = false;
    this.totalPrice = [];
    this.xLabel = [];

    this.revenueChart = new Chart({
      chart: {
        type: 'line'
      },
      title: {
        text: this.translate.instant('LBL_DEV_REVENUE_CHART')
      },
      credits: {
        enabled: false
      },
      yAxis: {
        title: {
          text: this.translate.instant('LBL_DEV_TYPETICKET') + ' (VNĐ)'
        }
      },
      xAxis: {
        categories: this.xLabel
      },
      plotOptions: {
        line: {
          dataLabels: {
            enabled: true
          },
          enableMouseTracking: true
        }
      },
      series: []
    });
    this.revenueChartModal.show();

    // get revenue
    this.apiDevices.managerDeviceGetRevenueByShiftId({
      shiftId: shiftId,
      typeOpt: 2,
      tranId: null
    }).subscribe(
      resp => {
        let total = 0;
        var titleChart = resp.title_chart;
        for (let index = 0; index < resp['msg'].length; index++) {
          const element = resp['msg'][index];
          this.totalPrice.push(element.total);
          this.xLabel.push(element.datetime);
          total = element.total;
        }
        this.revenueChart.addSerie({
          name: titleChart,
          data: this.totalPrice
        });

        this.isLoadingRevenue = true;
        var tranId = resp['tran_id'];

        // get data in interval
        this.intervalRevenue = setInterval(() => {
          this.apiDevices.managerDeviceGetRevenueByShiftId({
            shiftId: shiftId,
            typeOpt: 2,
            tranId: tranId
          }).subscribe(
            data => {
              // var currentLenght = this.totalPrice.length;
              // var newLenght = data['msg'].length;
              // if (currentLenght < newLenght) {
                for (let index = 0; index < data['msg'].length; index++) {
                  const element = data['msg'][index];
                  this.xLabel.push(element.datetime);
                  total += element.sub_total;
                  this.revenueChart.addPoint(total);
                }
              // }
              // else if(currentLenght == newLenght){
              //   const count = currentLenght -1;
              //   if(this.totalPrice[count] !==  data['msg'][count].total){
              //     let plus = moment(data['msg'][count].datetime, 'DD/MM/YYYY HH:mm:ss').add(90, 'seconds');
              //     let datetime = moment(plus).format('DD/MM/YYYY HH:mm:ss');
              //     this.xLabel.push(datetime);
              //     this.revenueChart.addPoint(data['msg'][count].total);
              //   }
              // }
              tranId = data['tran_id'];
            }
          );
        }, 30000);
      },
      err => {
        swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA') });
        this.revenueChartModal.hide();
        clearInterval(this.intervalRevenue);
      }
    );
  }

  openDepositChart(shiftId = 0) {

    clearInterval(this.intervalRevenue);

    this.isLoadingDeposit = false;
    this.count_price = [];
    this.value_price = [];
    this.xLabel = [];

    this.depositChart = new Chart({

      chart: {
        type: 'line'
      },
      title: {
        text: this.translate.instant('LBL_DEV_DEPOSIT_CHART')
      },
      credits: {
        enabled: false
      },
      yAxis: {
        title: {
          text: this.translate.instant('LBL_DEV_TYPETICKET') + ' (VNĐ)'
        }
      },
      xAxis: {
        categories: this.xLabel
      },
      plotOptions: {
        line: {
          dataLabels: {
            enabled: true
          },
          enableMouseTracking: true
        }
      },
      tooltip: {
        shared: false,
        useHTML: true,
        borderWidth: 3,
        headerFormat: '<b style="color: #fff;"></b><br/>',
        formatter: function () {

          var s = '<b>' + this.x + ' - ' + this.total.bus_station_name + '</b>';
          s += '<br/>' + this.total.title;
          return s;
        }
      },
      series: []
    });
    this.depositChartModal.show();

    //get revenue by tickets type
    this.apiDevices.managerDeviceGetRevenueByShiftId({
      shiftId: shiftId,
      typeOpt: 3
    }).subscribe(
      resp => {

        let titleChart = resp.title_chart;
        //push data count amonut
        for (let index = 0; index < resp['msg'].length; index++) {

          const element = resp['msg'][index];
          var type = '';
          element.type == "deposit" ? type = this.translate.instant("LBL_RCT_DETAIL_RECHARGE") : "";
          element.type == "deposit_month" ? type = this.translate.instant("LBL_RCT_DETAIL_DEPOSIT_MONTH") : "";

          let customTutip = { y: element.price, total: { title: titleChart + ' - <b>' + element.price.toLocaleString() + '</b> - '+type, bus_station_name: element.bus_station_name } };
          this.value_price.push(customTutip);
          this.xLabel.push(element.datetime);
        }

        this.depositChart.addSerie({
          name: titleChart,
          data: this.value_price
        });
        this.isLoadingDeposit = true;

        // get data in interval
        this.intervalRevenue = setInterval(() => {

          this.apiDevices.managerDeviceGetRevenueByShiftId({

            shiftId: shiftId,
            typeOpt: 3
          }).subscribe(
            data => {
              const currentLenght = this.value_price.length;
              const newLenght = data['msg'].length;
              if (currentLenght < newLenght) {
                for (let index = currentLenght; index < newLenght; index++) {
                  const element = data['msg'][index];
                  element.type == "deposit" ? type = this.translate.instant("LBL_RCT_DETAIL_RECHARGE") : "";
                  element.type == "deposit_month" ? type = this.translate.instant("LBL_RCT_DETAIL_DEPOSIT_MONTH") : "";

                  let customTutip_setInterval = { y: element.price, total: { title: titleChart + ' - <b>' + element.price.toLocaleString() + '</b> - '+type, bus_station_name: element.bus_station_name } };
                  this.xLabel.push(element.datetime);
                  this.depositChart.addPoint(customTutip_setInterval);
                }
              }
            }
          );
        }, 30000);
      },
      err => {
        swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA') });
        this.depositChartModal.hide();
        clearInterval(this.intervalRevenue);
      }
    );
  }

  openTicketGoodsChart(shiftId = 0) {

    clearInterval(this.intervalRevenue);

    this.isLoadingTypeTicket = false;
    this.count_price = [];
    this.value_price = [];
    this.xLabel = [];

    this.ticketTypeGoodsChart = new Chart({
      chart: {
        type: 'line'
      },
      title: {
        text: this.translate.instant('LBL_DEV_TYPEGOODS_CHART')
      },
      credits: {
        enabled: false
      },
      yAxis: {
        title: {
          text: this.translate.instant('LBL_DEV_TYPETICKET') + " (VNĐ)"
        }
      },
      xAxis: {
        categories: this.xLabel
      },
      tooltip: {
        shared: false,

        useHTML: true,
        borderWidth: 3,
        headerFormat: '<b style="color: #fff;"></b><br/>',
        formatter: function () {
          var s = '<b>' + this.x + ' - ' + this.total.bus_station_name + '</b>';
          s += '<br/>' + this.total.title;
          return s;
        }
      },
      plotOptions: {
        line: {
          dataLabels: {
            enabled: true
          },
          enableMouseTracking: true
        }
      },
      series: []
    });
    this.ticketTypeGoodsChartModal.show();

    //get revenue by goods type
    this.apiDevices.managerDeviceGetRevenueByShiftId({
      shiftId: shiftId,
      typeOpt: 4
    }).subscribe(
      resp => {

        let titleChart = resp.title_chart;
        //push data count amonut
        for (let index = 0; index < resp['msg'].length; index++) {
          const element = resp['msg'][index];

          var type = '';
          element.type == "pos_goods" ? type = this.translate.instant("LBL_RCT_DETAIL_POS_GOODS") : "";
          element.type == "charge_goods" ? type = this.translate.instant("LBL_RCT_DETAIL_CHARGE_GOODS") : "";
          element.type == "qrcode_goods" ? type = this.translate.instant("LBL_RCT_DETAIL_ONLINE_GOODS") : "";

          let customTutip = { y: element.price, total: { title: titleChart + ' - <b>' + element.price.toLocaleString() + '</b> -' + type, bus_station_name: element.bus_station_name } };
          this.value_price.push(customTutip);
          this.xLabel.push(element.datetime);
        }

        this.ticketTypeGoodsChart.addSerie({
          name: titleChart,
          data: this.value_price,
        });
        this.isLoadingTypeTicket = true;

        // get data in interval
        this.intervalRevenue = setInterval(() => {

          this.apiDevices.managerDeviceGetRevenueByShiftId({
            shiftId: shiftId,
            typeOpt: 4
          }).subscribe(
            data => {

              const currentLenght = this.value_price.length;
              const newLenght = data['msg'].length;
              if (currentLenght < newLenght) {
                for (let index = currentLenght; index < newLenght; index++) {
                  const element = data['msg'][index];
                  element.type == "pos_goods" ? type = this.translate.instant("LBL_RCT_DETAIL_POS_GOODS") : "";
                  element.type == "charge_goods" ? type = this.translate.instant("LBL_RCT_DETAIL_CHARGE_GOODS") : "";
                  element.type == "qrcode_goods" ? type = this.translate.instant("LBL_RCT_DETAIL_ONLINE_GOODS") : "";
                  let customTutip_setInterval = { y: element.price, total: { title: titleChart + ' - <b>' + element.price.toLocaleString() + '</b> - '+type, bus_station_name: element.bus_station_name } };
                  this.xLabel.push(element.datetime);
                  this.ticketTypeGoodsChart.addPoint(customTutip_setInterval);
                }
              }
            }
          );
        }, 30000);
      },
      err => {
        swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA') });
        this.ticketTypeGoodsChartModal.hide();
        clearInterval(this.intervalRevenue);
      }
    );
  }

  openTicketTypeOtherChart(shiftId = 0) {

    clearInterval(this.intervalRevenue);

    this.isLoadingTypeTicket = false;
    this.count_price = [];
    this.value_price = [];
    this.xLabel = [];

    this.ticketTypeOtherChart = new Chart({
      chart: {
        type: 'line',
      },
      title: {
        text: this.translate.instant('LBL_DEV_OTHER_CHART')
      },
      credits: {
        enabled: false
      },
      yAxis: {
        title: {
          text: this.translate.instant('LBL_DEV_TYPETICKET') + " (VNĐ)"
        }
      },
      xAxis: {
        categories: this.xLabel
      },
      tooltip: {
        shared: false,

        useHTML: true,
        borderWidth: 3,
        headerFormat: '<b style="color: #fff;"></b><br/>',
        formatter: function () {
          var s = '<b>' + this.x + ' - ' + this.total.bus_station_name + '</b>';
          s += '<br/>' + this.total.title;
          return s;
        }
      },
      plotOptions: {
        line: {
          dataLabels: {
            enabled: true
          },
          enableMouseTracking: true
        }
      },
      series: []
    });
    this.ticketTypeOtherChartModal.show();

    //get revenue by tickets type
    this.apiDevices.managerDeviceGetRevenueByShiftId({
      shiftId: shiftId,
      typeOpt: 5
    }).subscribe(
      resp => {
        let titleChart = resp.title_chart;
        //push data count amonut
        for (let index = 0; index < resp['msg'].length; index++) {
          const element = resp['msg'][index];
          var type = '';
          element.type == "charge_free" ? type = this.translate.instant("LBL_RCT_DETAIL_CHARGE_FREE") : "";
          element.type == "charge_month" ? type = this.translate.instant("LBL_RCT_DETAIL_CHARGE_MONTH") : "";
          let customTutip = {
            y: 0,
            total: {
              title: titleChart + ' - <b>0</b> - '+type,
              bus_station_name: element.bus_station_name
            }
          };
          this.value_price.push(customTutip);
          this.xLabel.push(element.datetime);
        }

        this.ticketTypeOtherChart.addSerie({
          name: titleChart,
          data: this.value_price
        });
        this.isLoadingTypeTicket = true;

        // get data in interval
        this.intervalRevenue = setInterval(() => {

          this.apiDevices.managerDeviceGetRevenueByShiftId({
            shiftId: shiftId,
            typeOpt: 5
          }).subscribe(
            data => {

              const currentLenght = this.value_price.length;
              const newLenght = data['msg'].length;

              if (currentLenght < newLenght) {

                for (let index = currentLenght; index < newLenght; index++) {

                  const element = data['msg'][index];
                  element.type == "charge_free" ? type = this.translate.instant("LBL_RCT_DETAIL_CHARGE_FREE") : "";
                  element.type == "charge_month" ? type = this.translate.instant("LBL_RCT_DETAIL_CHARGE_MONTH") : "";
                  let customTutip_setInterval = { y: 0, total: { title: titleChart + ' - <b>0</b> - '+type, bus_station_name: element.bus_station_name } };
                  this.xLabel.push(element.datetime);
                  this.ticketTypeOtherChart.addPoint(customTutip_setInterval);
                }
              }
            }
          );
        }, 30000);
      },
      err => {
        swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA') });
        this.ticketTypeOtherChartModal.hide();
        clearInterval(this.intervalRevenue);
      }
    );
  }
}
