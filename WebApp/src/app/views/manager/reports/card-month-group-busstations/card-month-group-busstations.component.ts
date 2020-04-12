import { Component, OnInit, ViewChild , AfterViewInit} from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { HttpErrorResponse } from '@angular/common/http';
import { ManagerReportsService, ManagerCompaniesService, ManagerUsersService} from '../../../../api/services';
import {  User } from '../../../../api/models';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import swal from 'sweetalert2';
import { saveAs } from 'file-saver/FileSaver';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-card-month-group-busstations',
  templateUrl: './card-month-group-busstations.component.html',
  styleUrls: ['./card-month-group-busstations.component.css']
})
export class CardMonthGroupBusstationsComponent implements OnInit , AfterViewInit{

  @ViewChild('rpDetailCardMonthGerenalModal') public rpDetailCardMonthGerenalModal: ModalDirective;
  @ViewChild('listUserModal') public listUserModal: ModalDirective;

  public isLoading = false;
  public isExport = false;
  public dataExport:any = null;
  public isCollected = false;
  public maxDate: Date;
  public users: User[];
  public cardMonthGroupBusSations = [];

  public bsRangeValueParam: any;
  public userIdParam: any = 0;
  public company: any;

  public daysForm: string;
  public monthForm: string;
  public yearsForm: string;
  public daysTo: string;
  public monthTo: string;
  public yearsTo: string;

  public from_date = null;
  public to_date = null;
  public staff_title = '';

  public searchUserName = this.translate.instant('BTN_VIEW_RECEIPT');
  public inputUserName = '';

  public permissions:any = [];

  constructor(
    private translate: TranslateService,
    private apiReports: ManagerReportsService,
    private spinner : NgxSpinnerService,
    private apiUsers: ManagerUsersService,
    private apiCompanies: ManagerCompaniesService
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

    //get users
    this.apiUsers.managerListUsersResponse({
      page: 1,
      limit: 99999
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {
        this.inputUserName = '';
        this.users = resp.body.filter(
          (user) => {
            if (user.role.name === 'driver' || user.role.name === 'subdriver') {
              return user;
            }
        });
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


  getDataReportCardMonthByGroupBusStations() {

    this.dataExport = null;
    this.isLoading = true;

    if (this.bsRangeValueParam === undefined || !this.bsRangeValueParam) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.bsRangeValueParam && this.bsRangeValueParam.length > 0) {

      this.from_date = moment(this.bsRangeValueParam[0]).format('YYYY-MM-DD');
      this.to_date = moment(this.bsRangeValueParam[1]).format('YYYY-MM-DD');

      this.daysForm = moment(this.bsRangeValueParam[0]).format('DD').toString();
      this.monthForm = moment(this.bsRangeValueParam[0]).format('MM').toString();
      this.yearsForm = moment(this.bsRangeValueParam[0]).format('YYYY').toString();

      this.daysTo = moment(this.bsRangeValueParam[1]).format('DD').toString();
      this.monthTo = moment(this.bsRangeValueParam[1]).format('MM').toString();
      this.yearsTo = moment(this.bsRangeValueParam[1]).format('YYYY').toString();
    }

    this.spinner.show();
    this.apiReports.managerReportsViewCardMonthByGroupBusStation({
      from_date: this.from_date,
      to_date: this.to_date,
      user_id: this.userIdParam ? this.userIdParam : 0
    }).subscribe(data => {
      this.cardMonthGroupBusSations = data;
      this.spinner.hide();
    });
  }

  showListUserModal() {
    this.listUserModal.show();
  }

  //function select user
  chooseUser(id: number) {
    this.users.map(
      (user) => {
        if (user.id === id) {
          this.searchUserName = user.fullname;
          this.userIdParam = user.id;
          this.staff_title = user.fullname;
          this.getDataReportCardMonthByGroupBusStations();
          this.listUserModal.hide();

        }
        if (id == 0) {
          this.searchUserName = this.translate.instant('BTN_VIEW_RECEIPT');
          this.userIdParam = id;
          this.staff_title = '';
          this.getDataReportCardMonthByGroupBusStations();
          this.listUserModal.hide();
        }
    });
  }

  showPrintPreview(){

    if (this.bsRangeValueParam === undefined || !this.bsRangeValueParam) {
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

  exportFile() {

    if ((this.bsRangeValueParam === undefined || !this.bsRangeValueParam)) {
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

    } else {
      this.apiReports.managerReportsExportCardMonthByGroupBusStationResponse({
        from_date: this.from_date,
        to_date: this.to_date,
        user_id: this.userIdParam ? this.userIdParam : 0
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
}
