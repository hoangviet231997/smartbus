import { Component, OnInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { ManagerReportsService, ManagerCompaniesService,ManagerRoutesService } from '../../../../api/services';
import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import swal from 'sweetalert2';
import { saveAs } from 'file-saver/FileSaver';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-dailies',
  templateUrl: './dailies.component.html',
  styleUrls: ['./dailies.component.css']
})
export class DailiesComponent implements OnInit {


  public selectedRouteId: any = null;
  public bsRangeValue: any;
  public maxDate: Date;

  public routes: any = [];
  public routeName: any;

  public route_group_debt_all = [];
  public route_group_debt_any = [];
  public route_detail = [];
  public total_collecter = [];
  public shift_now_yesterday_result = [];
  public route_group_colected_any = [];
  public route_group_colected_all = [];
  public shift_now_yesterday_all = [];

  public isCheckModuleApp: any;

  public isExport = false;
  public isLoading = false;
  public isCollected = false;
  public dataExport:any = null;

  public company: any;
  public daysForm: string;
  public monthForm: string;
  public yearsForm: string;
  public daysTo: string;
  public monthTo: string;
  public yearsTo: string;

  public permissions:any = [];

  constructor(
    private translate: TranslateService,
    private apiReports: ManagerReportsService,
    private apiRoutes: ManagerRoutesService,
    private spinner : NgxSpinnerService,
    private apiCompanies: ManagerCompaniesService
  ) {
    this.maxDate = new Date();
  }

  ngOnInit() {
    this.getComapny();
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {

    this.apiRoutes.managerlistRoutesResponse({
      page: 1,
      limit: 999999999
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

  getDataDaily(){

    this.dataExport = null;

    if(this.bsRangeValue ){

      this.daysForm = moment(this.bsRangeValue).format('DD').toString();
      this.monthForm = moment(this.bsRangeValue).format('MM').toString();
      this.yearsForm = moment(this.bsRangeValue).format('YYYY').toString();

      this.daysTo = moment(this.bsRangeValue).format('DD').toString();
      this.monthTo = moment(this.bsRangeValue).format('MM').toString();
      this.yearsTo = moment(this.bsRangeValue).format('YYYY').toString();
    }

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.bsRangeValue) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.selectedRouteId) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_STAFF_ROUTE'), 'warning');
      return;
    }
    if (this.selectedRouteId == 0) {
      this.routeName = [{ name: this.translate.instant('ROUTE_ALL') }];
    } else {
      this.routeName = this.routes.filter(item => item.id == this.selectedRouteId);
    }

    this.isLoading = true;

    this.spinner.show();
    this.apiReports.managerReportsViewDaily({
      to_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
      route_id: this.selectedRouteId //0 : tat ca , nguoc lai: id
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe( data => {
      // this.route_group_debt_any = data['route_group_debt_any'];
      // this.shift_now_yesterday_result = data['shift_now_yesterday_result'] ;
      // this.route_group_colected_any = data['route_group_colected_any'] ;
      // this.shift_now_yesterday_all = data['shift_now_yesterday_all'] ;
      this.route_group_debt_all = data['route_group_debt_all'];
      this.route_detail = data['route_detail'] ;
      this.total_collecter = data['total_collecter'] ;
      this.route_group_colected_all = data['route_group_colected_all'] ;
      this.isCheckModuleApp = data['isCheckModuleApp'];
      this.spinner.hide();
    });
  }

  getComapny() {

    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.company = data;
      }
    );
  }

  exportFile() {

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.bsRangeValue) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
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

      this.apiReports.managerReportsExportDailyResponse({
        to_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
        from_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
        route_id: this.selectedRouteId //0 : tat ca , nguoc lai: id
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

    if (!this.bsRangeValue) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
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
          .tx-bold{ font-weight: bold;}
          .tx-center{text-align: center}
          .tx-left{text-align: left}
          .tx-right{text-align: right}
          .tx-10{font-size: 13px; font-family: 'Times New Roman';}
          .tx-11{font-size: 11px; font-family: 'Times New Roman';}
          .tx-12{font-size: 16px; font-family: 'Times New Roman';}
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
            .tx-bold{ font-weight: bold;}
            .tx-center{text-align: center}
            .tx-left{text-align: left}
            .tx-right{text-align: right}
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
