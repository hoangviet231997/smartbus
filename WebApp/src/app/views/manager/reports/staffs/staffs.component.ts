import { Component, OnInit, AfterViewInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { ManagerRoutesService, ManagerReportsService, ManagerCompaniesService } from '../../../../api/services';
import { Route,StaffView,RpStaffForm } from '../../../../api/models';
import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import swal from 'sweetalert2';
import { saveAs } from 'file-saver/FileSaver';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-staffs',
  templateUrl: './staffs.component.html',
  styleUrls: ['./staffs.component.css']
})
export class StaffsComponent implements OnInit, AfterViewInit {

  public bsRangeValue: Date[];
  public maxDate: Date;
  public routes: Route[];
  public selectedRouteId = null;
  public selectedPosition = '';

  public isCollected = false;
  public isExport = false;
  public isLoading = false;
  public dataExport:any = null;

  public staffs: any = [];
  public isCheckModuleApp: any;

  public company: any;
  public daysForm: string;
  public monthForm: string;
  public yearsForm: string;
  public daysTo: string;
  public monthTo: string;
  public yearsTo: string;
  public routeName: any;
  public role_name: any;
  public key_role: any;

  public data_total_only: any ;
  public data_total_driver: any ;
  public data_total_subdriver: any ;

  public permissions: any = [];
  public user_login: any = {
    "role_name": 0,
    "id": 0
  };
  public is_user_login: any = false;

  constructor(
    private apiRoutes: ManagerRoutesService,
    private apiReports: ManagerReportsService,
    private translate: TranslateService,
    private apiCompanies: ManagerCompaniesService,
    private spinner : NgxSpinnerService)
  {
    this.maxDate = new Date();
  }

  ngOnInit() {
    if (localStorage.getItem('user')) {
      let user = JSON.parse(localStorage.getItem('user'));
      this.permissions = user.permissions;
      if(user.role.name === 'driver' || user.role.name === 'subdriver') {
        this.user_login.role_name = user.role.name;
        this.user_login.id = user.id;
        this.is_user_login = true;
        if(this.user_login.role_name === 'driver') this.selectedPosition = 'driver';
        if(this.user_login.role_name === 'subdriver') this.selectedPosition = 'subdriver';
      }
    }
  }

  ngAfterViewInit() {
    this.refreshView();
    if (localStorage.getItem('user')) {
      let user = JSON.parse(localStorage.getItem('user'));
      this.permissions = user.permissions;
      if(user.role.name === 'driver' || user.role.name === 'subdriver') {
        this.user_login.role_name = user.role.name;
        this.user_login.id = user.id;
        this.is_user_login = true;
        if(this.user_login.role_name === 'driver') this.selectedPosition = 'driver';
        if(this.user_login.role_name === 'subdriver') this.selectedPosition = 'subdriver';
      }
    }
  }

  refreshView() {
    this.getComapny();
    this.getListRoutes();
  }

  getListRoutes() {
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

  getComapny() {
    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.company = data;
      }
    );
  }

  getDataStaff(){
    //set isLoading = true
    this.dataExport = null;

    
    

    if (this.bsRangeValue === undefined) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('STAFF_ERROR_DATE'), 'warning');
      return;
    }

    if (this.bsRangeValue.length !== 2) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('STAFF_ERROR_DATE'), 'warning');
      return;
    }

    if(!this.is_user_login){
      if (this.selectedPosition === '') {
        this.isLoading = false;
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('STAFF_ERROR_ROLE'), 'warning');
        return;
      }
    }

    if (!this.selectedRouteId) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('STAFF_ERROR_ROUTE'), 'warning');
      return;
    }

    this.daysForm = moment(this.bsRangeValue[0]).format('DD').toString();
    this.monthForm = moment(this.bsRangeValue[0]).format('MM').toString();
    this.yearsForm = moment(this.bsRangeValue[0]).format('YYYY').toString();

    this.daysTo = moment(this.bsRangeValue[1]).format('DD').toString();
    this.monthTo = moment(this.bsRangeValue[1]).format('MM').toString();
    this.yearsTo = moment(this.bsRangeValue[1]).format('YYYY').toString();

    if (this.selectedRouteId == 0) {
      this.routeName = [{ name: this.translate.instant('ROUTE_ALL') }];
    } else {
      this.routeName = this.routes.filter(item => item.id == this.selectedRouteId);
    }
    this.isLoading = true;
    this.spinner.show();

    //get api view staff
    this.apiReports.managerReportsViewStaff({
      to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
      route_id: this.selectedRouteId,
      position: this.selectedPosition,
      user_id: this.user_login.id
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe( data => {

      this.staffs = data['staffs_arr'];
      this.isCheckModuleApp = data['isCheckModuleApp'];
      this.data_total_only = data['data_total_only'];
      this.data_total_driver = data['data_total_driver'];
      this.data_total_subdriver = data['data_total_subdriver'];

      this.spinner.hide();
    });
  }

  exportFile() {
    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.bsRangeValue.length !== 2) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if(!this.is_user_login){
      if (this.selectedPosition === '') {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('STAFF_ERROR_ROLE'), 'warning');
        return;
      }
    }

    if (!this.selectedRouteId) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_STAFF_ROUTE'), 'warning');
      return;
    }

    this.isExport = true;
    if(this.dataExport != null){

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
      const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel'});

      saveAs(blob, filename);

    }else{
      // export
      this.apiReports.managerReportsExportStaffResponse({
        to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
        from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
        route_id: this.selectedRouteId,
        position: this.selectedPosition
      }).pipe(
        map(_r => {
          return _r;
        })
      ).subscribe(
        data => {

          this.dataExport = data;
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
  }

  showPrintPreview(){
    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.bsRangeValue.length !== 2) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.selectedPosition.length === 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_STAFF_POSITION'), 'warning');
      return;
    }

    if (!this.selectedRouteId) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_STAFF_ROUTE'), 'warning');
      return;
    }
    let printContents, popupWin;
    printContents = document.getElementById('print-section').innerHTML;
    popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
    popupWin.document.open();
    if(this.isCheckModuleApp){
      popupWin.document.write(`
      <html>
        <head>
          <title></title>
          <style>
          @page { size: A4; size: landscape }
          .tx-center{text-align: center}
          .tx-left{text-align: left}
          .tx-right{text-align: right}
          .tx-bold{font-weight: 700;}
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
        <body onload="window.print();window.close()">`+ printContents +`</body>
      </html>`
    );
    }else{
      popupWin.document.write(`
      <html>
        <head>
          <title></title>
          <style>
          @page { size: A4; }
          .tx-center{text-align: center}
          .tx-left{text-align: left}
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
        <body onload="window.print();window.close()">`+ printContents +`</body>
      </html>`
    );
    }
    popupWin.document.close();

  }
}
