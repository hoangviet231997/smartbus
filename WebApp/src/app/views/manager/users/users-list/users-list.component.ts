import { Component, OnInit, ViewChild, ViewEncapsulation, AfterViewInit,Pipe, PipeTransform } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import * as moment from 'moment';
import { ManagerUsersService, ManagerRolesService} from '../../../../api/services';
import { RolesService } from '../../../../shared/roles.service';
import { User, UserCreate, UserUpdate, Role } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';
import { map } from 'rxjs/operators/map';
import { QtSocketService } from '../../../../shared/qt-socket.service';
import { ActivityLogsService } from '../../../../shared/activity-logs.service';
import { Subscription } from 'rxjs';
import { SocketComponent } from '../../../../shared/socket-component';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { transliterate as tr, slugify } from 'transliteration';
import { parseDate } from 'ngx-bootstrap';


@Pipe({
  name: 'filterdata'
})
export class FilterdataPipe implements PipeTransform {

  transform(arrayUsers: User[], filter: string): any[] {
    if (!arrayUsers || !arrayUsers.length) {
      return [];
    }
    if (!filter) {
      return arrayUsers;
    }
    return arrayUsers.filter(user => {
      return tr(user.fullname).toLowerCase().indexOf(tr(filter).toLowerCase()) >= 0;
    });
  }

}

