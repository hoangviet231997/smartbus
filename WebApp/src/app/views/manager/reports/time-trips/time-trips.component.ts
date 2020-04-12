import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { ManagerReportsService, ManagerCompaniesService, ManagerUsersService, ManagerRoutesService, ManagerTicketTypesService } from '../../../../api/services';
import { Route, User } from '../../../../api/models';

import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import swal from 'sweetalert2';
import { saveAs } from 'file-saver/FileSaver';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { NgxSpinnerService } from 'ngx-spinner';
@Component({
  selector: 'app-time-trips',
  templateUrl: './time-trips.component.html',
  styleUrls: ['./time-trips.component.css']
})
export class TimeTripsComponent implements OnInit , AfterViewInit {

  @ViewChild('listUserModal') public listUserModal: ModalDirective;
  @ViewChild('listDataDetailModal') public listDataDetailModal: ModalDirective;

  public bsRangeValue: Date[];
  public bsRangeValues: any = null;
  public maxDate: Date;

  public isExport = false;
  public isLoading =  false;
  public data_shift: any = null;
  public tmpIdTrip: any = null;
  public routeNameTrip: any = null;
  public data_export: any = [];
  public dataExport:any = null;

  public company: any;
  public daysForm: string;
  public monthForm: string;
  public yearsForm: string;
  public daysTo: string;
  public monthTo: string;
  public yearsTo: string;
  public routes: Route[];

  public selectedRouteId: any = 0;
  public selectedPosition: any = 'all';
  public selecteUserId: any = 0;

  public from_date = null;
  public to_date = null;
  public selectedType: any = 0;

  public users: User[];
  public searchUserName = 'Tất cả';
  public inputUserName = '';
  public staffs: any = [];
  public dataTimeTrips: any = [];
  public detailDataShift = [];

  public routeName: any;
  public isCheckModuleApp: any;
  public data_total_driver: any ;
  public data_total_subdriver: any ;
  public driver = true;

  public permissions:any = [];
  public is_user_login: any = false;

  constructor(
    private translate: TranslateService,
    private apiReports: ManagerReportsService,
    private apiCompanies: ManagerCompaniesService,
    private spinner: NgxSpinnerService,
    private apiUsers: ManagerUsersService,
    private apiRoutes: ManagerRoutesService,
  ) {
    this.maxDate = new Date();
  }

  ngOnInit() {

    if (localStorage.getItem('user')) {
      var user = JSON.parse(localStorage.getItem('user'));
      this.permissions = user.permissions;
      if(user.role.name === 'driver' || user.role.name === 'subdriver') {
        this.is_user_login = true;
        this.selectedType = 1;
        if(user.role.name === 'driver') this.selectedPosition = 'driver';
        if(user.role.name === 'subdriver') this.selectedPosition = 'subdriver';
        this.searchUserName = user.fullname;
        this.selecteUserId = user.id;
      }
    }
  }

  ngAfterViewInit() {
    this.getComapny();
    this.getRoutes();

    if (localStorage.getItem('user')) {
      var user = JSON.parse(localStorage.getItem('user'));
      this.permissions = user.permissions;
      if(user.role.name === 'driver' || user.role.name === 'subdriver') {
        this.is_user_login = true;
        this.selectedType = 1;
        if(user.role.name === 'driver') this.selectedPosition = 'driver';
        if(user.role.name === 'subdriver') this.selectedPosition = 'subdriver';
        this.searchUserName = user.fullname;
        this.selecteUserId = user.id;
      }
    }
  }

