import { Component, OnInit, AfterViewInit, ViewChild } from '@angular/core';
import { ManagerDevicesService, AdminDevicesService, ManagerModuleCompanyService } from '../../../api/services';
import { Device } from '../../../api/models';
import { DeviceChartComponent } from '../device-chart/device-chart.component';
import { map } from 'rxjs/operators/map';

@Component({
  selector: 'app-devices',
  templateUrl: './devices.component.html',
  styleUrls: ['./devices.component.css']
})
export class DevicesComponent implements OnInit, AfterViewInit {

  @ViewChild('deviceChart') deviceChart: DeviceChartComponent;

  public devices: Device[];

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;
  public timeoutSearchDevice;
  public txtIdentity: any = '';

  public permissions:any = [];
  public isModuleGoods = false;

  constructor(
    private apiDevices: ManagerDevicesService,
    private apiDeviceAdmin: AdminDevicesService,
    private apiModuleCompanies: ManagerModuleCompanyService) {
  }

  ngOnInit() {

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.refreshView();

    this.apiModuleCompanies.listModuleCompany().subscribe(data => {
      data.forEach(element => {
        if(element['name'] === 'Module_VC_Hang_Hoa' ){
          this.isModuleGoods = true;
        }
      });
    })
  }

  refreshView() {
    this.apiDevices.managerListDevicesResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {

        this.devices = resp.body;
        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
      }
    );
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.refreshView();
  }

  getDeviceByIdentitySearch(){
    clearTimeout(this.timeoutSearchDevice);
    this.timeoutSearchDevice = setTimeout(()=>{
      if(this.txtIdentity !== ''){
        this.apiDeviceAdmin.getDeviceByIdentitySearch(this.txtIdentity).subscribe(res => {
          this.devices = res;
        });
      }else{
        this.refreshView();
      }
    },500);
  }

  openDeviceChart(number, shift_id){
    switch(number){
      case 1:
        this.deviceChart.openTicketTypeOtherChart(shift_id);
        break;
      case 2:
        this.deviceChart.openDepositChart(shift_id);
        break;
      case 3:
        this.deviceChart.openTicketGoodsChart(shift_id);
        break;
      case 4:
        this.deviceChart.openTicketTypeChart(shift_id);
        break;
      case 5:
        this.deviceChart.openRevenueChart(shift_id);
        break;
    }
  }
}
