import { Component, OnInit, ViewChild } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap';
import { ManagerRoutesService, ManagerReportsService, ManagerCompaniesService } from 'src/app/api/services';
import { TranslateService } from '@ngx-translate/core';
import { map } from 'rxjs/operators/map';
import { Route } from 'src/app/api/models';
import { NgxSpinnerService } from 'ngx-spinner';
import swal from 'sweetalert2';
import * as moment from 'moment';
import { saveAs } from 'file-saver/FileSaver';

@Component({
  selector: 'app-card-exemption',
  templateUrl: './card-exemption.component.html',
  styleUrls: ['./card-exemption.component.css']
})
export class CardExemptionComponent implements OnInit {

  @ViewChild('detailModal') public detailModal: ModalDirective;

  public bsRangeValue: any;
  public company: any;
  public cardExemption: any = [];
  public cardExemptionDetail: any = [];
  public dateSearch: any;
  public isExport = false;
  public dataExport:any = null;
  public isLoading = false;
  public permissions:any[] = [];
  public routes: Route[];
  public route_name: any;
  public selectedRouteId: any = 0;
  public sum_of_tickets = 0;
  public sum_of_amount = 0;
  public sum_of_prices = 0;

  public maxDate: Date;
  public dayFrom: string;
  public monthFrom: string;
  public yearFrom: string;
  public dayTo: string;
  public monthTo: string;
  public yearTo: string;

  constructor(
    private translate: TranslateService,
    private apiCompanies: ManagerCompaniesService,
    private apiReports: ManagerReportsService,
    private apiRoutes: ManagerRoutesService,
    private spinner : NgxSpinnerService,
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
    this.getRoutes();
  }

  sum(sumOfEachTicket, sumOfEachAmount) {
    this.sum_of_tickets += sumOfEachTicket;
    this.sum_of_amount += sumOfEachAmount;
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

  getCardExemption() {

    this.dataExport = null;
    this.isLoading = true;

    if (this.bsRangeValue === undefined) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.bsRangeValue) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    this.dayFrom = moment(this.bsRangeValue[0]).format('DD').toString();
    this.monthFrom = moment(this.bsRangeValue[0]).format('MM').toString();
    this.yearFrom = moment(this.bsRangeValue[0]).format('YYYY').toString();

    this.dayTo = moment(this.bsRangeValue[1]).format('DD').toString();
    this.monthTo = moment(this.bsRangeValue[1]).format('MM').toString();
    this.yearTo = moment(this.bsRangeValue[1]).format('YYYY').toString();

    this.sum_of_tickets = 0;
    this.sum_of_amount = 0;
    this.sum_of_prices = 0;

    this.spinner.show();
    this.apiReports.managerReportsViewCardExemption({
      from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
      to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
      route_id: this.selectedRouteId
    }).pipe(map(_r => {return _r;}))
    .subscribe(data => {
      this.cardExemption = data;
      this.spinner.hide();

      this.cardExemption.forEach(function(value) {
        this.sum(value.total_ticket, value.total_amount);
        this.route_name = value.route_name;
      }, this);
    });
  }

  getCardExemptionDetail(data: any = []) {
    this.sum_of_prices = 0;

    this.detailModal.show();
    this.cardExemptionDetail = data;
    this.cardExemptionDetail.forEach(function(value, index, array){
      if(index === (array.length - 1)) {
        this.sum_of_prices = value.sum_of_prices;
      }
    }, this);
  }

  exportExcelFile() {

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.bsRangeValue) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
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

      this.apiReports.managerReportsExportCardExemptionResponse({
        from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
        to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
        route_id: this.selectedRouteId
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
  }

  showPrintPreview() {

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.bsRangeValue) {
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
    popupWin.document.close();
  }
}
