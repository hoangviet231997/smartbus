import { Component, OnInit, ViewChild } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { HttpErrorResponse } from '@angular/common/http';
import { ManagerReportsService, ManagerCompaniesService, ManagerMembershipsService } from '../../../../api/services';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import swal from 'sweetalert2';
import { saveAs } from 'file-saver/FileSaver';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-cards',
  templateUrl: './cards.component.html',
  styleUrls: ['./cards.component.css']
})
export class CardsComponent implements OnInit {

  @ViewChild('detailMembershiChargeModel') public detailMembershiChargeModel: ModalDirective;
  @ViewChild('detailMembershiDepositModel') public detailMembershiDepositModel: ModalDirective;
  @ViewChild('detailMembershipModel') public detailMembershipModel: ModalDirective;

  public bsRangeValue: any;
  public maxDate: Date;
  public isLoading = false;
  public currentTime: Date = new Date();
  public dateDefault: any = [];
  public company: any;
  public isExport = false;
  public dataExport:any = null;

  public membershipsCharges: any = [];
  public total_membershipsCharges: any = 0;
  public membershipsDeposits: any = [];
  public total_membershipsDeposits: any = 0;

  public membershipsChargetotal: any = 0;
  public membershipsDepositTotal: any = 0;


  public membershipCards: any = [];
  public totalMemberships: any;
  public data_from: any = null;
  public data_to: any = null;

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
    private apiCompanies: ManagerCompaniesService,
    private spinner: NgxSpinnerService,
    private apiMembership: ManagerMembershipsService,
  ) {
    this.maxDate = new Date();
   }

  ngOnInit() {

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  // tslint:disable-next-line:use-life-cycle-interface
  ngAfterViewInit() {
    this.getComapny();
  }
  showPrintPreview() {

    if (this.dateDefault === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.dateDefault.length !== 2) {
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

    popupWin.document.close();
  }

  searchCard() {

    this.isLoading = true;
    this.data_from = null;
    this.data_to = null;
    this.dataExport = null;

    if(this.dateDefault.length > 0) {

      this.data_from = moment(this.dateDefault[0]).format('YYYY-MM-DD');
      this.data_to = moment(this.dateDefault[1]).format('YYYY-MM-DD');

      this.daysForm = moment(this.dateDefault[0]).format('DD').toString();
      this.monthForm = moment(this.dateDefault[0]).format('MM').toString();
      this.yearsForm = moment(this.dateDefault[0]).format('YYYY').toString();

      this.daysTo = moment(this.dateDefault[1]).format('DD').toString();
      this.monthTo = moment(this.dateDefault[1]).format('MM').toString();
      this.yearsTo = moment(this.dateDefault[1]).format('YYYY').toString();
    }
    this.spinner.show();
    this.apiReports.managerReportsViewCard({
      from_date: this.data_from,
      to_date: this.data_to
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {

        this.membershipCards = resp['card_arr'];
        this.totalMemberships = resp['total_memberships'];
        this.spinner.hide();
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

  exportFile() {

    if (this.dateDefault === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.dateDefault.length !== 2) {
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
      this.apiReports.managerReportsExportCardResponse({
        to_date: moment(this.dateDefault[1]).format('YYYY-MM-DD'),
        from_date: moment(this.dateDefault[0]).format('YYYY-MM-DD'),
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
