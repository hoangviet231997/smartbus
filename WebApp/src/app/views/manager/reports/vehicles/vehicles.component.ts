  import { Component, OnInit,ViewEncapsulation, AfterViewInit, ViewChild, Pipe, PipeTransform } from '@angular/core';
import { ManagerReportsService, ManagerRoutesService, ManagerCompaniesService, ManagerVehiclesService } from '../../../../api/services';
import { Route, Vehicle } from '../../../../api/models';
import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import swal from 'sweetalert2';
import { saveAs } from 'file-saver/FileSaver';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { transliterate as tr, slugify } from 'transliteration';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';

@Pipe({ name: 'filterVehicle' })
export class FilterVehiclePipe implements PipeTransform {
  public transform(arrayVehicles: Vehicle[], filter: string): any[] {

    if (!arrayVehicles || !arrayVehicles.length) {
      return [];
    }
    if (!filter) {
      return arrayVehicles;
    }
    return arrayVehicles.filter(vehicle => {
      return tr(vehicle.license_plates).toLowerCase().indexOf(tr(filter).toLowerCase()) >= 0;
    });
  }
}

@Component({
  selector: 'app-vehicles',
  templateUrl: './vehicles.component.html',
  styleUrls: ['./vehicles.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class VehiclesComponent implements OnInit, AfterViewInit {

  @ViewChild('listVehical') public listVehical: ModalDirective;

  public bsRangeValue: Date[];
  public maxDate: Date;
  public routes: Route[];
  public selectedRouteId: any = 0;

  public isExport = false;
  public isCollected = false;
  public dataExport:any = null;

  public vehicles: any = [];
  public isCheckModuleApp: any;

  public isLoading = false;

  public total_pos: number;
  public total_charge: number;
  public total_qrcode: number;
  public total_tickets: number;
  public total_ticket_pos: number;
  public total_ticket_charge: number;
  public total_ticket_qrcode: number;
  public total_price_discount : number;
  public total_price_collected: number;
  public total_price_deposit_month : number;
  public count_collected_ticket : number;
  public count_discount_ticket : number;
  public count_revenue_ticket  : number;
  public total_price_deposit  : number;

  public company: any;
  public daysForm: string;
  public monthForm: string;
  public yearsForm: string;
  public daysTo: string;
  public monthTo: string;
  public yearsTo: string;
  public routeName: any;
  public vehicle_Items: Vehicle[];
  public inputLicensePlates: string;
  public licensePlatesInput: any;
  public vehicle_id: any = 0;

  public permissions:any[] = [];

  constructor(
    private translate: TranslateService,
    private apiRoutes: ManagerRoutesService,
    private apiReports: ManagerReportsService,
    private apiCompanies: ManagerCompaniesService,
    private apiVehicles: ManagerVehiclesService,
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

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {

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

  getDataVehicle(){

    this.dataExport = null;
    this.isLoading = true;

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

    if (this.vehicle_id == null) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_VEHICLE_NUMBER'), 'warning');
      return;
    }

    if (this.selectedRouteId === null || this.selectedRouteId === undefined) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_STAFF_ROUTE'), 'warning');
      return;
    }


    this.daysForm = moment(this.bsRangeValue[0]).format('DD').toString();
    this.monthForm = moment(this.bsRangeValue[0]).format('MM').toString();
    this.yearsForm = moment(this.bsRangeValue[0]).format('YYYY').toString();

    this.daysTo = moment(this.bsRangeValue[1]).format('DD').toString();
    this.monthTo = moment(this.bsRangeValue[1]).format('MM').toString();
    this.yearsTo = moment(this.bsRangeValue[1]).format('YYYY').toString();

    if (this.selectedRouteId == 0) {
      this.routeName = [{name: this.translate.instant('BTN_VIEW_RECEIPT')}];
    } else {
      this.routeName = this.routes.filter(item => item.id == this.selectedRouteId);
    }

    this.spinner.show();

    this.apiReports.managerReportsViewVehicles({
      route_id: this.selectedRouteId,
      to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
      vehicle_id: this.vehicle_id
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      data => {
        this.vehicles = data['vehicles_arr'];
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

    if (this.vehicle_id < 0) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_VEHICLE_NUMBER'), 'warning');
      return;
    }

    if (this.selectedRouteId < 0) {
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

      this.apiReports.managerReportsExportVehiclesResponse({
        route_id: this.selectedRouteId,
        to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
        from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
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

    if (this.vehicle_id == null) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_VEHICLE_NUMBER'), 'warning');
      return;
    }

    if (this.selectedRouteId < 0) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_STAFF_ROUTE'), 'warning');
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
        </head>
        <body onload="window.print();window.close()">`+ printContents +`</body>
      </html>`
    );
    popupWin.document.close();

  }

  showListVehicleModal() {

    this.getlistVehicleAll();
    this.listVehical.show();
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
    this.getDataVehicle();
  }
}
