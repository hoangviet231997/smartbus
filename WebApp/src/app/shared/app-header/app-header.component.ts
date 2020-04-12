import { Component, OnInit, AfterViewInit, ViewChild } from '@angular/core';
import { User, UserUpdate, ChangePasswordUser } from '../../api/models';
import { ModalDirective } from 'ngx-bootstrap';
import { AdminUsersService, ManagerNotifiesService } from '../../api/services';
import { HttpErrorResponse } from '@angular/common/http';
import swal from 'sweetalert2';
import * as moment from 'moment';
import * as io from 'socket.io-client';
import { InjectionToken, FactoryProvider } from '@angular/core';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { ApiConfiguration } from '../../api/api-configuration';

var _this;

@Component({
  selector: 'app-header',
  templateUrl: './app-header.component.html',
  styleUrls: ['./app-header.component.css']
})

export class AppHeaderComponent implements OnInit, AfterViewInit {

  @ViewChild('editUserModal') public editUserModal: ModalDirective;
  @ViewChild('changePasswordModal') public changePasswordModal: ModalDirective;

  public user: User = null;
  public role = null;
  public userUpdate: UserUpdate;
  public isUpdated = false;
  public isChanged = false;
  public emailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
  public changePassForm: ChangePasswordUser;
  public birthday: Date;
  public now: Date = new Date();
  public user_down: any = null;
  public permissions: any[] = [];
  public notifies: any[] = [];
  public logo_company:any = null;

  public countNotifies = {
    readed: 0,
    unread: 0,
    all: 0
  }
  public socket;
  public timeOutSocketNotify = null;

  constructor(
    private apiUsers: AdminUsersService,
    private apiNotifies: ManagerNotifiesService,
    private router: Router,
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
    private config: ApiConfiguration
  ) {
    this.user = JSON.parse(localStorage.getItem('user'));
    this.role = JSON.parse(localStorage.getItem('user')).role.name
    // localStorage.getItem('role');
    this.userUpdate = new UserUpdate();
    this.changePassForm = new ChangePasswordUser();
    this.now = new Date();
  }

  ngOnInit() {
    this.user_down = localStorage.getItem('token_shadow');
    if (this.user) {
      if(this.user.company){
        this.logo_company = this.user.company.logo;
      }
      this.permissions = this.user.permissions;
    }

    _this = this;
    this.getNotifySocket();
  }

  ngAfterViewInit() {
    this.getDataNotifies();
  }

  getDataNotifies(){

    this.notifies = [];
    if(this.permissions['web_notifies']){
      this.apiNotifies.managerListNotifyShares().subscribe(data => {
        this.countNotifies = {
          readed: 0,
          unread: 0,
          all: 0
        }
        data.forEach(element => {
          var subject_data = JSON.parse(element.subject_data)
          var obj = {
            id : element.id,
            title : element.title,
            readed : element.readed,
            subject_id : element.subject_id,
            subject_data : subject_data,
            created_at: element.created_at,
            color : element.readed == 0 ? '#e0eef9' : '#fff',
            avatar: subject_data.avatar,
            key: element['key'],
            url_img: element['url_img'],
            route_link: element['route_link']
          };

          if(element['key'] == 'mbs_expired'){
            if(this.permissions['card_membership_card']){
              this.notifies.push(obj);
              if(element.readed == 0) this.countNotifies.unread += 1;
              this.countNotifies.all += 1;
            }
          }
          if(element['key'] == 'mbs_register'){
            if(this.permissions['card_membership_tmp']){
              this.notifies.push(obj);
              if(element.readed == 0) this.countNotifies.unread += 1;
              this.countNotifies.all += 1;
            }
          }
        });
      });
    }
  }

  showEditUser() {
    this.userUpdate.id = this.user.id;
    this.userUpdate.company_id = this.user.company_id;
    this.userUpdate.fullname = this.user.fullname;
    this.userUpdate.phone = this.user.phone;
    this.userUpdate.address = this.user.address;
    this.userUpdate.birthday = this.user.birthday;
    this.userUpdate.gender = this.user.gender;
    this.userUpdate.sidn = this.user.sidn;
    this.userUpdate.email = this.user.email;
    this.userUpdate.role_id = this.user.role_id;
    this.userUpdate.rfid = null;
    this.birthday = null;

    if (this.user.birthday) {
      this.birthday = moment(this.user.birthday).toDate();
    }

    if ( this.user.rfidcard !== null) {
      this.userUpdate.rfid = this.user.rfidcard.rfid;
    }

    this.editUserModal.show();
  }

  showChangePassword() {
    this.changePasswordModal.show();
  }

