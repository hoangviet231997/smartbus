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
  selector: 'app-vehicles-all',
  templateUrl: './vehicles-all.component.html',
  styleUrls: ['./vehicles-all.component.css']
})
export class VehiclesAllComponent implements OnInit {

  public bsRangeValue: Date[];
  public maxDate: Date;

  public isExport = false;
  public isCollected = false;
  public dataExport:any = null;

  public vehicles: any = [];
  public isCheckModuleApp: any;

  public isLoading = false;

  public sum_pos: number;
  public sum_charge: number;
  public sum_tickets: number;
  public sum_revenue: number;

  public company: any;
  public daysForm: string;
  public monthForm: string;
  public yearsForm: string;
  public daysTo: string;
  public monthTo: string;
  public yearsTo: string;

  public permissions:any[] = [];

  constructor(
    private translate: TranslateService,
    private apiReports: ManagerReportsService,
    private apiCompanies: ManagerCompaniesService,
    private spinner : NgxSpinnerService
  ) {
    this.maxDate = new Date();
  }

  ngOnInit() {

    this.getComapny();
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  getComapny() {
    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.company = data;
      }
    );
  }

  getDataVehicle() {

    this.isLoading = true;
    this.dataExport = null;

    if (this.bsRangeValue === undefined) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.bsRangeValue.length !== 2) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    this.daysForm = moment(this.bsRangeValue[0]).format('DD').toString();
    this.monthForm = moment(this.bsRangeValue[0]).format('MM').toString();
    this.yearsForm = moment(this.bsRangeValue[0]).format('YYYY').toString();

    this.daysTo = moment(this.bsRangeValue[1]).format('DD').toString();
    this.monthTo = moment(this.bsRangeValue[1]).format('MM').toString();
    this.yearsTo = moment(this.bsRangeValue[1]).format('YYYY').toString();

    this.spinner.show();

    this.apiReports.managerReportsViewVehicleAll({
      to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      data => {
        this.vehicles = data['vehicle_arr'];
        this.isCheckModuleApp = data['isCheckModuleApp'];
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
      this.apiReports.managerReportsExportVehicleAllResponse({
        to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
        from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
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

  showPrintPreview() {

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.bsRangeValue.length !== 2) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
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
          @page { size: A4; size: landscape}
          .tx-center{text-align: center}
          .tx-left{text-align: left}
          .tx-bold{font-weight: bold;}
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
    popupWin.document.close();

  }
}
