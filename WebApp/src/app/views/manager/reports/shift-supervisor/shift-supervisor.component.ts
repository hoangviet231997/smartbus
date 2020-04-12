import { Component, OnInit, ViewChild, ViewEncapsulation, AfterViewInit, Pipe, PipeTransform } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import * as moment from 'moment';
import { ManagerReportsService, ManagerUsersService, ManagerCompaniesService } from '../../../../api/services';
import { User } from '../../../../api/models';
import { map } from 'rxjs/operators/map';
import { HttpErrorResponse } from '@angular/common/http';
import { transliterate as tr, slugify } from 'transliteration';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { saveAs } from 'file-saver/FileSaver';

@Pipe({ name: 'filterUserSupervisor' })
export class FilterUserSupervisor implements PipeTransform {
  public transform(arrayUsers: User[], filter: string): any[] {
    if (!arrayUsers || !arrayUsers.length) { return []; }
    if (!filter) { return arrayUsers; }
    return arrayUsers.filter(user => { return tr(user.fullname).toLowerCase().indexOf(tr(filter).toLowerCase()) >= 0; });
  }
}

@Component({
  selector: 'app-shift-supervisor',
  templateUrl: './shift-supervisor.component.html',
  styleUrls: ['./shift-supervisor.component.css']
})
export class ShiftSupervisorComponent implements OnInit, AfterViewInit {

  @ViewChild('listUserModal') public listUserModal: ModalDirective;

  public bsRangeValue: Date[];
  public maxDate: Date;
  public shift_supervisor: any = [];
  public users: User[];
  public permissions:any[] = [];
  public inputUserName: string;
  public searchUserName = '';
  public searchUserID: any = 0;
  public daysForm: any;
  public monthForm: any;
  public yearsForm: any;
  public daysTo: any;
  public monthTo: any;
  public yearsTo: any;
  public company: any;
  public isLoading = false;
  public isExport = false;
  public dataExport:any = null;

  constructor(
    private translate: TranslateService,
    private apiReports: ManagerReportsService,
    private spinner : NgxSpinnerService,
    private apiCompanies: ManagerCompaniesService,
    private apiUsers: ManagerUsersService,
  ) {
    this.maxDate = new Date();
  }

  ngOnInit() { if (localStorage.getItem('user')) this.permissions = JSON.parse(localStorage.getItem('user')).permissions; }

  ngAfterViewInit() {
    this.refreshView();
    this.getComapny();
  }

  refreshView() {
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
        this.users = resp.body.filter( (user) => {  if (user.role.name === 'staff') return user; });
      }
    );
  }

  getComapny() {
    this.apiCompanies.managerGetCompany().subscribe( data => { this.company = data; });
  }

  getDataShiftSupervisor(){

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

    if(this.bsRangeValue.length > 0 ){

      this.daysForm = moment(this.bsRangeValue[0]).format('DD').toString();
      this.monthForm = moment(this.bsRangeValue[0]).format('MM').toString();
      this.yearsForm = moment(this.bsRangeValue[0]).format('YYYY').toString();

      this.daysTo = moment(this.bsRangeValue[1]).format('DD').toString();
      this.monthTo = moment(this.bsRangeValue[1]).format('MM').toString();
      this.yearsTo = moment(this.bsRangeValue[1]).format('YYYY').toString();
    }

    this.spinner.show();
    this.apiReports.managerReportsViewShiftSupervisor({
      to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
      user_id: this.searchUserID
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      data => {
        this.shift_supervisor = data;
        this.spinner.hide();
    });
  }

  showListUserModal() {
    this.listUserModal.show();
  }

  chooseUser(id: number) {
    this.users.map(
      (user) => {
        if (user.id === id) {
          this.searchUserName = user.fullname;
          this.searchUserID = user.id;
          this.listUserModal.hide();
          this.getDataShiftSupervisor();
        }
      });
  }

  showPrintPreview(){

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
      this.apiReports.managerReportsExportShiftSupervisorResponse({
        to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
        from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
        user_id: this.searchUserID
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
