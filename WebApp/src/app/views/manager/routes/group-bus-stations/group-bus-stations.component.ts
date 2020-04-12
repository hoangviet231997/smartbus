import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap';
import swal from 'sweetalert2';
import { Router } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import { MouseEvent } from '@agm/core';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { GroupBusStationForm } from '../../../../api/models';
import { ManagerBusStationsService, ManagerRoutesService, ManagerTicketTypesService, ManagerModuleCompanyService } from '../../../../api/services';
import { ActivityLogsService } from '../../../../shared/activity-logs.service';

@Component({
  selector: 'app-group-bus-stations',
  templateUrl: './group-bus-stations.component.html',
  styleUrls: ['./group-bus-stations.component.css'],
})
export class GroupBusStationsComponent implements OnInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public groupBusStations:any = [];
  public routes: any = [];
  public tmpBusStation: any = [];
  public ticketTypes: any = [];
  public groupBusStationCreate: GroupBusStationForm;
  public groupBusStationUpdate: GroupBusStationForm;

  public isModuleCardMonthChargeLimit = false;
  public isModuleCardPrepaidKm = false;
  public isModuleCar = false;

  public isCheckDirectionInTurnCr = false;
  public isCheckDirectionTurnOnCr = false;

  public isCheckDirectionInTurnUp = false;
  public isCheckDirectionTurnOnUp = false;

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  public searchGroupBusStation:any = '';
  public timeoutSearchGroupBusStation;

  public user_down: any = null;

  constructor(
    private apiGroupBusStations: ManagerBusStationsService,
    private apiModuleCompanies: ManagerModuleCompanyService,
    private apiTicketTypes: ManagerTicketTypesService,
    private apiRoutes: ManagerRoutesService,
    private activityLogs: ActivityLogsService,
    private translate: TranslateService,
    private spinner: NgxSpinnerService
  ){
    this.groupBusStationCreate = new GroupBusStationForm;
    this.groupBusStationUpdate = new GroupBusStationForm;
    this.groupBusStationCreate.type = "month";
  }

  ngOnInit() {
    this.user_down = localStorage.getItem('token_shadow');
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  getDataGroupBusStation(){
    this.spinner.show();
    this.apiGroupBusStations.managerlistGroupBusStationResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).subscribe(data => {
      this.groupBusStations = data.body;
      this.paginationTotal = data.headers.get('pagination-total');
      this.paginationCurrent = data.headers.get('pagination-current');
      this.paginationLast = data.headers.get('pagination-last');
      this.spinner.hide();
    });
  }

  getDataRoutes(){
    //get routes
    this.spinner.show();
    this.apiRoutes.managerGetRoutesBusStions().subscribe(
      resp => {
        this.routes = resp;
        this.routes.map(element => {
          var arr = [];
          element.bus_stations.forEach(e => {
            if (e.direction == 0) {
              arr.push(e);
            }
          });
          element.bus_stations = arr;
        });
        this.spinner.hide();
      }
    );
  }

  getDataModule(){
    //get module company
    this.apiModuleCompanies.listModuleCompany().subscribe(data => {
      data.forEach(element => {
        if (element['name'] === 'Module_TT_SL_Quet') this.isModuleCardMonthChargeLimit = true;
        if (element['name'] === 'Module_Xe_Khach') this.isModuleCar = true;
        if (element['name'] === 'Module_TTT_Km') this.isModuleCardPrepaidKm = true;
      });
    })

    setTimeout(() => {
      //get price
      let type_ticket_type = this.isModuleCar ? 0 : 1;
      this.apiTicketTypes.managerListTicketTypesByTypeParam(type_ticket_type).subscribe(data => {
        this.ticketTypes = data;
      });
    }, 1000);
  }

  refreshView() {

    this.searchGroupBusStation = '';
    this.getDataGroupBusStation()
    this.getDataRoutes();
    this.getDataModule();
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.getDataGroupBusStation();
  }

  changeCheckDirection(value, type) {

    if (type === 0) {
      if (value === 0) {
        this.isCheckDirectionInTurnCr = true;
        this.isCheckDirectionTurnOnCr = false;
      }

      if (value === 1) {
        this.isCheckDirectionTurnOnCr = true;
        this.isCheckDirectionInTurnCr = false;
      }
      this.groupBusStationCreate.direction = value;
    }

    if (type === 1) {

      if (value === 0) {
        this.isCheckDirectionInTurnUp = true;
        this.isCheckDirectionTurnOnUp = false;
      }

      if (value === 1) {
        this.isCheckDirectionTurnOnUp = true;
        this.isCheckDirectionInTurnUp = false;
      }
      this.groupBusStationUpdate.direction = value;
    }
  }

  showAddGroupCompaniesModal() {
    this.tmpBusStation = [];
    this.groupBusStationCreate.direction = 0;
    this.isCheckDirectionTurnOnCr = false;
    this.isCheckDirectionInTurnCr = true;
    this.groupBusStationCreate.parent_gr_bus_station_id = null;
    this.groupBusStationCreate.type = "month";
    this.groupBusStationCreate.color = null;
    this.addModal.show();
  }

  changeCheckedBusStation(event, busStation) {
    if (event.currentTarget.checked) {
      this.tmpBusStation.push(busStation.id);
    } else {
      const index: number = this.tmpBusStation.indexOf(busStation.id);
      if (index !== -1) {
        this.tmpBusStation.splice(index, 1);
      }
    }
  }

  changeSelectTypeGroupBusStation(){

  }

  addGroupBusStation() {

    if(!this.groupBusStationCreate.type){
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_GROUP_BUS_STATION_TYPE'), 'warning');
      return;
    }

    if (!this.groupBusStationCreate.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_GROUP_BUS_STATION_NAME'), 'warning');
      return;
    }

    if (this.tmpBusStation.length < 2) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_GROUP_BUS_STATION_STATION_ONLY'), 'warning');
      return;
    }

    if(this.groupBusStationCreate.type === "month" || this.groupBusStationCreate.type === "ticket_pos"){
      if (!this.groupBusStationCreate.ticket_price_id) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_GROUP_BUS_STATION_TICKET_PRICE'), 'warning');
        return;
      }
    }

    if (this.isModuleCar) {
      if (!this.groupBusStationCreate.parent_gr_bus_station_id) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_GROUP_BUS_STATION_PARENT_GR_BUS_STATAION_ID'), 'warning');
        return;
      }
    }

    this.spinner.show();
    this.apiGroupBusStations.manmagerCreateGroupBusStation({
      name: this.groupBusStationCreate.name,
      bus_stations: this.tmpBusStation,
      ticket_price_id: this.groupBusStationCreate.ticket_price_id,
      direction: this.groupBusStationCreate.direction,
      parent_gr_bus_station_id: this.groupBusStationCreate.parent_gr_bus_station_id,
      type: (this.groupBusStationCreate.type !== '') ? this.groupBusStationCreate.type : null,
      color: this.groupBusStationCreate.color ? this.groupBusStationCreate.color : null
    }).subscribe(
      res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'create';
        activity_log['subject_type'] = 'group_bus_station';
        activity_log['subject_data'] = this.groupBusStationCreate ? JSON.stringify({
          name: this.groupBusStationCreate.name,
          bus_stations: this.tmpBusStation,
          ticket_price_id: this.groupBusStationCreate.ticket_price_id,
          direction: this.groupBusStationCreate.direction,
          parent_gr_bus_station_id: this.groupBusStationCreate.parent_gr_bus_station_id,
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.addModal.hide();
        this.groupBusStationCreate = new GroupBusStationForm();
        this.refreshView();
        this.spinner.hide();
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          } else if (err.status === 422) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD') });
          }
        }
      }
    );
  }

  showGetGroupBusStationById(id) {
    this.tmpBusStation = [];
    this.spinner.show();
    this.editModal.show();
    this.apiGroupBusStations.managerGetGroupBusStationById(id).subscribe(
      res => {
        this.groupBusStationUpdate.name = res.name;
        this.groupBusStationUpdate.id = res.id;
        this.groupBusStationUpdate.ticket_price_id = res.ticket_price_id;
        this.groupBusStationUpdate.direction = res.direction;
        this.groupBusStationUpdate.type = res.type;
        this.groupBusStationUpdate.color = res.color
        // if(res.color == null) this.groupBusStationUpdate.color = '#00b297';

        if (this.groupBusStationUpdate.direction === 0) {
          this.isCheckDirectionInTurnUp = true;
          this.isCheckDirectionTurnOnUp = false;
        }

        if (this.groupBusStationUpdate.direction === 1) {
          this.isCheckDirectionTurnOnUp = true;
          this.isCheckDirectionInTurnUp = false;
        }

        this.groupBusStationUpdate.parent_gr_bus_station_id = res.parent_gr_bus_station_id;
        if (res.bus_stations) {
          this.tmpBusStation = JSON.parse(res.bus_stations)
        }
        this.spinner.hide();
      }
    );
  }

  editGroupBusStation() {

    if(!this.groupBusStationUpdate.type){
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_GROUP_BUS_STATION_TYPE'), 'warning');
      return;
    }

    if (!this.groupBusStationUpdate.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_GROUP_BUS_STATION_NAME'), 'warning');
      return;
    }

    if (this.tmpBusStation.length < 2) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_GROUP_BUS_STATION_STATION_ONLY'), 'warning');
      return;
    }
    if(this.groupBusStationUpdate.type === "month" || this.groupBusStationUpdate.type === "ticket_pos"){
      if (!this.groupBusStationUpdate.ticket_price_id) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_GROUP_BUS_STATION_TICKET_PRICE'), 'warning');
        return;
      }
    }

    if (this.isModuleCar) {
      if (!this.groupBusStationUpdate.parent_gr_bus_station_id) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_GROUP_BUS_STATION_PARENT_GR_BUS_STATAION_ID'), 'warning');
        return;
      }
    }

    this.spinner.show();
    this.apiGroupBusStations.manmagerUpdateGroupBusStation({
      name: this.groupBusStationUpdate.name,
      bus_stations: this.tmpBusStation,
      id: this.groupBusStationUpdate.id,
      ticket_price_id: this.groupBusStationUpdate.ticket_price_id,
      direction: this.groupBusStationUpdate.direction,
      parent_gr_bus_station_id: this.groupBusStationUpdate.parent_gr_bus_station_id,
      type: (this.groupBusStationUpdate.type !== '') ? this.groupBusStationUpdate.type : null,
      color: this.groupBusStationUpdate.color ? this.groupBusStationUpdate.color : null
    }).subscribe(
      res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'update';
        activity_log['subject_type'] = 'group_bus_station';
        activity_log['subject_data'] = this.groupBusStationUpdate ? JSON.stringify({
          name: this.groupBusStationUpdate.name,
          bus_stations: this.tmpBusStation,
          id: this.groupBusStationUpdate.id,
          ticket_price_id: this.groupBusStationUpdate.ticket_price_id,
          direction: this.groupBusStationUpdate.direction,
          parent_gr_bus_station_id: this.groupBusStationUpdate.parent_gr_bus_station_id
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.editModal.hide();
        this.groupBusStationUpdate = new GroupBusStationForm();
        this.refreshView();
        this.spinner.hide();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          } else if (err.status === 422) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD') });
          }
        }
      }
    );
  }

  deleteGroupBusStation(id: number) {
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
        this.apiGroupBusStations.managerDeleteGroupBusStationById(id).subscribe(
          res => {

            //call service create activity log
            var activity_log: any = [];
            activity_log['user_down'] =  this.user_down ? this.user_down : null;
            activity_log['action'] = 'delete';
            activity_log['subject_type'] = 'group_bus_station';
            activity_log['subject_data'] = id ? JSON.stringify({
              id: id
            }) : '';
            var activityLog = this.activityLogs.createActivityLog(activity_log);

            this.refreshView();
            this.spinner.hide();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            this.spinner.hide();
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD') });
          }
        );
      }
    });
  }

  getInputGroupBusStation() {

    clearTimeout(this.timeoutSearchGroupBusStation);
    this.timeoutSearchGroupBusStation = setTimeout(() => {
      if (this.searchGroupBusStation !== '') {
        this.apiGroupBusStations.managerSearchGroupBusStation({
          name: this.searchGroupBusStation
        }).subscribe((res) => {
          this.groupBusStations = res;
        });
      } else {
        this.refreshView();
      }
    }, 500);
  }
}