  updateUser() {

    if (!this.emailPattern.test(this.userUpdate.email)) {
      swal('Warning', ' Email is incorrect!', 'warning');
      return;
    }

    this.isUpdated = true;
    this.apiUsers.updateUser({
      id: this.userUpdate.id,
      company_id: this.userUpdate.company_id,
      fullname: this.userUpdate.fullname,
      phone: this.userUpdate.phone,
      address: this.userUpdate.address,
      birthday: this.birthday ? moment(this.birthday).format('YYYY-MM-DD') : null ,
      gender: this.userUpdate.gender,
      sidn: this.userUpdate.sidn,
      email: this.userUpdate.email,
      rfid: this.userUpdate.rfid,
      role_id: this.userUpdate.role_id
    }).subscribe(
      res => {
        this.editUserModal.hide();
        this.userUpdate = new UserUpdate();
        this.isUpdated = false;
        localStorage.setItem('user', JSON.stringify(res));
        swal('Updated successfully', '', 'success').then(
          (result) => {
            if (result.value) {
              swal('Please login to continue!', '', 'success').then((result_login) => {
                this.router.navigate(['/auth/signout']);
              });
            }
          }
        );
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: 'ERROR', text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: 'ERROR', text: 'Missing required field.'});
          }
        }
        this.isUpdated = false;
      }
    );
  }

  saveChangePassword() {
    if (!this.changePassForm.current_password) {
      swal('Warning', ' Please specify current password', 'warning');
      return;
    }

    if (!this.changePassForm.new_password) {
      swal('Warning', ' Please specify new password', 'warning');
      return;
    }

    if (!this.changePassForm.confirm_password) {
      swal('Warning', ' Please specify confirm password', 'warning');
      return;
    }

    if (this.changePassForm.new_password.length < 6) {
      swal('Warning', 'Your new password must be at least 6 characters', 'warning');
      return;
    }

    if (this.changePassForm.new_password !== this.changePassForm.confirm_password) {
      swal('Warning', 'New password  was different to the confirm password!', 'warning');
      return;
    }

    this.isChanged = true;
    this.apiUsers.changePasswordOfUser({
      userId: this.user.id,
      body: this.changePassForm
    }).subscribe(
      res => {
        this.changePasswordModal.hide();
        this.changePassForm = new ChangePasswordUser();
        this.isChanged = false;
        swal('Updated successfully', '', 'success').then( (result) => {
          swal('Please login to continue!', '', 'success').then((result_login) => {
            this.router.navigate(['/auth/signout']);
          });
        });
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: 'ERROR', text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: 'ERROR', text: 'Missing required field.'});
          }
        }
        this.isChanged = false;
      }
    );
  }

  maklAllNotifyReaded(){

    swal({
      title: this.translate.instant('SWAL_ERROR_SURE'),
      text: this.translate.instant('SWAL_ERROR_MARK_ALL_NOTIFIFY_READ'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: this.translate.instant('SWAL_OK'),
      cancelButtonText: this.translate.instant('SWAL_CANCEL')
    }).then((result) => {

      if (result.value) {
        this.spinner.show();
        this.apiNotifies.managerUpdateReadedNotify({
          id: 0,
          readed: 1
        }).subscribe(data => {
          this.ngAfterViewInit();
          this.spinner.hide()
        },
        err => {
          this.spinner.hide();
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPD_FAILD')});
        });
      }
    });

  }

  gotoNotifyByRouteLink(obj: any) {

    switch (obj.key) {
      case 'mbs_expired':
        if(this.permissions['card_membership_card']){
          if(obj.readed === 0){
            this.spinner.show();
            this.apiNotifies.managerUpdateReadedNotify({
              id: obj.id,
              readed: 1
            }).subscribe(data => {
              this.spinner.hide();
              this.ngAfterViewInit();
              this.router.navigate(['/'+obj.route_link], {queryParams: {subjectId: obj.subject_id}});
            });
          }else{
            this.router.navigate(['/'+obj.route_link], {queryParams: {subjectId: obj.subject_id}});
          }
        }else{
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTIFY_PERMISION'), 'warning');
          return;
        }
        break;

      case 'mbs_register':
        if(this.permissions['card_membership_tmp']){
          if(obj.readed === 0){
            this.spinner.show();
            this.apiNotifies.managerUpdateReadedNotify({
              id: obj.id,
              readed: 1
            }).subscribe(data => {
              this.spinner.hide();
              this.ngAfterViewInit();
              this.router.navigate(['/'+obj.route_link], {queryParams: {subjectId: obj.subject_id}});
            });
          }else{
            this.router.navigate(['/'+obj.route_link], {queryParams: {subjectId: obj.subject_id}});
          }
        }else{
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTIFY_PERMISION'), 'warning');
          return;
        }
        break;

      default:
        this.router.navigate(['/'+obj.route_link], {queryParams: {subjectId: obj.subject_id}});
        break;
    }
  }

  getNotifySocket() {

    this.socket = io(this.config.getStrUrlSocket());
    this.socket.on('emitDatadataRegisterMembershipMobile_' +this.user.company_id, function (mps_register) {
      clearTimeout(_this.timeOutSocketNotify)
      _this.timeOutSocketNotify = setTimeout(() => {
        if(mps_register){
          var data = JSON.parse(mps_register);
          var subject_data = JSON.parse(data.subject_data)
          var obj = {
            id : data.id,
            title : data.title,
            readed : data.readed,
            subject_id : data.subject_id,
            subject_data : subject_data,
            created_at: data.created_at,
            color : data.readed == 0 ? '#e0eef9' : '#fff',
            avatar: subject_data.avatar,
            key: data.key,
            url_img: data.url_img,
            route_link: data.route_link
          };
          const index: number = _this.notifies.indexOf(obj.id);
          if(index === -1){
            if(data.key == 'mbs_register'){
              if(_this.permissions['card_membership_tmp']){
                _this.notifies.unshift(obj);
                if(obj.readed == 0) _this.countNotifies.unread += 1;
                _this.countNotifies.all += 1;
              }
            }
          }
        }
      }, 100);
    });
  }
}
