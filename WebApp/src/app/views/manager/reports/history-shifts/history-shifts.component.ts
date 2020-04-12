import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpErrorResponse } from '@angular/common/http';
import { ManagerHistoryShiftsService, ManagerUsersService } from '../../../../api/services';
import { HistoryShift, User, UserSearch, HistoryShiftSearch } from '../../../../api/models';
import { map } from 'rxjs/operators/map';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import * as moment from 'moment';
import { transliterate as tr, slugify } from 'transliteration';
import { TranslateService } from '@ngx-translate/core';
import { saveAs } from 'file-saver/FileSaver';

@Component({
  selector: 'app-history-shifts',
  templateUrl: './history-shifts.component.html',
  styleUrls: ['./history-shifts.component.css']
})
export class HistoryShiftsComponent implements OnInit, AfterViewInit {

  @ViewChild('listUserModal') public listUserModal: ModalDirective;
  @ViewChild('listUserCollectedModal') public listUserCollectedModal: ModalDirective;

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  public historyShifts: HistoryShift[] = [];
  public currentTime: Date = new Date();
  public maxDate: Date;
  public dateDefault: any = [];

  public user_id: any;
  public user_collected_id: any;

  public inputUserName: string;
  public inputUserCollectedName: string;

  public users: User[];
  public user_collectes: User[];

  public userSearch: UserSearch;

  public searchUserName = '';
  public searchUserCollectedName = '';
  public isLoading = false;

  public total_collect: any;
  public total_all: any;
  public isExport: any;
  public dataExport:any = null;

  public permissions:any = [];

  constructor(
    private spinner: NgxSpinnerService,
    private apiHistoryShifts: ManagerHistoryShiftsService,
    private translate: TranslateService,
    private apiUsers: ManagerUsersService
  ) { 
      this.userSearch = new UserSearch();
      this.maxDate = new Date();
  }

  ngOnInit() {
    //this.receiptForm.date = this.currentTime.toISOString().slice(0, 10);
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.getUsers();
  }

  getUsers() {
    this.apiUsers.managerListAllUser().subscribe(
      resp => {
        this.inputUserName = '';
        this.inputUserCollectedName = '';

        this.users = resp.filter(
          (user) => {
            if (user.role.name === 'driver' || user.role.name === 'subdriver') {
              return user;
            }
        });

        this.user_collectes = resp.filter(
          (user) => {
            if (user.role.name === 'collecter') {
              return user;
            }
        });
      }
    );
   
  }

  //function select user
  chooseUser(id: number) {

    this.users.map(
      (user) => {
        if (user.id === id) {
          this.searchUserName = user.fullname;
          this.user_id = user.id;
          this.listUserModal.hide();
          this.searchHistoryShift();
        }
    });

    this.user_collectes.map(
      (user) => {
        if (user.id === id) {
          this.searchUserCollectedName = user.fullname;
          this.user_collected_id = user.id;
          this.listUserCollectedModal.hide();
          this.searchHistoryShift();
        }
    });
  }

  searchHistoryShift() {

    // this.isLoading = true;
    var data_from = null;
    var data_to = null;
    var user_id = null;
    var user_collected_id = null;
    this.dataExport = null;

    if (this.dateDefault === undefined || !this.dateDefault || this.dateDefault.length === null || this.dateDefault.length === 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      this.isLoading = false;
      return;
    }
    
    if(this.dateDefault.length > 0) { 
      data_from = moment(this.dateDefault[0]).format('YYYY-MM-DD');
      data_to = moment(this.dateDefault[1]).format('YYYY-MM-DD');

      var date1 = new Date(data_from);
      var date2 = new Date(data_to);
      var timeDiff = Math.abs(date2.getTime() - date1.getTime());
      var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24)); 
      if (diffDays > 1) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_CHOSE_TWO_DATE'), 'warning');
        return;
      }
    }

    if(this.user_id) { user_id = this.user_id; }
    if(this.user_collected_id) { user_collected_id = this.user_collected_id; }
    this.spinner.show();
    this.apiHistoryShifts.managerHistoryShiftSearch({
      date_form: data_from,
      date_to: data_to,
      user_id: user_id,
      user_collected_id: user_collected_id
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {
        this.historyShifts = resp['data'];
        this.total_collect = resp['collecte_group'];
        this.total_all = resp['total_all'];
        if(this.historyShifts.length == 0)  {this.isLoading = true;}
        this.spinner.hide();
      }
    );
  }

  exportFile() {
    let data_from = null;
    let data_to = null;
    let user_id = null;
    let user_collected_id = null;

    if (this.dateDefault.length > 0) {
      data_from = moment(this.dateDefault[0]).format('YYYY-MM-DD');
      data_to = moment(this.dateDefault[1]).format('YYYY-MM-DD');
    }
    if (this.user_id) { user_id = this.user_id; }
    if (this.user_collected_id) { user_collected_id = this.user_collected_id; }

    if (!data_from && !data_to && !user_id && !user_collected_id) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_CHOSE_DATE_OR_STAFF'), 'warning');
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
      this.apiHistoryShifts.managerHistoryShiftExportResponse({
        date_form: data_from,
        date_to: data_to,
        user_id: user_id,
        user_collected_id: user_collected_id
      }).pipe(
        map(_r => {
          return _r;
        })
      ).subscribe(
        resp => {

          this.dataExport = resp;
          this.isExport = false;
          // get filename
          const contentDispositionHeader: string = resp.headers.get('Content-Disposition');
          const parts: string[] = contentDispositionHeader.split(';');
          const filename = parts[1].split('=')[1];

          // convert resp
          const byteCharacters = atob(resp.body);
          const byteNumbers = new Array(byteCharacters.length);
          for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
          }
          const byteArray = new Uint8Array(byteNumbers);
          const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel' });

          saveAs(blob, filename);
        }
      );
    }
  }
  
  showListUserModal() {
    this.listUserModal.show();
  }

  showListUserCollectedModal(){
    this.listUserCollectedModal.show();
  }
}
