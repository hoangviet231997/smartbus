import { Component, OnInit, AfterViewInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { ManagerReportsService, ManagerCompaniesService, ManagerRoutesService, ManagerTicketTypesService } from '../../../../api/services';
import { Route, TicketAllocate} from '../../../../api/models';
import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import swal from 'sweetalert2';
import { saveAs } from 'file-saver/FileSaver';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-tickets',
  templateUrl: './tickets.component.html',
  styleUrls: ['./tickets.component.css']
})
export class TicketsComponent implements OnInit, AfterViewInit{

  public bsRangeValue: Date[] = [];
  public maxDate: Date;
  public tickets: any[] = [];
  public tickets_station: any[] = [];
  public totalTickets: any;

  public ticket_arr:any = [];

  public isExport = false;
  public isLoading =  false;

  public total_into_money: number
  public total_count_sale: number;

  public company: any;
  public daysForm: string;
  public monthForm: string;
  public yearsForm: string;
  public daysTo: string;
  public monthTo: string;
  public yearsTo: string;
  public routes: Route[];
  public ticketTypes = [];
  public selectedRouteId: any = 0;
  public selectedPriceId: any = 0;
  public from_date = null;
  public to_date = null;
  public selectedType: any = 0;
  public dataExport:any = null;
  public permissions:any =[];

  constructor(
    private translate: TranslateService,
    private apiReports: ManagerReportsService,
    private apiCompanies: ManagerCompaniesService,
    private spinner : NgxSpinnerService,
    private apiRoutes: ManagerRoutesService,
    private apiTicketTypes: ManagerTicketTypesService
  ) {
    this.maxDate = new Date();
  }

  ngOnInit() {

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {

    this.getComapny();
    this.getRoutes();
    this.getTicketTypes();
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

  getTicketTypes() {
    // 0: ve luot , 1: ve thang, -1: ca 2 loai ve
    this.apiTicketTypes.managerListTicketTypesByTypeParam(-1).subscribe(
      data => {
        this.ticketTypes = data;
      }
    );
  }

  getDataTicket(){

    this.dataExport = null;

    if (this.selectedType == 0) {

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

      this.isLoading = true;

      if (this.bsRangeValue && this.bsRangeValue.length > 0) {
        this.daysForm = moment(this.bsRangeValue[0]).format('DD').toString();
        this.monthForm = moment(this.bsRangeValue[0]).format('MM').toString();
        this.yearsForm = moment(this.bsRangeValue[0]).format('YYYY').toString();

        this.daysTo = moment(this.bsRangeValue[1]).format('DD').toString();
        this.monthTo = moment(this.bsRangeValue[1]).format('MM').toString();
        this.yearsTo = moment(this.bsRangeValue[1]).format('YYYY').toString();
        this.from_date = moment(this.bsRangeValue[0]).format('YYYY-MM-DD');
        this.to_date = moment(this.bsRangeValue[1]).format('YYYY-MM-DD');
      }

      if (this.selectedRouteId == undefined)   this.selectedRouteId = 0;

      if (this.selectedPriceId == undefined)   this.selectedPriceId = 0;

      this.spinner.show();
      //call api report view
      this.apiReports.managerReportsViewTickets({
        from_date: this.from_date,
        to_date: this.to_date,
        route_id: this.selectedRouteId,
        price_id: this.selectedPriceId
      }).subscribe(
        data => {

          this.tickets = data;
          this.spinner.hide();
        });
    }

    if (this.selectedType == 1) {

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

      this.isLoading = true;

      if (this.bsRangeValue && this.bsRangeValue.length > 0) {
        this.daysForm = moment(this.bsRangeValue[0]).format('DD').toString();
        this.monthForm = moment(this.bsRangeValue[0]).format('MM').toString();
        this.yearsForm = moment(this.bsRangeValue[0]).format('YYYY').toString();

        this.daysTo = moment(this.bsRangeValue[1]).format('DD').toString();
        this.monthTo = moment(this.bsRangeValue[1]).format('MM').toString();
        this.yearsTo = moment(this.bsRangeValue[1]).format('YYYY').toString();
        this.from_date = moment(this.bsRangeValue[0]).format('YYYY-MM-DD');
        this.to_date = moment(this.bsRangeValue[1]).format('YYYY-MM-DD');
      }

      if (this.selectedRouteId == undefined) {
        this.selectedRouteId = 0;
      }
      if (this.selectedPriceId == undefined) {
        this.selectedPriceId = 0;
      }

      this.spinner.show();
      //call api report view
      this.apiReports.managerReportsViewTicketsByStation({
        from_date: this.from_date,
        to_date: this.to_date,
        route_id: this.selectedRouteId,
        price_id: this.selectedPriceId
      }).pipe(
        map(_r => {
          return _r;
        })
      ).subscribe(
        data => {
          this.tickets_station = data;
          this.spinner.hide();
      });
    }
  }

  exportFile() {

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

    if (this.selectedType == 0){
      this.isExport = true;

      //check data base64 export
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
        this.apiReports.managerReportsExportTicketsResponse({
          from_date: this.from_date,
          to_date: this.to_date,
          route_id: this.selectedRouteId,
          price_id: this.selectedPriceId
        }).pipe(
          map(_r => {
            return _r;
          })
        ).subscribe(
          data => {

            //set data base64 export
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

    if (this.selectedType == 1){
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
        this.apiReports.managerReportsExportTicketsByStationResponse({
          from_date: this.from_date,
          to_date: this.to_date,
          route_id: this.selectedRouteId,
          price_id: this.selectedPriceId
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
  }

  showPrintPreviewSummary(){

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
        <body onload="window.print();window.close()">`+ printContents +`</body>
      </html>`
    );
    popupWin.document.close();
  }

  selectedChangeTypeTicket(){

    this.isLoading = false;
    this.selectedRouteId = 0;
    this.bsRangeValue = [];
    this.selectedPriceId = 0;
    this.dataExport = null;
  }
}
