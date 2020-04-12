import { Component, OnInit,ViewEncapsulation, AfterViewInit, ViewChild, Pipe, PipeTransform } from '@angular/core';
import { ManagerReportsService, ManagerRoutesService, ManagerCompaniesService, ManagerVehiclesService ,ManagerTicketTypesService, ManagerUsersService } from '../../../../api/services';
import { User, UserSearch, ReceiptView, ReceiptForm, Permission, NumberConvert } from '../../../../api/models';
import { Route, Vehicle } from '../../../../api/models';
import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import swal from 'sweetalert2';
import { saveAs } from 'file-saver/FileSaver';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { transliterate as tr, slugify } from 'transliteration';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { getDate } from 'ngx-bootstrap/chronos/utils/date-getters';

@Component({
  selector: 'app-output',
  templateUrl: './output.component.html',
  styleUrls: ['./output.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class OutputComponent implements OnInit , AfterViewInit {
  @ViewChild('listVehical') public listVehical: ModalDirective;
  // @ViewChild('listUserModal') public listUserModal: ModalDirective;

  public maxDate: Date;
  public bsRangeValue: any;
  public licensePlatesInput: any;
  // public user_id: any;
  // public inputUserName : any;
  public isLoading = false;
  public vehicle_id: any = 0;
  // public selectedRouteId: any = 0;
  // public routeName: any;
  // public routes: Route[];
  public vehicles: any = [];
  public isCheckModuleApp: any;
  public inputLicensePlates: string;
  public vehicle_Items: Vehicle[];
  public output_arr:any = [];
  public total_arr:any = [];


  public daysForm: string;
  public monthForm: string;
  public yearsForm: string;
  public daysTo: string;
  public monthTo: string;
  public yearsTo: string;

  public nameDriver = '';
  public nameSubDriver = '';
  public license_plate ='';
  public nameStation = '';
  public route_name = '';
  public route_number: any = '';



  // public valueSelectedPrice: any = [];
  // public ticket_arr:any = [];
  // public ticketPriceItems: any = [];

  // public searchUserName = '';
  // public users: User[];
  // public userSearch: UserSearch;
  // public receiptForm: ReceiptForm;
  public isCollected = false;
  public company: any;
  public now = new Date();
  public isExport = false;
  public dataExport:any = null;
  
  public permissions:any = [];

  constructor(
    private translate: TranslateService,
    // private apiRoutes: ManagerRoutesService,
    private apiReports: ManagerReportsService,
    private apiCompanies: ManagerCompaniesService,
    private apiVehicles: ManagerVehiclesService,
    private spinner : NgxSpinnerService,
    // private apiTicketTypes: ManagerTicketTypesService,
    // private apiUsers: ManagerUsersService,


  ) {
    this.maxDate = new Date();
    // this.userSearch = new UserSearch();
    // this.userSearch.rfid = null;
    // this.userSearch.barcode = null;

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

  getComapny() {
    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.company = data;
      }
    );
  }

  refreshView() {

    // this.spinner.show();
    // this.apiTicketTypes.managerListTicketTypesByTypeParam(-1).subscribe(
    //   resp => {
    //     this.ticketPriceItems = [];

    //     for (let i = 0; i < resp.length; i++) {
    //       this.ticketPriceItems.push({
    //         id: resp[i].ticket_prices[resp[i].ticket_prices.length - 1].id,
    //         text: resp[i].ticket_prices[resp[i].ticket_prices.length - 1].price+'('+resp[i].order_code+' - '+((resp[i].type == 0) ? "Vé lượt":"Vé tháng")+')'
    //       });
    //     }
    //     this.spinner.hide();
    //   }
    // );

  }

  getOutput() {

    this.isLoading = true;
    this.dataExport = null;

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.bsRangeValue) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.vehicle_id === 0) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_VEHICLE_NUMBER'), 'warning');
      return;
    }

    this.daysForm = moment(this.bsRangeValue).format('DD').toString();
    this.monthForm = moment(this.bsRangeValue).format('MM').toString();
    this.yearsForm = moment(this.bsRangeValue).format('YYYY').toString();

    this.daysTo = moment(this.bsRangeValue).format('DD').toString();
    this.monthTo = moment(this.bsRangeValue).format('MM').toString();
    this.yearsTo = moment(this.bsRangeValue).format('YYYY').toString();

    this.spinner.show();
    this.apiReports.managerReportsViewOutputByVehicle({
      to_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
      vehicle_id: this.vehicle_id
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      data => {
        this.isCheckModuleApp = data['isCheckModuleApp'];
        this.output_arr = data['result_arr'];
        this.total_arr = data['obj_total'];
        this.nameDriver = "";
        this.nameSubDriver = "";
        this.license_plate = "";
        this.nameStation = "";
        this.route_name = "";
        this.route_number = '';
        if(this.output_arr.length > 0){
          this.nameDriver = this.output_arr[0].driver_name;
          this.nameSubDriver = this.output_arr[0].subdriver_name;
          this.license_plate = this.output_arr[0].license_plate;
          this.nameStation = this.output_arr[0].station_start;
          this.route_name = this.output_arr[0].route_name;
          this.route_number = this.output_arr[0].route_number;
        }
        this.spinner.hide();
      });
  }

  showListVehicleModal() {

    this.getlistVehicleAll();
    this.listVehical.show();
  }

  // showListUserModal() {
  //   this.listUserModal.show();
  // }

  // chooseUser(id: number) {
  //   if (id == 0) {
  //     this.user_id = id;
  //     this.inputUserName = this.translate.instant('BTN_VIEW_RECEIPT');
  //   } else {
  //     this.users.map(
  //       (user) => {
  //         if (user.id == id) {
  //           this.inputUserName = user.fullname;
  //           this.user_id = user.id;
  //         }
  //     });
  //   }
  //   this.listVehical.hide();
  //   this.getDataVehicle();
  // }

  chooseVehicle(vehicel_id) {
    if (vehicel_id == 0) {
      this.vehicle_id = vehicel_id;
      this.licensePlatesInput = this.translate.instant('BTN_VIEW_RECEIPT');
    } else {
      this.vehicle_Items.map(
        (vehicle) => {
          if (vehicle.id == vehicel_id) {
            this.licensePlatesInput = vehicle.license_plates;
            this.vehicle_id = vehicle.id;
          }
      });
    }
    this.listVehical.hide();
    this.getOutput();
  }

  getlistVehicleAll(){

    this.apiVehicles.getlistVehicleAllResponse().pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      res => {
        this.inputLicensePlates = '';
        this.vehicle_Items = res.body.filter(
          (vehicle) => {
            return vehicle;
        });
      }
    );
  }

  // getDataTicket(){

  //   if (this.bsRangeValue === undefined) {
  //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
  //     return;
  //   }

  //   if (!this.bsRangeValue) {
  //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
  //     return;
  //   }

  //   if(!this.valueSelectedPrice['id']){
  //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_EXPORT_PRINT_TKT_PRICE'), 'warning');
  //     return;
  //   }

  //   this.spinner.show();
  //   //get data print ticket
  //   this.apiReports.managerReportsPrintTickets({
  //     to_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
  //     from_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
  //     price_id: this.valueSelectedPrice ? this.valueSelectedPrice['id'] : 0,
  //     }).subscribe(
  //       resp => {
  //         this.ticket_arr = resp;
  //         this.spinner.hide();
  //   });
  // }

  showPrintPreview() {

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.bsRangeValue) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.vehicle_id === 0) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_VEHICLE_NUMBER'), 'warning');
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
          .tx-right{text-align: right}
          .tx-left{text-align: left}
          .tx-bold{font-weight: bolder;}
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
          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        </head>
        <body style="font-family: 'Times New Roman'"
            onload="window.print();window.close()">`+ printContents + `
            <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        </body>
      </html>`
    );
    popupWin.document.close();

  }

  exportFile() {

    // if (this.output_arr.length > 0) {

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.bsRangeValue) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.vehicle_id === 0) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_VEHICLE_NUMBER'), 'warning');
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
      this.apiReports.managerReportsExportOutputByVehicleResponse({
        to_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
        from_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
        vehicle_id: this.vehicle_id
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
          const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel' });

          saveAs(blob, filename);
        },
        err => {
          this.isExport = false;
        }
      );
    }
  }
  // }

  // refreshValuePrice(value:any):void {
  //   this.valueSelectedPrice = value;
  // }

  // public selectedPrice(value: any) {
  //   this.valueSelectedPrice = value;
  //   this.getDataTicket();
  // }

  // public removedPrice(value: any){

  //   if( this.valueSelectedPrice['id'] == value.id){
  //     this.valueSelectedPrice['id'] = null;
  //   }
  //   this.getDataTicket();
  // }

}
