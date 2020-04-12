import { Component, OnInit, AfterViewInit, ViewChild } from '@angular/core';
import { ManagerDevicesService, AdminDevicesService } from '../../../api/services';
import { Device } from '../../../api/models';
import { map } from 'rxjs/operators/map';
@Component({
  selector: 'app-device-status',
  templateUrl: './device-status.component.html',
  styleUrls: ['./device-status.component.css']
})
export class DeviceStatusComponent implements OnInit {

  public devices: Device[];
  public txtIdentity: any = '';
  public timeoutSearchDevice;

  constructor(
    private apiDevices: ManagerDevicesService,
    private apiDeviceAdmin: AdminDevicesService) {
  }
  ngOnInit() {
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.apiDevices.managerListDevices({
      page: 1,
      limit: 999999999
    }).subscribe(
      resp => {
        this.devices = resp;
      }
    );
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

}