@Component({
  selector: 'app-users-list',
  templateUrl: './users-list.component.html',
  styleUrls: ['./users-list.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class UsersListComponent implements OnInit, AfterViewInit, SocketComponent {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public users: User[];
  public users_s: User[];
  public user: User;
  public user_down = null;
  public roleItems: Role[];
  public userCreate: UserCreate;
  public userUpdate: UserUpdate;
  public isCreated = false;
  public isUpdated = false;

  public style_search: any = '';
  public key_input: any= '';

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  public inputUserName = '';
  public timeoutSearchUser;
  public emailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
  public numberPatten = /^-?[\d.]+(?:e-?\d+)?$/;
  private socketSubscription: Subscription;
  public permissions:any[] = [];

  constructor(
    public roles: RolesService,
    private apiUsers: ManagerUsersService,
    private apiRoles: ManagerRolesService,
    private qtSocket: QtSocketService,
    private activityLogs: ActivityLogsService,
    private translate: TranslateService,
    private spinner: NgxSpinnerService
  ) {
    this.user = new User();
    this.userCreate = new UserCreate();
    this.userUpdate = new UserUpdate();
  }

  ngOnInit() {
    this.user_down = localStorage.getItem('token_shadow');
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  socketDown() {
    // console.log('clean up user-list socket');
    // this.socketSubscription.unsubscribe();
  }

  socketUp() {
    // this.socketSubscription = this.qtSocket.onData().subscribe(
    //   data => {
    //     console.log('from subscription: ', data.toString());
    //     if (this.addModal.show) {
    //       this.userCreate.rfid = data.toString().split(':').pop();
    //     } else if (this.editModal.show) {
    //       this.userUpdate.rfid = data.toString().split(':').pop();
    //     }
    //   }
    // );
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.getListUsers();
    this.apiRoles.managerListRoles().subscribe(
      roles => {
        this.roleItems = roles;
      }
    );
  }

  getListUsers(){
    this.spinner.show();
    this.apiUsers.managerListUsersResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {

        this.key_input = '';
        this.users = resp.body;

        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
          this.spinner.hide();
      }
    );
  }

  getDataUserByInputName() {

    clearTimeout(this.timeoutSearchUser);

    if (this.style_search == '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_USER_SEARCH_ACTIVED'), 'warning');
      return;
    }

    this.timeoutSearchUser = setTimeout(() => {
      if (this.key_input !== '') {
        this.apiUsers.managerListUserInput({
          style_search: this.style_search,
          key_input: this.key_input
        }).pipe(
          map(_r => {
            return _r;
          })
        ).subscribe(data => {
          this.users = data;
        });
      } else {
        this.refreshView();
      }
    }, 500);
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.getListUsers();
  }

  showAddUserModal() {
    this.user = new User();
    this.userCreate = new UserCreate();
    this.addModal.show();
  }

  showEditUserModal(id: number, devRoleId: any, devRoleName: any) {

    this.spinner.show();
    this.apiUsers.managerGetUser(id).subscribe(
      data => {

        this.userUpdate.id = data.id;
        this.userUpdate.username = data.username;
        this.userUpdate.email = data.email;
        this.userUpdate.fullname = data.fullname;
        this.userUpdate.birthday = data.birthday ? moment(data.birthday).format('YYYY-MM-DD') : null;
        this.userUpdate.address = data.address;
        this.userUpdate.sidn = data.sidn;
        this.userUpdate.phone = data.phone;
        this.userUpdate.gender = data.gender;
        this.userUpdate.rfid = null;
        this.userUpdate.role_id = data.role_id;

        if (data.rfidcard !== null) {
          this.userUpdate.rfid = data.rfidcard.rfid;
        }

        this.spinner.hide();
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
      }
    );
  }

  checkYearLeap(year: number) {

      if (year % 400 == 0)
          return true;

      if (year % 4 == 0 && year % 100 != 0)
          return true;

      return false;
  }

  checkBirthday(birthday: any){

  }

  addUser() {

    if (this.userCreate.role_id === undefined || this.userCreate.role_id <= 0) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_ROLE'), 'warning');
      return;
    }

    if (!this.userCreate.username) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_USER'), 'warning');
      return;
    }

    if (!this.userCreate.password) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_PASS'), 'warning');
      return;
    }

    if (!this.userCreate.confirm_password) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_CONFIRM_PASS'), 'warning');
      return;
    }

    if (this.userCreate.password !== this.userCreate.confirm_password) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOT_MATCH'), 'warning');
      return;
    }

    if (this.userCreate.email) {
      if (!this.emailPattern.test(this.userCreate.email)) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_EMAIL'), 'warning');
        return;
      }
    }

    if(this.userCreate.phone){
      if (!this.numberPatten.test(this.userCreate.phone) || (this.userCreate.phone.length < 10 || this.userCreate.phone.length > 11)) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_PHONE'), 'warning');
        return;
      }
    }

    let indexRole = this.roleItems.findIndex( e => ((e.id == this.userCreate.role_id) && (e.name === "driver" || e.name === "subdriver")));
    if(indexRole != -1){
      if(!this.userCreate.rfid){
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_RFID'), 'warning');
        return;
      }
      if(this.userCreate.rfid){
        if(this.userCreate.rfid.length != 8){
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_RFID_LENGTH_8'), 'warning');
          return;
        }
      }
    }


    // if(this.userCreate.birthday){

    //   const date_now =  new Date();

    //   const date_year =  this.userCreate.birthday.substr(0, 4);
    //   const date_month =  this.userCreate.birthday.substr(5, 2);
    //   const date_day =  this.userCreate.birthday.substr(8, 2);

    //   if(parseDate(this.userCreate.birthday) >= date_now ){
    //     swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_BIRTHDAY'), 'warning');
    //     return;
    //   }

    //   if(parseInt(date_month) == 2){
    //     if(this.checkYearLeap(parseInt(date_month))){
    //       if(parseInt(date_day) > 29){
    //         swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_BIRTHDAY'), 'warning');
    //         return;
    //       }
    //     }else{
    //       if(parseInt(date_day) > 28){
    //         swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_BIRTHDAY'), 'warning');
    //         return;
    //       }
    //     }
    //   }

    //   if(parseInt(date_month) % 2 == 0){
    //     if(parseInt(date_day) > 30){
    //       swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_BIRTHDAY'), 'warning');
    //       return;
    //     }
    //   } else{
    //     if(parseInt(date_day) > 31){
    //       swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_BIRTHDAY'), 'warning');
    //       return;
    //     }
    //   }
    // }

    this.isCreated = true;
    this.apiUsers.managerCreateUser({
      username: this.userCreate.username,
      password: this.userCreate.password,
      confirm_password: this.userCreate.confirm_password,
      role_id: this.userCreate.role_id,
      fullname: this.userCreate.fullname,
      gender: 1,
      birthday: this.userCreate.birthday ? this.userCreate.birthday : null,
      phone: this.userCreate.phone,
      email: this.userCreate.email,
      address: this.userCreate.address,
      rfid: this.userCreate.rfid ? this.userCreate.rfid.toUpperCase() : null,
      sidn: ''
    }).subscribe(
      res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'create';
        activity_log['subject_type'] = 'user';
        if(this.userCreate){
          delete this.userCreate['confirm_password'];
          delete this.userCreate['password'];
        }
        activity_log['subject_data'] = this.userCreate ? JSON.stringify({
          username: this.userCreate.username,
          password: this.userCreate.password,
          confirm_password: this.userCreate.confirm_password,
          role_id: this.userCreate.role_id,
          fullname: this.userCreate.fullname,
          gender: 1,
          birthday: this.userCreate.birthday ? this.userCreate.birthday : null,
          phone: this.userCreate.phone,
          email: this.userCreate.email,
          address: this.userCreate.address,
          rfid: this.userCreate.rfid ? this.userCreate.rfid.toUpperCase() : null,
          sidn: ''
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.addModal.hide();
        this.userCreate = new UserCreate();
        this.isCreated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD')});
          }
        }
        this.isCreated = false;
      }
    );
  }

  editUser() {

    if (this.userUpdate.role_id === undefined || this.userUpdate.role_id <= 0) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_ROLE'), 'warning');
      return;
    }

    if (this.userUpdate.email) {
      if (!this.emailPattern.test(this.userUpdate.email)) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_EMAIL'), 'warning');
        return;
      }
    }

    if(this.userUpdate.phone){
      if (!this.numberPatten.test(this.userUpdate.phone) || (this.userUpdate.phone.length < 10 || this.userUpdate.phone.length > 11)) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_PHONE'), 'warning');
        return;
      }
    }

    let indexRole = this.roleItems.findIndex( e => ((e.id == this.userUpdate.role_id) && (e.name === "driver" || e.name === "subdriver")));
    if(indexRole != -1){
      if(!this.userUpdate.rfid){
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_RFID'), 'warning');
        return;
      }
      if(this.userUpdate.rfid){
        if(this.userUpdate.rfid.length != 8){
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_RFID_LENGTH_8'), 'warning');
          return;
        }
      }
    }

    this.isUpdated = true;
    this.apiUsers.managerUpdateUser({
      id: this.userUpdate.id,
      role_id: this.userUpdate.role_id,
      rfid: this.userUpdate.rfid ? this.userUpdate.rfid.toUpperCase() : null,
      email: this.userUpdate.email,
      fullname: this.userUpdate.fullname,
      birthday: this.userUpdate.birthday ? this.userUpdate.birthday : null,
      address: this.userUpdate.address,
      sidn: this.userUpdate.sidn,
      phone: this.userUpdate.phone,
      gender: this.userUpdate.gender,
    }).subscribe(
      res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'update';
        activity_log['subject_type'] = 'user';
        activity_log['subject_data'] = this.userUpdate ? JSON.stringify({
          id: this.userUpdate.id,
          role_id: this.userUpdate.role_id,
          rfid: this.userUpdate.rfid ? this.userUpdate.rfid.toUpperCase() : null,
          email: this.userUpdate.email,
          fullname: this.userUpdate.fullname,
          birthday: this.userUpdate.birthday ? this.userUpdate.birthday : null,
          address: this.userUpdate.address,
          sidn: this.userUpdate.sidn,
          phone: this.userUpdate.phone,
          gender: this.userUpdate.gender,
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.editModal.hide();
        this.userUpdate = new UserUpdate();
        this.isUpdated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
          }
        }
        this.isUpdated = false;
      }
    );
  }

  deleteUser(id: number, role: string) {
    swal({
      title: this.translate.instant('SWAL_ERROR_SURE'),
      text: this.translate.instant('SWAL_ERROR_REMOVE'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: this.translate.instant('SWAL_OK'),
      cancelButtonText: this.translate.instant('SWAL_CANCEL')
    }).then((result) => {

      if (result.value) {
        this.spinner.show();
        if (role === 'manager') {
          this.spinner.hide();
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_PMS')});
        } else {
          this.apiUsers.managerDeleteUser(id).subscribe(
            res => {

              //call service create activity log
              var activity_log: any = [];
              activity_log['user_down'] =  this.user_down ? this.user_down : null;
              activity_log['action'] = 'delete';
              activity_log['subject_type'] = 'user';
              activity_log['subject_data'] = JSON.stringify({user_id: id, role:role});
              var activityLog = this.activityLogs.createActivityLog(activity_log);

              this.refreshView();
              this.spinner.hide();
              swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
            },
            err => {
              this.spinner.hide();
              swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
            }
          );
        }
      }
    });
  }

  disableUser(id: number, role: string, disable: number){

    swal({
      title: this.translate.instant('SWAL_ERROR_SURE'),
      text: this.translate.instant('SWAL_ERROR_UPDATE_USR'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: this.translate.instant('SWAL_OK'),
      cancelButtonText: this.translate.instant('SWAL_CANCEL')
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        if (role === 'manager') {
          this.spinner.hide();
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_PMS')});
        } else {
          this.apiUsers.managerActionUser({
            id: id,
            disable
          }).subscribe(
            res => {

              //call service create activity log
              var activity_log: any = [];
              activity_log['user_down'] =  this.user_down ? this.user_down : null;
              activity_log['action'] = (disable == 0) ? 'disable' : 'enable';
              activity_log['subject_type'] = 'user';
              activity_log['subject_data'] = JSON.stringify({user_id: id, role:role, disable: disable});
              var activityLog = this.activityLogs.createActivityLog(activity_log);

              this.refreshView();
              this.spinner.hide();
              swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
            },
            err => {
              this.spinner.hide();
              swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
            }
          );
        }
      }
    });
  }
}
