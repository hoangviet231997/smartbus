
import { Component, OnInit, ViewChild, AfterViewInit, ViewEncapsulation } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { AdminPartnersService, AdminCompaniesService} from '../../../../api/services';
import { PartnerAccountForm } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';
import { MouseEvent } from '@agm/core';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import * as moment from 'moment';

@Component({
  selector: 'app-partners-account',
  templateUrl: './partners-account.component.html',
  styleUrls: ['./partners-account.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class PartnersAccountComponent implements OnInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public partners_account: any = [];
  public partnerAccountCreate: PartnerAccountForm;
  public partnerAccountUpdate: PartnerAccountForm;
  public isCreated = false;
  public isUpdated = false;
  public date_now : Date;
  public companyItems: any = [];
  public companyActiveSelected:any = [];
  public show_password: any = 0;

  constructor(
    private apiPartnerAccount: AdminPartnersService,
    private apiCompanies: AdminCompaniesService,
    private translate: TranslateService,
    private spinner: NgxSpinnerService
  ) {
    this.date_now = new Date();
    this.partnerAccountCreate = new PartnerAccountForm();
    this.partnerAccountUpdate = new PartnerAccountForm();
  }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.getListPartnerAccount();
    this.getListCompanies();
  }

  getListPartnerAccount(){
    this.apiPartnerAccount.listPartnerAccounts().subscribe(
      partner => {
        this.partners_account = partner;
      }
    );
  }

  getListCompanies(){
    this.apiCompanies.listCompanies({
      page: 0,
      limit: 9999
    }).subscribe(
      data => {
        this.companyItems = [];
        for (let i = 0; i < data.length; i++) {
          this.companyItems.push({
            id: data[i].id,
            text: data[i].name
          });
        }
      }
    );
  }

  refreshValueCompany(value:any):void{
    this.partnerAccountCreate.company_id = value['id'];
  }

  selectedValueCompany(value:any){
    this.partnerAccountCreate.company_id = value['id'];
  }

  removedValueCompany(value:any){
    this.partnerAccountCreate.company_id = null;
  }


  showAddPartnerModal() {

    this.addModal.show();
    var temp =  (<HTMLInputElement>document.getElementById('passwordCreate'));
    temp.type = "password";
    this.show_password = 0;
    this.partnerAccountCreate = new PartnerAccountForm();
    this.companyActiveSelected = [];
  }

  showEditPartnerModal(id: number) {

    this.spinner.show();
    this.show_password = 0;
    var temp =  (<HTMLInputElement>document.getElementById('passwordUpdate'));
    temp.type = "password";

    this.apiPartnerAccount.getParnertAccountById(id).subscribe(
      data => {
        this.partnerAccountUpdate.id = data.id;
        this.partnerAccountUpdate.name = data.name;
        this.partnerAccountUpdate.company_id = data.company_id;
        this.partnerAccountUpdate.url_api = data.url_api;
        this.partnerAccountUpdate.partner_code = data.partner_code;
        this.partnerAccountUpdate.username_login = data.username_login;
        this.partnerAccountUpdate.password_login = data.password_login;
        this.partnerAccountUpdate.public_key = data.public_key;
        this.partnerAccountUpdate.private_key = data.private_key;
        this.partnerAccountUpdate.description = data.description;

        this.companyActiveSelected = [{
          id: data.company_id,
          text: data['company'].name
        }]

        this.spinner.hide();
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_MODEL')});
      }
    );
  }

  addPartnerAccount() {

    if (!this.partnerAccountCreate.company_id) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_COMPANY'), 'warning');
      return;
    }

    if (!this.partnerAccountCreate.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_NAME'), 'warning');
      return;
    }

    if (!this.partnerAccountCreate.partner_code) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_PARTNER_CODE'), 'warning');
      return;
    }

    if (!this.partnerAccountCreate.url_api) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_URL_API'), 'warning');
      return;
    }

    if (!this.partnerAccountCreate.username_login) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_UERNAME_LOGIN'), 'warning');
      return;
    }

    if (!this.partnerAccountCreate.password_login) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_PASSWORD_LOGIN'), 'warning');
      return;
    }

    if (!this.partnerAccountCreate.public_key) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_PUBLIC_KEY'), 'warning');
      return;
    }

    this.isCreated = true;
    this.apiPartnerAccount.createPartnerAccount({
      name: this.partnerAccountCreate.name,
      company_id: this.partnerAccountCreate.company_id,
      url_api: this.partnerAccountCreate.url_api,
      partner_code: this.partnerAccountCreate.partner_code,
      username_login: this.partnerAccountCreate.username_login,
      password_login: this.partnerAccountCreate.password_login,
      public_key: this.partnerAccountCreate.public_key,
      private_key: this.partnerAccountCreate.private_key,
      description: this.partnerAccountCreate.description
    }).subscribe(
      res => {
        this.addModal.hide();
        this.partnerAccountCreate = new PartnerAccountForm();
        this.isCreated = false;
        this.getListPartnerAccount();
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

  editPartnerAccount() {

    if (!this.partnerAccountUpdate.company_id) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_COMPANY'), 'warning');
      return;
    }

    if (!this.partnerAccountUpdate.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_NAME'), 'warning');
      return;
    }

    if (!this.partnerAccountUpdate.partner_code) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_PARTNER_CODE'), 'warning');
      return;
    }

    if (!this.partnerAccountUpdate.url_api) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_URL_API'), 'warning');
      return;
    }

    if (!this.partnerAccountUpdate.username_login) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_UERNAME_LOGIN'), 'warning');
      return;
    }

    if (!this.partnerAccountUpdate.password_login) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_PASSWORD_LOGIN'), 'warning');
      return;
    }

    if (!this.partnerAccountUpdate.public_key) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PARTNER_ACCOUNT_PUBLIC_KEY'), 'warning');
      return;
    }

    this.isUpdated = true;
    this.apiPartnerAccount.updatePartnerAccount({
      id: this.partnerAccountUpdate.id,
      name: this.partnerAccountUpdate.name,
      company_id: this.partnerAccountUpdate.company_id,
      url_api: this.partnerAccountUpdate.url_api,
      partner_code: this.partnerAccountUpdate.partner_code,
      username_login: this.partnerAccountUpdate.username_login,
      password_login: this.partnerAccountUpdate.password_login,
      public_key: this.partnerAccountUpdate.public_key,
      private_key: this.partnerAccountUpdate.private_key ? this.partnerAccountUpdate.private_key : null,
      description: this.partnerAccountUpdate.description ? this.partnerAccountUpdate.description : null,
    }).subscribe(
      data => {
        this.editModal.hide();
        this.partnerAccountUpdate = new PartnerAccountForm();
        this.isUpdated = false;
        this.getListPartnerAccount();
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

  deletePartner(id: number) {
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
        this.apiPartnerAccount.deletePartnerAccount(id).subscribe(
          res => {
            this.getListPartnerAccount();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
          }
        );
      }
    });
  }

  showPassword(type){
    if(type == 0){
      var temp = (<HTMLInputElement>document.getElementById("passwordCreate"));
      if (temp.type === "password") {
          this.show_password = 1;
          temp.type = "text";
      }else {
          this.show_password = 0;
          temp.type = "password";
      }
    }else{
      var temp = (<HTMLInputElement>document.getElementById("passwordUpdate"));
      if (temp.type === "password") {
          this.show_password = 1;
          temp.type = "text";
      }else {
          this.show_password = 0;
          temp.type = "password";
      }
    }
  }
}
