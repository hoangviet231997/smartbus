
import { Component, OnInit, AfterViewInit, OnDestroy, ViewEncapsulation } from '@angular/core';
import { ManagerDevicesService, AdminDevicesService } from '../../../api/services';
import { ApiConfiguration } from "../../../api/api-configuration";
import { map } from 'rxjs/operators/map';
import { ActivatedRoute } from '@angular/router';
import 'rxjs/add/operator/filter';
import { NgxSpinnerService } from 'ngx-spinner';
import * as io from 'socket.io-client';
import swal from 'sweetalert2';
import { TranslateService } from '@ngx-translate/core';

var _this;

@Component({
  selector: 'app-device-locations',
  templateUrl: './device-locations.component.html',
  styleUrls: ['./device-locations.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class DeviceLocationsComponent implements OnInit, AfterViewInit, OnDestroy {
  public device_items: any = [];
  public intervalDevice;
  public socket;
  public permissions: any;
  public company_id: number;
  public marker_lat = 0;
  public marker_lng = 0;
  public markers = [];
  public latitude = 12.6496222;
  public lngitude = 104.3004339;
  public zoom = 5;
  public company_lat;
  public company_lng;
  public infoWindowOpened = null;

  constructor(
    private apiDevices: ManagerDevicesService,
    private apiDeviceAdmin: AdminDevicesService,
    private config: ApiConfiguration,
    private translate: TranslateService,
  ) { }

  ngOnInit() {
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
      this.company_id = JSON.parse(localStorage.getItem('user')).company_id;
    }
    _this = this;
    this.getSocketDevice();
  }

  async getPositionCompany() {
    this.company_id = await JSON.parse(localStorage.getItem('user')).company_id;
    this.company_lat = await JSON.parse(localStorage.getItem('user')).company['position'].coordinates[1];
    this.company_lng = await JSON.parse(localStorage.getItem('user')).company['position'].coordinates[0];

    if (this.company_lat != 0 || this.company_lng != 0) {
      this.latitude = parseFloat(this.company_lat);
      this.lngitude = parseFloat(this.company_lng);
      this.zoom = 10
    }
  }

  async getSocketDevice() {

    this.getPositionCompany();
    this.socket = io(this.config.getStrUrlSocket());
    this.socket.emit('receiveDataDeviceWeb', this.company_id);

    this.socket.on('emitDeviceWeb_' + this.company_id, (devices) => {

      for (let i = 0; i < devices.length; i++) {
        let index = this.markers.findIndex((e) => (e.imei === devices[i].imei));
        if (index !== (-1)) {
          this.markers.map((e_marker) => {
            if (e_marker.imei === devices[i].imei) {
              e_marker.lat = devices[i].location.coordinates[1];
              e_marker.lng = devices[i].location.coordinates[0];
            }
          });
        } else {
          this.markers.push({
            imei: devices[i].imei,
            lat: devices[i].location.coordinates[1],
            lng: devices[i].location.coordinates[0]
          });
        }
      }
    })

    clearInterval(this.intervalDevice);
    this.intervalDevice = setInterval(() => {
      this.socket.emit('receiveDataDeviceWeb', this.company_id);
    }, 5000);
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
        this.device_items = [];
        for (let i = 0; i < resp.length; i++) {
          this.device_items.push({
            id: resp[i].id,
            text: resp[i].identity
          });
        }
      }
    );
  }

  markerClick(infoWindowDevice) {
    if (this.infoWindowOpened === infoWindowDevice) {
      return;
    }
    if (this.infoWindowOpened !== null) {
      this.infoWindowOpened.close();
    }
    this.infoWindowOpened = infoWindowDevice;
  }

  refreshValueDeviceLocation( event: any):void{

    let index = this.markers.findIndex((e) => (e.imei === event['text']));
    if(index !== -1){
      this.latitude =  this.markers[index].lat;
      this.lngitude =  this.markers[index].lng;
      this.zoom = 20;
    }else{
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('LBL_DEVICE_LOCATION_NOT_IMEI'), 'warning');
      return;
    }
  }

  selectedDeviceLocation(event:any) {

    let index = this.markers.findIndex((e) => (e.imei === event['text']));
    if(index !== -1){
      this.latitude =  this.markers[index].lat;
      this.lngitude =  this.markers[index].lng;
      this.zoom = 20;
    }else{
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('LBL_DEVICE_LOCATION_NOT_IMEI'), 'warning');
      return;
    }
  }

  removedDeviceLocation(event) {
    this.getPositionCompany();
  }

  ngOnDestroy() {
    clearInterval(this.intervalDevice);
    this.socket.close();
  }

}
