import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { ManagerVehiclesService, ManagerRoutesService, ManagerDevicesService, ManagerModuleCompanyService } from '../../../api/services';
import { Vehicle, VehicleForm, Route, Device } from '../../../api/models';
import { RolesService } from '../../../shared/roles.service';
import { HttpErrorResponse } from '@angular/common/http';
import { map } from 'rxjs/operators/map';
import { QtSocketService } from '../../../shared/qt-socket.service';
import { Subscription } from 'rxjs';
import { SocketComponent } from '../../../shared/socket-component';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { DeviceChartComponent } from '../device-chart/device-chart.component';
import { NgxSpinnerService } from 'ngx-spinner';
import { ActivityLogsService } from '../../../shared/activity-logs.service';

@Component({
  selector: 'app-vehicles',
  templateUrl: './vehicles.component.html',
  styleUrls: ['./vehicles.component.css']
})
export class VehiclesComponent implements OnInit, AfterViewInit, SocketComponent {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;
  @ViewChild('listDevicesModal') public listDevicesModal: ModalDirective;
  @ViewChild('deviceChart') deviceChart: DeviceChartComponent;
  @ViewChild('infoSupervisorModal') public infoSupervisorModal: ModalDirective;

  public vehicles: Vehicle[];
  public vehicle: Vehicle;
  public vehicleCreate: VehicleForm;
  public vehicleUpdate: VehicleForm;
  public routes: Route[];
  public devices: any = [];
  public isCreated = false;
  public isUpdated = false;
  public isAssign = false;
  public selectedRouteId = 0;
  public vehicleId = 0;

  public user_down: any = null;

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;
  private socketSubscription: Subscription;
  public permissions:any[] = [];
  public search_license:any = '';

  public timeOutSearchVehicle;
  public isModuleGoods = false;

  public infoSupervisor:any = {
    supervisor_name: '',
    supervisor_station_from: '',
    supervisor_started: ''
  };

  constructor(
    public roles: RolesService,
    public apiRoutes: ManagerRoutesService,
    private apiVehicles: ManagerVehiclesService,
    private apiDevices: ManagerDevicesService,
    private qtSocket: QtSocketService,
    private router: Router,
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
    private apiModuleCompanies: ManagerModuleCompanyService,
    private activityLogs: ActivityLogsService
  ) {
    this.vehicle = new Vehicle();
    this.vehicleCreate = new VehicleForm();
    this.vehicleUpdate = new VehicleForm();
  }

  ngOnInit() {

    this.user_down = localStorage.getItem('token_shadow');

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  socketDown() {
    // console.log('clean up vehicle list socket');
    // this.socketSubscription.unsubscribe();
  }

  socketUp() {
    // this.socketSubscription = this.qtSocket.onData().subscribe(
    //   data => {
    //     console.log('from subscription: ', data.toString());
    //     if (this.addModal.show) {
    //       this.vehicleCreate.rfid = data.toString().split(':').pop();
    //     } else if (this.editModal.show) {
    //       this.vehicleUpdate.rfid = data.toString().split(':').pop();
    //     }
    //   }
    // );
  }

  ngAfterViewInit() {
    // this.socketUp();
    this.refreshView();
    //get module company
    this.apiModuleCompanies.listModuleCompany().subscribe(data => {
      data.forEach(element => {
        if(element['name'] === 'Module_VC_Hang_Hoa' ){
          this.isModuleGoods = true;
        }
      });
    })
  }

  refreshView() {
    this.spinner.show();
    this.apiVehicles.managerlistVehiclesResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {
        this.vehicles = resp.body;
        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
        this.spinner.hide();
      }
    );
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.refreshView();
  }

  showAddVehicleModal() {
    this.addModal.show();
  }

