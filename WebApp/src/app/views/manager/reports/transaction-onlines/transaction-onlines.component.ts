
import { Component, OnInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { ManagerReportsService } from '../../../../api/services';
import * as moment from 'moment';
import swal from 'sweetalert2';
import { saveAs } from 'file-saver/FileSaver';
import { NgxSpinnerService } from 'ngx-spinner';
import { map } from 'rxjs/operators/map';

@Component({
  selector: 'app-transaction-onlines',
  templateUrl: './transaction-onlines.component.html',
  styleUrls: ['./transaction-onlines.component.css']
})
export class TransactionOnlinesComponent implements OnInit {

  public maxDate: Date;

  public transactionOnlines: any = [];
  public totalOnlines: any;
  public company: any;

  public dateFrom: any;
  public dateTo: any;
  public pos: any;
  public pos_taxi: any;
  public online: any;
  public online_taxi: any;
  public charge: any;
  public charge_taxi: any;
  public charge_free: any;
  public topup_momo: any;
  public deposit: any;
  public totals: any;
  public total_num: any;

  public daysForm: any;
  public monthForm: any;
  public yearsForm: any;
  public daysTo: any;
  public monthTo: any;
  public yearsTo: any;

  public selectedType: any;
  public selectedPartner: any;
  public bsRangeValue: any;
  public isCheckModuleApp: any ;

  public isExport = false;
  public isLoading =  false;
  public dataExport:any = null;

  public permissions:any[] = [];

  constructor(
    private translate: TranslateService,
    private apiReports: ManagerReportsService,
    private spinner : NgxSpinnerService
  ) {
    this.maxDate = new Date();
  }

  ngOnInit() {
    if (localStorage.getItem('user')) {
      this.company = JSON.parse(localStorage.getItem('user')).company;
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  getTransactionOnline(){

    this.isLoading = true;
    this.dataExport = null;

    if (!this.selectedType) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_TRANSACTION_ONLINE_TYPE'), 'warning');
      return;
    }

    if (this.bsRangeValue === undefined) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_TRANSACTION_ONLINE_DATE'), 'warning');
      return;
    }

    if (this.bsRangeValue.length !== 2) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_TRANSACTION_ONLINE_DATE'), 'warning');
      return;
    }

    if (!this.selectedPartner) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_TRANSACTION_ONLINE_PARTNER'), 'warning');
      return;
    }

    this.spinner.show();
    if(this.bsRangeValue.length > 0) {
      this.daysForm = moment(this.bsRangeValue[0]).format('DD').toString();
      this.monthForm = moment(this.bsRangeValue[0]).format('MM').toString();
      this.yearsForm = moment(this.bsRangeValue[0]).format('YYYY').toString();
      this.daysTo = moment(this.bsRangeValue[1]).format('DD').toString();
      this.monthTo = moment(this.bsRangeValue[1]).format('MM').toString();
      this.yearsTo = moment(this.bsRangeValue[1]).format('YYYY').toString();
    }
    this.apiReports.managerReportsViewTransactionOnline({
      from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
      to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
      type: this.selectedType,
      partner: this.selectedPartner,
    }).subscribe(data => {
      this.isCheckModuleApp = data['isCheckModuleApp'];
      this.totalOnlines = data['total_onlines'];
      this.transactionOnlines = data['transactions'];
      this.spinner.hide();
    });
  }

  printFile() {

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
          .tx-bold {font-weight: bold}
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
    popupWin.document.close();
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
      this.apiReports.managerReportsExportTransactionOnlineResponse({
        from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
        to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
        type: this.selectedType,
        partner: this.selectedPartner,
      }).pipe(
        map(_r => {
          return _r;
        })).subscribe(
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
}