  getComapny() {

    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.company = data;
      }
    );
  }

  getRoutes() {

    this.apiRoutes.managerlistRoutesResponse({
      page: 1,
      limit: 99999
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {
        this.routes = resp.body;
      }
    );
  }

  getUsers() {

    // get users
    this.apiUsers.managerListUsersResponse({
      page: 1,
      limit: 99999
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {
        this.inputUserName = '';
        this.users = resp.body.filter(
          (user) => {

            if(this.selectedPosition == 'all'){
             if (user.role.name === 'driver' || user.role.name === 'subdriver') {
                return user;
              }
            }

            if(this.selectedPosition == 'driver'){
             if (user.role.name === 'driver') {
                return user;
              }
            }

            if(this.selectedPosition == 'subdriver'){
             if (user.role.name === 'subdriver') {
                return user;
              }
            }
        });
      }
    );
  }

  getData(type = null) {

    this.isLoading = true;
    this.dataExport = null;
    this.dataTimeTrips = [];

    if (!this.bsRangeValue) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('STAFF_ERROR_DATE'), 'warning');
      return;
    }

    if(!this.is_user_login){
      if(this.selectedPosition == 'all' && type == 'role'){
        this.selecteUserId = 0;
        this.searchUserName = 'Tất cả';
      }

      if((this.selectedPosition == 'driver' || this.selectedPosition == 'subdriver') && type == 'role'){
        this.selecteUserId = 0;
        this.searchUserName = 'Tất cả';
        this.dataTimeTrips = [];
      }
    }

    if (this.selectedRouteId == 0) {
      this.routeName = [{name: this.translate.instant('BTN_VIEW_RECEIPT')}];
    } else {
      this.routeName = this.routes.filter(item => item.id == this.selectedRouteId);
    }

    this.daysForm = moment(this.bsRangeValue[0]).format('DD').toString();
    this.monthForm = moment(this.bsRangeValue[0]).format('MM').toString();
    this.yearsForm = moment(this.bsRangeValue[0]).format('YYYY').toString();

    this.daysTo = moment(this.bsRangeValue[1]).format('DD').toString();
    this.monthTo = moment(this.bsRangeValue[1]).format('MM').toString();
    this.yearsTo = moment(this.bsRangeValue[1]).format('YYYY').toString();

    this.spinner.show();
    this.apiReports.managerReportsViewTrip({
      to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
      user_id: this.selecteUserId,
      route_id: this.selectedRouteId,
      type_opt: this.selectedType,
      position: this.selectedPosition
    }).subscribe( resp => {
      this.dataTimeTrips = resp;
      this.spinner.hide();
    });
  }

  OnChangeView() {

    this.isLoading = false;
    this.bsRangeValue = null;
    this.bsRangeValues = null;
    this.searchUserName = 'Tất cả';
    this.selectedPosition = 'all';
    this.selectedRouteId = 0;
    this.selecteUserId = 0;
    this.dataTimeTrips = [];
  }

  showListUserModal() {
    this.getUsers();
    this.listUserModal.show();
  }

  showlistDataDetailModal(items:any, idTmpTrip: number, routeName: string) {

    this.detailDataShift = items;
    this.tmpIdTrip = idTmpTrip;
    this.routeNameTrip = routeName;
    this.listDataDetailModal.show();
  }

  chooseUser(id: number) {
    this.users.map(
      (user) => {
        if (id === 0) {
          this.searchUserName = this.translate.instant('BTN_VIEW_RECEIPT');
          this.getData(null);
          this.listUserModal.hide();
        } else if (user.id === id) {
          this.searchUserName = user.fullname;
          this.selecteUserId = user.id;
          this.getData(null);
          this.listUserModal.hide();
        }
    });
  }

  exportFile() {

    this.isExport = true;
    if (this.dataExport != null) {

      this.isExport = false;

      // get filename
      const contentDispositionHeader: string = this.dataExport.headers.get('Content-Disposition');
      const parts: string[] = contentDispositionHeader.split(';');
      const filename = parts[1].split('=')[1];

      // convert data
      const byteCharacters = atob(this.dataExport.body);
      const byteNumbers = new Array(byteCharacters.length);
      for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
      }
      const byteArray = new Uint8Array(byteNumbers);
      const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel' });

      saveAs(blob, filename);

    } else {

      // export
      this.apiReports.managerReportsExportTripResponse({

        to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
        from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
        user_id: this.selecteUserId,
        route_id: this.selectedRouteId,
        type_opt: this.selectedType,
        position: this.selectedPosition

      }).pipe(
        map(_r => {
          return _r;
        })
      ).subscribe(
        data => {
          this.isExport = false;
          this.dataExport = data;

          // get filename
          const contentDispositionHeader: string = data.headers.get('Content-Disposition');
          const parts: string[] = contentDispositionHeader.split(';');
          const filename = parts[1].split('=')[1];

          // convert data
          const byteCharacters = atob(data.body);
          const byteNumbers = new Array(byteCharacters.length);
          for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
          }
          const byteArray = new Uint8Array(byteNumbers);
          const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel' });

          saveAs(blob, filename);
        },
        err => {
          this.isExport = false;
        }
      );
    }
  }

  exportFileModal() {

    this.isExport = true;

    // export
    this.apiReports.managerReportsExportTripTimeDetailResponse({

      to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
      user_id: this.selecteUserId,
      route_id: this.selectedRouteId,
      type_opt: this.selectedType,
      position: this.selectedPosition,
      id_tmp: this.tmpIdTrip,
      route_name_tmp: this.routeNameTrip
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      data => {
        this.isExport = false;
        // get filename
        const contentDispositionHeader: string = data.headers.get('Content-Disposition');
        const parts: string[] = contentDispositionHeader.split(';');
        const filename = parts[1].split('=')[1];

        // convert data
        const byteCharacters = atob(data.body);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
          byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel'});

        saveAs(blob, filename);
      },
      err => {
        this.isExport = false;
      }
    );

  }

  exportFileTimeKeeping() {

    if (this.bsRangeValues === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.bsRangeValues === null) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    this.isExport = true;

    // export
    this.apiReports.managerReportsExportTimeKeepingResponse({
      to_date: moment(this.bsRangeValues).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValues).format('YYYY-MM-DD'),
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      data => {
        this.data_export = atob(data.body);
        this.isExport = false;
        // get filename
        const contentDispositionHeader: string = data.headers.get('Content-Disposition');
        const parts: string[] = contentDispositionHeader.split(';');
        const filename = parts[1].split('=')[1];

        // convert data
        const byteCharacters = atob(data.body);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
          byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel' });

        saveAs(blob, filename);
      },
      err => {
        this.isExport = false;
      }
    );
  }

  showPrintPreviewSummary() {

    let printContents, popupWin;
    printContents = document.getElementById('print-section').innerHTML;
    popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
    popupWin.document.open();
    popupWin.document.write(`
      <html>
        <head>
          <title></title>
          <style>
          @page { size: A4; }
          .tx-center{text-align: center}
          .tx-left{text-align: left}
          .tx-right{text-align: right}
          .tx-bold{font-weight: bold}
          .tx-10{font-size: 15px; font-family: 'Times New Roman';}
          .tx-11{font-size: 12px; font-family: 'Times New Roman';}
          .tx-12{font-size: 20px; font-family: 'Times New Roman';}
          .w-10{width: 10cm}
          .w-3{width: 3cm;float:left}
          .fl{float:left}
          .fr{float:right}
          .w-4{width: 4cm}
          .w-2{width: 1.5cm}
          .pt-0{margin-top: 0}
          </style>
        </head>
        <body onload="window.print();window.close()">` + printContents + `</body>
      </html>`
    );
    popupWin.document.close();
  }

  showPrintModal() {
    let printContents, popupWin;
    printContents = document.getElementById('print-modal').innerHTML;
    popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
    popupWin.document.open();
    popupWin.document.write(`
      <html>
        <head>
          <title></title>
          <style>
          @page { size: A4; }
          .tx-center{text-align: center}
          .tx-left{text-align: left}
          .tx-right{text-align: right}
          .tx-bold{font-weight: bold}
          .tx-10{font-size: 15px; font-family: 'Times New Roman';}
          .tx-11{font-size: 12px; font-family: 'Times New Roman';}
          .tx-12{font-size: 20px; font-family: 'Times New Roman';}
          .w-10{width: 10cm}
          .w-3{width: 3cm;float:left}
          .fl{float:left}
          .fr{float:right}
          .w-4{width: 4cm}
          .w-2{width: 1.5cm}
          .pt-0{margin-top: 0}
          </style>
        </head>
        <body onload="window.print();window.close()">` + printContents + `</body>
      </html>`
    );
    popupWin.document.close();
  }
}
