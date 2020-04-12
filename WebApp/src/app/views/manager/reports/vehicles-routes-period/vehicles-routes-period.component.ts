import { Component, OnInit, AfterViewInit, ViewChild } from '@angular/core';
import { ManagerReportsService, ManagerCompaniesService } from '../../../../api/services';
import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import swal from 'sweetalert2';
import { saveAs } from 'file-saver/FileSaver';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { transliterate as tr, slugify } from 'transliteration';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-vehicles-routes-period',
  templateUrl: './vehicles-routes-period.component.html',
  styleUrls: ['./vehicles-routes-period.component.css']
})
export class VehiclesRoutesPeriodComponent implements OnInit {

  public maxDate: Date;
  public isLoading = false;
  public permissions: any[] = [];

  public selectContentRp: any;
  public selectContentCompare: any = 'all';

  public now_bsRangeValue: Date[];
  public last_bsRangeValue: Date[];

  public company: any;
  public vehicles_routes_period: any = [];
  public isExport = false;
  public dataExport:any = null;
  public isCollected = false;
  public current_period: any;
  public previous_period: any;

  constructor(
    private translate: TranslateService,
    private apiReports: ManagerReportsService,
    private apiCompanies: ManagerCompaniesService,
    private spinner: NgxSpinnerService
  ) {
    this.maxDate = new Date();
  }

  ngOnInit() {
    this.getComapny();
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
    this.selectContentCompare = 'all';
  }

  ngAfterViewInit() { }

  getComapny() {
    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.company = data;
      }
    );
  }

  getData() {

    if (this.now_bsRangeValue === undefined) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_NOW'), 'warning');
      return;
    }

    if (this.now_bsRangeValue.length !== 2) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_NOW'), 'warning');
      return;
    }

    if (this.last_bsRangeValue === undefined) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_LAST'), 'warning');
      return;
    }

    if (this.last_bsRangeValue.length !== 2) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_LAST'), 'warning');
      return;
    }

    if (this.selectContentRp === undefined || this.selectContentRp === '' || this.selectContentRp === null) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_CONTENT_RP'), 'warning');
      return;
    }

    this.dataExport = null;
    this.isLoading = true;
    this.current_period = moment(this.now_bsRangeValue[0]).format('DD/MM/YYYY') + ' - ' + moment(this.now_bsRangeValue[1]).format('DD/MM/YYYY');
    this.previous_period = moment(this.last_bsRangeValue[0]).format('DD/MM/YYYY') + ' - ' + moment(this.last_bsRangeValue[1]).format('DD/MM/YYYY');

    this.spinner.show();
    this.apiReports.managerReportsViewVehicleRoutePriod({
      now_from_date: moment(this.now_bsRangeValue[0]).format('YYYY-MM-DD'),
      now_to_date: moment(this.now_bsRangeValue[1]).format('YYYY-MM-DD'),
      last_from_date: moment(this.last_bsRangeValue[0]).format('YYYY-MM-DD'),
      last_to_date: moment(this.last_bsRangeValue[1]).format('YYYY-MM-DD'),
      object_compare: this.selectContentCompare,
      object_report: this.selectContentRp
    }).subscribe((data) => {
      this.vehicles_routes_period = data;
      if(this.vehicles_routes_period) this.spinner.hide();
    });
  }

  showPrintPreview() {

    if (this.now_bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_NOW'), 'warning');
      return;
    }

    if (this.now_bsRangeValue.length !== 2) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_NOW'), 'warning');
      return;
    }

    if (this.last_bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_LAST'), 'warning');
      return;
    }

    if (this.last_bsRangeValue.length !== 2) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_LAST'), 'warning');
      return;
    }

    if (this.selectContentRp === undefined || this.selectContentRp === '' || this.selectContentRp === null) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_CONTENT_RP'), 'warning');
      return;
    }
    
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
          .tx-10{font-size: 15px; font-family: 'Times New Roman';}
          .tx-11{font-size: 12px; font-family: 'Times New Roman';}
          .tx-12{font-size: 20px; font-family: 'Times New Roman';}
          .tx-bold{font-weight: bold;}
          .w-10{width: 10cm}
          .w-3{width: 3cm;float:left}
          .fl{float:left}
          .fr{float:right}
          .w-4{width: 4cm}
          .w-2{width: 1.5cm}
          .pt-0{margin-top: 0}
          </style>
        </head>
        <body onload="window.print();window.close()">`+ printContents + `</body>
      </html>`
    );
    popupWin.document.close();
  }

  exportFile() {

    if (this.now_bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_NOW'), 'warning');
      return;
    }

    if (this.now_bsRangeValue.length !== 2) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_NOW'), 'warning');
      return;
    }

    if (this.last_bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_LAST'), 'warning');
      return;
    }

    if (this.last_bsRangeValue.length !== 2) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE_LAST'), 'warning');
      return;
    }

    if (this.selectContentRp === undefined || this.selectContentRp === '' || this.selectContentRp === null) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_CONTENT_RP'), 'warning');
      return;
    }

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
      this.apiReports.managerReportsExportVehicleRoutePriodResponse({
        now_from_date: moment(this.now_bsRangeValue[0]).format('YYYY-MM-DD'),
        now_to_date: moment(this.now_bsRangeValue[1]).format('YYYY-MM-DD'),
        last_from_date: moment(this.last_bsRangeValue[0]).format('YYYY-MM-DD'),
        last_to_date: moment(this.last_bsRangeValue[1]).format('YYYY-MM-DD'),
        object_compare: this.selectContentCompare,
        object_report: this.selectContentRp
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
          const byteCharacters = atob(this.dataExport.body);
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
}
