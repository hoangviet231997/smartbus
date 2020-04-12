import { Component, OnInit, AfterViewInit, OnDestroy, ViewEncapsulation } from '@angular/core';
import { ManagerDashboardsService, ManagerUsersService, ManagerDevicesService, ManagerVehiclesService, ManagerRoutesService } from '../../../api/services';
import { Dashboard } from '../../../api/models';
import { ActivatedRoute } from '@angular/router';
import 'rxjs/add/operator/filter';
import { NgxSpinnerService } from 'ngx-spinner';
import * as io from 'socket.io-client';
import swal from 'sweetalert2';
import { TranslateService } from '@ngx-translate/core';
import { ApiConfiguration } from '../../../api/api-configuration';

var _this;

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class DashboardComponent implements OnInit, AfterViewInit, OnDestroy {

  public zoom = 5.5;
  public latitude = 12.6496222;
  public lngitude = 104.3004339;
  public checkMarker = '';
  public infoWindowOpened = null;
  public vehicles_direction_from = [];
  public vehicles_direction_to = [];
  // public vehicles_not_running = [];
  public busStations: any = [];
  private intervalVehicles;
  public isCheckParams = false;
  private vehicleId: number;
  private routeId: number;
  public license_plates: any = '';
  public driver_name: any = '';
  public subdriver_name: any = '';
  public direction_name: any = '';

  public selectedRouteId: any = '';
  public routes: any = [];

  public countUser = 0;
  public countDevice = 0;
  public countVehicle = 0;

  // private url = 'https://node.busmap.com.vn:2399'; //live
  // private url = 'https://preprod.busmap.com.vn:2300'; //preprod
  // public url = 'https://beta.busmap.com.vn:2309'; //beta
  public socket;
  public vehicles = [];
  public company_id: any = '';
  public company_lat: any = '';
  public company_lng: any = '';

  public vehicleItems: Array<any> = [];
  public vehicleValue: any = [];

  public mapStyles: any = [
    {
      featureType: 'poi.business',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.government',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.attraction',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.medical',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.place_of_worship',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.school',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.sports_complex',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'transit',
      elementType: 'labels.icon',
      stylers: [{ visibility: 'off' }]
    }
  ];

  public permissions:any = [];

  constructor(
    private apiDashboard: ManagerDashboardsService,
    private apiRoute: ManagerRoutesService,
    private route: ActivatedRoute,
    private spinner: NgxSpinnerService,
    private apiUser: ManagerUsersService,
    private apiDevice: ManagerDevicesService,
    private apivehicle: ManagerVehiclesService,
    private translate: TranslateService,
    private config: ApiConfiguration,
  ) {
    this.vehicleValue = [];
  }

  ngOnInit() {

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }

    _this = this;
    this.getDashboarSocket();
  }

  ngOnDestroy() {
    clearInterval(this.intervalVehicles);
    this.socket.close();
  }

  ngAfterViewInit() {

    this.refreshView();
  }

  getDataVehicle() {
    this.vehicleValue = [];
    this.apivehicle.managerlistVehicles({
      page: 1,
      limit: 999999999
    }).subscribe(vehicles => {
      this.countVehicle = vehicles.length;
      this.vehicleItems = [];
      for (let i = 0; i < this.countVehicle; i++) {
        this.vehicleItems.push({
          id: vehicles[i].id,
          text: vehicles[i].license_plates
        });
      }
    });
  }

  getDataUser() {

    this.apiUser.managerListUsers({
      page: 1,
      limit: 999999999
    }).subscribe(users => {
      this.countUser = users.length;
    });
  }

  getDataDevice() {

    this.apiDevice.managerListDevices({
      page: 1,
      limit: 999999999
    }).subscribe(devices => {
      this.countDevice = devices.length;
    });
  }

  getRoute() {
    this.apiRoute.managerlistRoutes({
      page: 1,
      limit: 999999999
    }).subscribe(data => {
      this.routes = data;
    });
  }

  getRouteBusStation() {
    this.apiDashboard.managerDashboardGetRouteBusStations().subscribe(data => {
      this.busStations = data;
      this.infoWindowOpened = null;
    });
  }

  refreshView() {

    // this.route.queryParams.subscribe(
    //   params => {
    //     if (params.vehicleId || params.routeId) {
    //       this.isCheckParams = true;
    //       this.selectedRouteId = parseInt(params.routeId);
    //     }
    //   });

    // if (this.isCheckParams) {
    //   this.getRouteParams();
    //   // this.getDashboarData();
    //   // this.runTime();
    //   // this.getDashboarSocket();
    //   this.getDataUser();
    //   this.getDataDevice();
    //   this.getDataVehicle();
    //   this.getRoute();
    // } else {
      // this.getDashboarData();
      // this.runTime();
      // this.getDashboarSocket();
      this.getDataUser();
      this.getDataDevice();
      this.getDataVehicle();
      // this.getRouteBusStation();
      this.getRoute();
    // }
  }

  // getRouteParams() {

  //   this.route.queryParams.subscribe(
  //     params => {
  //       this.vehicleId = params.vehicleId;
  //       this.routeId = params.routeId;
  //       if (this.routeId) {
  //         this.apiRoute.managerGetRouteById(this.routeId).subscribe(data => {
  //           this.busStations = [];
  //           data.bus_stations.forEach(element => {
  //             let obj = {
  //               address: element.address,
  //               lat: element.lat,
  //               lng: element.lng,
  //               name: element.name,
  //               route_name: data.name,
  //               station_order: element.station_order,
  //             }
  //             this.busStations.push(obj);
  //           });
  //
  //           // this.infoWindowOpened = null;
  //         })
  //       }
  //     }
  //   );
  // }

  getDashboarData() {

    // if (this.vehicleId === undefined && this.company_lat && this.company_lng) {
    //   if(this.company_lat != 0){
    //     this.latitude = parseFloat(this.company_lat);
    //     this.lngitude = parseFloat(this.company_lng);
    //     this.zoom = 10;
    //   }
    // }
    // else {
    //   const vehicle = _this.vehicles_is_running.find(data => data.vehicle_id === Number(this.vehicleId));
    //   if (vehicle) {
    //     if (vehicle.position.coordinates[1] && vehicle.position.coordinates[0]) {
    //       this.latitude = vehicle.position.coordinates[1];
    //       this.lngitude = vehicle.position.coordinates[0];
    //       this.zoom = 16;
    //     }
    //   }
    // };

    //
    //   this.apiDashboard.managerDashboardGetData().subscribe(
    //     resp => {
    //       if (this.vehicleId === undefined && resp.company.lat && resp.company.lng) {
    //         this.latitude = resp.company.lat;
    //         this.lngitude = resp.company.lng;
    //         this.zoom = 10;
    //       } else {
    //         // focus vehicle
    //         const vehicle = resp['vehicles_is_running'].find(data => data.id === Number(this.vehicleId));
    //
    //         if (vehicle) {
    //           if (vehicle.lat && vehicle.lng) {
    //             this.latitude = vehicle.lat;
    //             this.lngitude = vehicle.lng;
    //             this.zoom = 16;
    //           }
    //         }
    //       };
    //       this.vehicles_is_running = resp['vehicles_is_running'];
    //       this.vehicles_not_running = resp['vehicles_not_running'];
    //     }
    //   );
  }

  async getDashboarSocket() {

    this.company_id = await JSON.parse(localStorage.getItem('user')).company_id;
    this.company_lat = await JSON.parse(localStorage.getItem('user')).company['position'].coordinates[1];
    this.company_lng = await JSON.parse(localStorage.getItem('user')).company['position'].coordinates[0];

    if (this.company_lat != 0 || this.company_lng != 0) {
      this.latitude = parseFloat(this.company_lat);
      this.lngitude = parseFloat(this.company_lng);
      this.zoom = 10
    }

    this.socket = io(this.config.getStrUrlSocket());
    this.socket.emit('receiveDataVehicleWeb', this.company_id);

    this.socket.on('emitVehicleWeb_' + this.company_id, function (vehicles) {

      vehicles.forEach(function (element) {

        if (element.is_running === 1){
          // //check running
          // let index_run = _this.vehicles_is_running.findIndex(e => (e.vehicle_id == element.vehicle_id) );
          // if(index_run != -1){
          //   _this.vehicles_is_running.map(e_vehicle => {
          //     if((e_vehicle.vehicle_id == element.vehicle_id) && (e_vehicle.location.coordinates[1] != element.location.coordinates[1]
          //       || e_vehicle.location.coordinates[0] != element.location.coordinates[0])){
          //       e_vehicle.location.coordinates[1] = element.location.coordinates[1];
          //       e_vehicle.location.coordinates[0] = element.location.coordinates[0];
          //       // return e_vehicle;
          //     }
          //   });
          // }else{
          //   _this.vehicles_is_running.push(element);
          // }
          //
          // //check stop
          // let index_stop = _this.vehicles_not_running.findIndex(e => (e.vehicle_id == element.vehicle_id) );
          // if(index_stop != -1)  _this.vehicles_not_running.splice(index_stop, 1);

          if(element.direction == 0){
            //check direction from
            let index_direction_from = _this.vehicles_direction_from.findIndex(e => (e.vehicle_id == element.vehicle_id) );
            if(index_direction_from != -1){
              _this.vehicles_direction_from.map(e_vehicle => {
                if((e_vehicle.vehicle_id == element.vehicle_id) && (e_vehicle.location.coordinates[1] != element.location.coordinates[1]
                  || e_vehicle.location.coordinates[0] != element.location.coordinates[0])){
                  e_vehicle.location.coordinates[1] = element.location.coordinates[1];
                  e_vehicle.location.coordinates[0] = element.location.coordinates[0];
                }
              });
            }else{
              _this.vehicles_direction_from.push(element);
            }
            //check direction to
            let index_direction_to = _this.vehicles_direction_to.findIndex(e => (e.vehicle_id == element.vehicle_id) );
            if(index_direction_to != -1)  _this.vehicles_direction_to.splice(index_direction_to, 1);
          }else{
            //check direction to
            let index_direction_to = _this.vehicles_direction_to.findIndex(e => (e.vehicle_id == element.vehicle_id) );
            if(index_direction_to != -1){
              _this.vehicles_direction_to.map(e_vehicle => {
                if((e_vehicle.vehicle_id == element.vehicle_id) && (e_vehicle.location.coordinates[1] != element.location.coordinates[1]
                  || e_vehicle.location.coordinates[0] != element.location.coordinates[0])){
                  e_vehicle.location.coordinates[1] = element.location.coordinates[1];
                  e_vehicle.location.coordinates[0] = element.location.coordinates[0];
                }
              });
            }else{
              _this.vehicles_direction_to.push(element);
            }
            //check running
            let index_direction_from = _this.vehicles_direction_from.findIndex(e => (e.vehicle_id == element.vehicle_id) );
            if(index_direction_from != -1)  _this.vehicles_direction_from.splice(index_direction_from, 1);
          }
        }
        // else{
        //
        //   //check stop
        //   let index_stop = _this.vehicles_not_running.findIndex(e => (e.vehicle_id == element.vehicle_id) );
        //   if(index_stop != -1){
        //     _this.vehicles_not_running.map(e_vehicle => {
        //       if((e_vehicle.vehicle_id == element.vehicle_id) && (e_vehicle.location.coordinates[1] != element.location.coordinates[1]
        //         || e_vehicle.location.coordinates[0] != element.location.coordinates[0])){
        //         e_vehicle.location.coordinates[1] = element.location.coordinates[1];
        //         e_vehicle.location.coordinates[0] = element.location.coordinates[0];
        //         // return e_vehicle;
        //       }
        //     });
        //   }else{
        //     _this.vehicles_not_running.push(element);
        //   }
        //
        //   //check running
        //   let index_run = _this.vehicles_is_running.findIndex(e => (e.vehicle_id == element.vehicle_id) );
        //   if(index_run != -1)  _this.vehicles_is_running.splice(index_run, 1);
        // }

        // let index = _this.vehicles.findIndex(e => (e.vehicle_id == element.vehicle_id) )
        // if(index != -1){
        //   _this.vehicles.map(e_vehicle => {
        //     if((e_vehicle.vehicle_id == element.vehicle_id) && (e_vehicle.location.coordinates[1] != element.location.coordinates[1]
        //       || e_vehicle.location.coordinates[0] != element.location.coordinates[0])){
        //       e_vehicle.location.coordinates[1] = element.location.coordinates[1];
        //       e_vehicle.location.coordinates[0] = element.location.coordinates[0];
        //       // return e_vehicle;
        //     }
        //   });
        // }else{
        //   _this.vehicles.push(element);
        // }
        _this.infoWindowOpened = null;
      });
    });
    clearInterval(this.intervalVehicles);
    _this.intervalVehicles = setInterval(() => {
      _this.socket.emit('receiveDataVehicleWeb', _this.company_id);
    }, 5000);
  }

  checkedMaker(type: number) {

    if (type == 1) {
      this.mapStyles = null;
    } else {
      this.mapStyles = [
        {
          featureType: 'poi.business',
          stylers: [{ visibility: 'off' }]
        },
        {
          featureType: 'poi.government',
          stylers: [{ visibility: 'off' }]
        },
        {
          featureType: 'poi.attraction',
          stylers: [{ visibility: 'off' }]
        },
        {
          featureType: 'poi.medical',
          stylers: [{ visibility: 'off' }]
        },
        {
          featureType: 'poi.place_of_worship',
          stylers: [{ visibility: 'off' }]
        },
        {
          featureType: 'poi.school',
          stylers: [{ visibility: 'off' }]
        },
        {
          featureType: 'poi.sports_complex',
          stylers: [{ visibility: 'off' }]
        },
        {
          featureType: 'transit',
          elementType: 'labels.icon',
          stylers: [{ visibility: 'off' }]
        }
      ];
    }
  }

  // runTime() {
  //   this.intervalVehicles = setInterval(() => {
  //     this.apiDashboard.managerDashboardGetVehicles().subscribe(
  //       resp => {
  //         // find and focus vehicle
  //         if (this.vehicleId !== undefined) {
  //           const vehicle =  resp['vehicles_is_running'].find(data => data.id === Number(this.vehicleId));
  //           if (vehicle) {
  //             if (vehicle.lat && vehicle.lng) {
  //               this.latitude = vehicle.lat;
  //               this.lngitude = vehicle.lng;
  //               this.zoom = 16;
  //             }
  //           }
  //         }
  //         this.infoWindowOpened = null;
  //         // push data
  //         this.vehicles_is_running = resp['vehicles_is_running'];
  //         this.vehicles_not_running = resp['vehicles_not_running'];
  //       }
  //     );
  //   }, 15000);
  // }

  clickedMarker(infoWindow) {

    if (_this.infoWindowOpened === infoWindow) {
      return;
    }
    if (_this.infoWindowOpened !== null) {
      _this.infoWindowOpened.close();
    }
    _this.infoWindowOpened = infoWindow;
  }

  clickedMarkerBusStation(infoWindowStation) {

    if (this.infoWindowOpened === infoWindowStation) {
      return;
    }
    if (this.infoWindowOpened !== null) {
      this.infoWindowOpened.close();
    }
    this.infoWindowOpened = infoWindowStation;
  }

  getDataRouteBusStation() {

      if (this.selectedRouteId == 0) {
        this.getRouteBusStation();
      }

      if(this.selectedRouteId == null || this.selectedRouteId == ''){
          this.busStations = [];
      }

      if(this.selectedRouteId > 0) {
        this.busStations = [];
        this.apiRoute.managerGetRouteById(this.selectedRouteId).subscribe(data => {
          data.bus_stations.forEach(element => {
            let obj = {
              address: element.address,
              lat: element.lat,
              lng: element.lng,
              name: element.name,
              distance: element['distance'],
              route_name: data.name,
              station_order: element.station_order,
            }
            this.busStations.push(obj);
          });
          this.infoWindowOpened = null;
        });
      }
  }

  refreshValueVehicle(value:any):void {
    this.vehicleValue = value;
  }

  public selectedVehicle(value: any) {
    this.vehicleValue = value;
    this.searchVehicleById();
  }

  public removedVehicle(value: any){

    if( this.vehicleValue['id'] == value.id){
      this.vehicleValue['id'] = null;
    }
    this.searchVehicleById();
  }

  public searchVehicleById(){

    _this = this;

    if(_this.vehicleValue['id'] != null || _this.vehicleValue.length == 0){

      if(_this.vehicles_direction_from.length > 0 || _this.vehicles_direction_to.length > 0){

        const vehicleObj_direction_from = _this.vehicles_direction_from.find(data => data.vehicle_id === parseInt(_this.vehicleValue['id']));
        const vehicleObj_direction_to = _this.vehicles_direction_to.find(data => data.vehicle_id === parseInt(_this.vehicleValue['id']));

        if(vehicleObj_direction_to !== undefined || vehicleObj_direction_from !== undefined){
          if(vehicleObj_direction_to !== undefined){
            _this.zoom = 20;
            _this.latitude = vehicleObj_direction_to.location.coordinates[1];
            _this.lngitude = vehicleObj_direction_to.location.coordinates[0];
          }else if(vehicleObj_direction_from !== undefined){
            _this.zoom = 20;
            _this.latitude = vehicleObj_direction_from.location.coordinates[1];
            _this.lngitude = vehicleObj_direction_from.location.coordinates[0];
          }
        }else{
          swal(_this.translate.instant('SWAL_ERROR'), _this.translate.instant('LBL_MAP_VEHICLE_NOT_RUNNING'), 'warning');
          return;
        }
      }else{
        swal(_this.translate.instant('SWAL_ERROR'), _this.translate.instant('LBL_MAP_VEHICLE_NOT_RUNNING'), 'warning');
        return;
      }
    }else{
      _this.latitude = parseFloat(_this.company_lat)
      _this.lngitude = parseFloat(_this.company_lng);
      _this.zoom = 10;
    }
  }
}