  showEditVehicleModal(id: number) {

    this.spinner.show();
    this.apiVehicles.managerGetVehicleById(id).subscribe(
      data => {
        this.vehicleUpdate.id = data.id;
        this.vehicleUpdate.license_plates = data.license_plates;
        this.vehicleUpdate.rfid = data.rfidcard.rfid;
        this.vehicleUpdate.device_imei = data.device_imei;
        this.vehicleUpdate.bluetooth_mac_add = data.bluetooth_mac_add;
        this.vehicleUpdate.bluetooth_pass = data.bluetooth_pass;

        if (data.route_id > 0) {
          this.selectedRouteId = data.route_id;
        } else {
          this.selectedRouteId = 0;
        }
        this.vehicleId = id;

        // get all router
        this.apiRoutes.managerlistRoutes({
          page: 1,
          limit: 999999
        }).subscribe(
          resp => {
            this.routes = resp;
          }
        );

        //get devices by isrunning
        this.apiDevices.managerListDevicesByIsRunning(0).subscribe( resp => {
          this.devices = resp;
        });

        this.spinner.hide();
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD')});
          }
        }
      }
    );


  }

  addVehicle() {
    if (!this.vehicleCreate.license_plates) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_LP'), 'warning');
      return;
    }

    if (!this.vehicleCreate.rfid) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_RFID'), 'warning');
      return;
    }

    this.isCreated = true;
    this.apiVehicles.managerCreateVehicle({
      license_plates: this.vehicleCreate.license_plates,
      rfid: this.vehicleCreate.rfid,
      bluetooth_mac_add: this.vehicleCreate.bluetooth_mac_add,
      bluetooth_pass: this.vehicleCreate.bluetooth_pass
    }).subscribe(
      res => {
        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'create';
        activity_log['subject_type'] = 'vehicle';
        activity_log['subject_data'] = this.vehicleCreate ? JSON.stringify({
          license_plates: this.vehicleCreate.license_plates,
          rfid: this.vehicleCreate.rfid,
          bluetooth_mac_add: this.vehicleCreate.bluetooth_mac_add,
          bluetooth_pass: this.vehicleCreate.bluetooth_pass
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.addModal.hide();
        this.vehicleCreate = new VehicleForm;
        this.isCreated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD')});
          }
        }
        this.isCreated = false;
      }
    );
  }

  editVehicle() {

    if (this.selectedRouteId <= 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_ROU'), 'warning');
      return;
    }

    if (this.vehicleId <= 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_VHC'), 'warning');
      return;
    }

    if (!this.vehicleUpdate.license_plates) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_LP'), 'warning');
      return;
    }

    if (!this.vehicleUpdate.rfid) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_RFID'), 'warning');
      return;
    }

    // this.isAssign = true;
    // this.apiVehicles.managerVehicleAssignRoute({
    //   id: this.vehicleId,
    //   route_id: this.selectedRouteId
    // }).subscribe(
    //   data => {
    //     this.listRoutesModal.hide();
    //     this.selectedRouteId = 0;
    //     this.vehicleId = 0;
    //     this.isAssign = false;
    //     this.refreshView();
    //     swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
    //   },
    //   err => {
    //     if (err instanceof HttpErrorResponse) {
    //       if (err.status === 404) {
    //         swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
    //       } else if (err.status === 422) {
    //         swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
    //       }
    //     }
    //     this.isAssign = false;
    //   }
    // );


    this.isUpdated = true;
    this.apiVehicles.managerUpdateVehicle({
      id: this.vehicleUpdate.id,
      license_plates: this.vehicleUpdate.license_plates,
      rfid: this.vehicleUpdate.rfid,
      route_id: this.selectedRouteId,
      device_imei: this.vehicleUpdate.device_imei,
      bluetooth_mac_add: this.vehicleUpdate.bluetooth_mac_add,
      bluetooth_pass: this.vehicleUpdate.bluetooth_pass
    }).subscribe(
      data => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'update';
        activity_log['subject_type'] = 'vehicle';
        activity_log['subject_data'] = this.vehicleUpdate ? JSON.stringify({
          id: this.vehicleUpdate.id,
          license_plates: this.vehicleUpdate.license_plates,
          rfid: this.vehicleUpdate.rfid,
          route_id: this.selectedRouteId,
          device_imei: this.vehicleUpdate.device_imei,
          bluetooth_mac_add: this.vehicleUpdate.bluetooth_mac_add,
          bluetooth_pass: this.vehicleUpdate.bluetooth_pass
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.editModal.hide();
        this.vehicleUpdate = new VehicleForm;
        this.isUpdated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
          }
        }
        this.isUpdated = false;
      }
    );
  }

  // updateDeviceForVehicle(){

  //   if (!this.vehicleUpdate.device_imei) {
  //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_DEVICE_IMEI'), 'warning');
  //     return;
  //   }

  //   this.isAssign = true;
  //   this.apiVehicles.managerUpdateVehicle({
  //     id:  this.vehicleUpdate.id,
  //     device_imei: this.vehicleUpdate.device_imei,
  //     update_type: 'assign'
  //   }).subscribe(resp => {
  //     this.listDevicesModal.hide();
  //     this.vehicleUpdate = new VehicleForm;
  //     this.isAssign = false;
  //     this.refreshView();
  //     swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
  //   },
  //   err => {
  //     if (err instanceof HttpErrorResponse) {
  //       if (err.status === 404) {
  //         swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
  //       } else if (err.status === 422) {
  //         swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
  //       }
  //     }
  //     this.isUpdated = false;
  //   });
  // }

  deleteVehicle(id: number) {
    swal({
      title: this.translate.instant('SWAL_ERROR_SURE'),
      text: this.translate.instant('SWAL_ERROR_REMOVE'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: this.translate.instant('SWAL_OK'),
      cancelButtonText: this.translate.instant('SWAL_CANCEL')
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.apiVehicles.managerDeleteVehicle(id).subscribe(
          res => {

            //call service create activity log
            var activity_log: any = [];
            activity_log['user_down'] =  this.user_down ? this.user_down : null;
            activity_log['action'] = 'delete';
            activity_log['subject_type'] = 'vehicle';
            activity_log['subject_data'] = JSON.stringify({id: id});
            var activityLog = this.activityLogs.createActivityLog(activity_log);

            this.refreshView();
            this.spinner.hide();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            this.spinner.hide();
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
          }
        );
      }
    });
  }

  // showRoutesModal(vehicleid: number) {
  //   this.spinner.show();
  //   // get vehicle by id
  //   this.apiVehicles.managerGetVehicleById(vehicleid).subscribe(
  //     data => {
  //       if (data.route_id > 0) {
  //         this.selectedRouteId = data.route_id;
  //       } else {
  //         this.selectedRouteId = 0;
  //       }
  //       this.vehicleId = vehicleid;
  //     }
  //   );

  //   // get all router
  //   this.apiRoutes.managerlistRoutes({
  //     page: 1,
  //     limit: 999999
  //   }).subscribe(
  //     resp => {
  //       this.routes = resp;
  //       this.spinner.hide();
  //       this.listRoutesModal.show();
  //     },
  //     err => {
  //       this.spinner.hide();
  //     }
  //   );
  // }

  // updateRouteForVehicle() {

  //   if (this.selectedRouteId <= 0) {
  //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_ROU'), 'warning');
  //     return;
  //   }

  //   if (this.vehicleId <= 0) {
  //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_VHC'), 'warning');
  //     return;
  //   }

  //   this.isAssign = true;
  //   this.apiVehicles.managerVehicleAssignRoute({
  //     id: this.vehicleId,
  //     route_id: this.selectedRouteId
  //   }).subscribe(
  //     data => {
  //       this.listRoutesModal.hide();
  //       this.selectedRouteId = 0;
  //       this.vehicleId = 0;
  //       this.isAssign = false;
  //       this.refreshView();
  //       swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
  //     },
  //     err => {
  //       if (err instanceof HttpErrorResponse) {
  //         if (err.status === 404) {
  //           swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
  //         } else if (err.status === 422) {
  //           swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
  //         }
  //       }
  //       this.isAssign = false;
  //     }
  //   );
  // }

  gotoMap(id: number) {
    this.router.navigate(['/manager/dashboard'], {queryParams: {idDevice: id}, skipLocationChange: true});
  }

  getInputLicense() {
    clearTimeout(this.timeOutSearchVehicle);
    this.timeOutSearchVehicle = setTimeout(()=>{
      let params = {
        license: this.search_license
      };
      if( this.search_license != ''){
        this.apiVehicles.managerVehicleSearch(params).subscribe(
          res => {
            this.vehicles = res;
          }
        );
      }else{
        this.refreshView();
      }
    },500);
  }

  showInfoSupervisor(data: any){
    this.infoSupervisorModal.show();
    this.infoSupervisor = data;
  }
}
