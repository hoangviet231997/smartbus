import { Component, OnInit, ViewChild, ViewEncapsulation, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { AuthService, AdminUsersService, AdminCompaniesService, AdminRolesService } from '../../../api/services';
import { User, UserCreate, UserUpdate, } from '../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';
import { map } from 'rxjs/operators/map';
import { QtSocketService } from '../../../shared/qt-socket.service';
import { Subscription } from 'rxjs';
import { SocketComponent } from '../../../shared/socket-component';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class UsersComponent implements OnInit, AfterViewInit, SocketComponent {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public users: User[];
  public user: User;
  public userUpdate: UserUpdate;
  public userCreate: UserCreate;
  public isCreated = false;
  public isUpdated = false;

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  public companyItems: Array<any> = [];
  public valueSelectedCompany: any = {};
  public devCompanySelected: Array<any> = [];
  public emailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
  public numberPatten = /^-?[\d.]+(?:e-?\d+)?$/;
  private socketSubscription: Subscription;

  constructor(
      private apiUsers: AdminUsersService, private apiCompanies: AdminCompaniesService, private apiRoles: AdminRolesService,
      private apiAuths: AuthService, private qtSocket: QtSocketService, private translate: TranslateService,
      private spinner: NgxSpinnerService
    ) {
    this.user = new User();
    this.userCreate = new UserCreate();
    this.valueSelectedCompany = {};
    this.userUpdate = new UserCreate;
  }

  ngOnInit() {
  }

  socketDown() {
    // console.log('clean up blank card socket');
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
    // this.socketUp();
    // this.refreshView();
  }

  refreshView() {
    this.apiUsers.listUsersResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      }
    )).subscribe(
      resp => {
        this.users = resp.body;
        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
      }
    );

    this.apiCompanies.listCompanies({
      page: 0,
      limit: 9999
    }).subscribe(
      companies => {
        this.companyItems = [];
        for (let i = 0; i < companies.length; i++) {
          this.companyItems.push({
            id: companies[i].id,
            text: companies[i].name
          });
        }
      }
    );
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.refreshView();
  }

  showAddUserModal() {
    this.devCompanySelected = [];
    this.valueSelectedCompany = [];
    this.user = new User();
    this.addModal.show();
  }

  showEditUserModal(id: number, devCompanyId: any, devCompanyName: any) {
    this.spinner.show();
    this.devCompanySelected = [];
    this.valueSelectedCompany = [];
    this.apiUsers.getUser(id).subscribe(
      data => {
        this.userUpdate.id = data.id;
        this.userUpdate.username = data.username;
        this.userUpdate.email = data.email;
        this.userUpdate.fullname = data.fullname;
        this.userUpdate.birthday = data.birthday;
        this.userUpdate.address = data.address;
        this.userUpdate.sidn = data.sidn;
        this.userUpdate.phone = data.phone;
        this.userUpdate.gender = data.gender;
        this.userUpdate.role_id =  data.role_id;
        this.userUpdate.rfid = null;

        if (data.rfidcard !== null) {
          this.userUpdate.rfid = data.rfidcard.rfid;
        }

        this.devCompanySelected.push({
          id: devCompanyId,
          text: devCompanyName
        });
        this.valueSelectedCompany = {id: devCompanyId, text: devCompanyName};
        this.spinner.hide();
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA')});
      }
    );
  }

  addUser() {
    if (!this.userCreate.username) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_USER'), 'warning');
      return;
    }

    if (!this.userCreate.password) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PASS'), 'warning');
      return;
    }

    if (!this.userCreate.confirm_password) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_CONFIRM_PASS'), 'warning');
      return;
    }

    if (this.userCreate.password !== this.userCreate.confirm_password) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NOT_MATCH'), 'warning');
      return;
    }

    if (this.valueSelectedCompany.length <= 1) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_COMPANY'), 'warning');
      return;
    }

    if (this.userCreate.email) {
      if (!this.emailPattern.test(this.userCreate.email)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_EMAIL'), 'warning');
        return;
      }
    }

    if(this.userCreate.phone){
      if (!this.numberPatten.test(this.userCreate.phone)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PHONE'), 'warning');
        return;
      }
    }

    this.isCreated = true;
    this.apiUsers.createUser({
      username: this.userCreate.username,
      password: this.userCreate.password,
      confirm_password: this.userCreate.confirm_password,
      company_id: this.valueSelectedCompany.id,
      fullname: this.userCreate.fullname,
      gender: 1,
      birthday: '',
      phone: this.userCreate.phone,
      email: this.userCreate.email,
      address: '',
      rfid: this.userCreate.rfid,
      sidn: ''
    }).subscribe(
      res => {
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

    if (this.valueSelectedCompany.length <= 1) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_COMPANY'), 'warning');
      return;
    }

    if (this.userUpdate.email) {
      if (!this.emailPattern.test(this.userUpdate.email)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_EMAIL'), 'warning');
        return;
      }
    }

    if(this.userUpdate.phone){
      if (!this.numberPatten.test(this.userUpdate.phone)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PHONE'), 'warning');
        return;
      }
    }

    this.isUpdated = true;
    this.apiUsers.updateUser({
      id: this.userUpdate.id,
      company_id: this.valueSelectedCompany.id,
      rfid: this.userUpdate.rfid,
      email: this.userUpdate.email,
      fullname: this.userUpdate.fullname,
      birthday: this.userUpdate.birthday,
      address: this.userUpdate.address,
      sidn: this.userUpdate.sidn,
      phone: this.userUpdate.phone,
      gender: this.userUpdate.gender,
      role_id: this.userUpdate.role_id
    }).subscribe(
      res => {
        this.editModal.hide();
        this.userUpdate = new UserUpdate();
        this.isUpdated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err.status === 404) {
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
        } else if (err.status === 422) {
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
        }
        this.isUpdated = false;
      }
    );
  }

  deleteUser(id: number) {
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
        this.apiUsers.deleteUser(id).subscribe(
          res => {
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
    });
  }

  public refreshValueCompany(value: any) {
    this.valueSelectedCompany = value;
  }
}
