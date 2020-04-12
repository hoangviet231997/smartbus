import { Component, OnInit, AfterViewInit } from '@angular/core';
import { MouseEvent } from '@agm/core';
import { flatten } from '@angular/compiler';
import { AdminCompaniesService, AdminUsersService, AdminDevicesService, ManagerVehiclesService } from '../../../api/services';
import { map } from 'rxjs/operators/map';
import { Device } from '../../../api/models';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit, AfterViewInit {

  public devices: Device[];

  // google maps zoom level
  zoom = 5.5;

  // initial center position for the map
  lat = 16.1474466;
  lng = 106.5499128;

  latitude = 0;
  lngitude = 0;
  checkMarker = '';
  infoWindowOpened = null;

  public countCompany = 0;
  public countUser = 0;
  public countDevice = 0;
  public countVehicle = 0;

  constructor(
    private apiCompanies: AdminCompaniesService,
    private apiUsers: AdminUsersService,
    private apiDevices: AdminDevicesService,
    private apiVehicle: ManagerVehiclesService
  ) { }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.getDataCompany();
    this.getDataUser();
    this.getDataDevice();
    this.getDataVehicle();
  }

  getDataUser(){

    this.apiUsers.listUsers({
      page: 1,
      limit: 999999999
    }).pipe(
      map(_r => {
        return _r;
      }
    )).subscribe(
      res => {
        this.countUser = res.length;
      }
    );
  }

  getDataDevice(){

    this.apiDevices.listDevices({
      page: 1,
      limit: 999999999
    }).subscribe(
      reps => {

        this.countDevice = reps.length;
        this.devices = reps;
      }
    );
  }

  getDataCompany(){

    this.apiCompanies.listCompanies({
      page: 0,
      limit: 9999
    }).subscribe(
      res => {
        this.countCompany = res.length;
      }
    );
  }

  getDataVehicle(){

    this.apiVehicle.managerlistVehicles({
      page: 1,
      limit:999999999
    }).subscribe(vehicles => {
      this.countVehicle = vehicles.length;
    });
  }

  clickedMarker(infoWindow){

    if (this.infoWindowOpened ===  infoWindow) {
      return;
    }
    if (this.infoWindowOpened !== null) {
      this.infoWindowOpened.close();
    }
    this.infoWindowOpened = infoWindow;
  }

  checkIsRunning(isRunning: number) {

    if (isRunning == 1) {
      return 'assets/img/bus.png';
    }
    if(isRunning == 0) {
      return 'assets/img/icon-bus-gray.png';
    }
  }
}
