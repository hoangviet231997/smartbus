import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { AdminPartnersService, AdminCompaniesService} from '../../../../api/services';
import { PartnerForm } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';
import { MouseEvent } from '@agm/core';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import * as moment from 'moment';

@Component({
  selector: 'app-partners',
  templateUrl: './partners.component.html',
  styleUrls: ['./partners.component.css']
})
export class PartnersComponent implements OnInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public partners: any = [];
  public partnerCreate: PartnerForm;
  public partnerUpdate: PartnerForm;
  public isCreated = false;
  public isUpdated = false;
  public emailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
  public numberPatten = /^-?[\d.]+(?:e-?\d+)?$/;
  public date_now : Date;
  public isCheckUpdate = false;
  public is_checked = 0;

  public companies: any = [];
  public companyCreate: any = [];
  public companyUpdate: any = [];

  constructor(
    private apiPartners: AdminPartnersService,
    private apiCompanies: AdminCompaniesService,
    private translate: TranslateService, private spinner: NgxSpinnerService
  ) {
    this.date_now = new Date();
    this.partnerCreate = new PartnerForm();
    this.partnerUpdate = new PartnerForm();
  }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {

    this.apiPartners.listPartners().subscribe(
      partner => {
        this.partners = partner;
      }
    );

    this.apiCompanies.listCompanies({
      page: 0,
      limit: 9999
    }).subscribe(
      data => {
        this.companies = data;
      }
    );

  }
  showAddPartnerModal() {

    this.addModal.show();
    this.companyCreate = [];
  }

  changeCheckedCompany(event, id: number, type){


    if(type == 0){

      if (event.currentTarget.checked) {

        this.companyCreate.push(id);

      }else{

        const index: number = this.companyCreate.indexOf(id);

        if (index !== -1) {
          this.companyCreate.splice(index, 1);
        }
      }
    }

    if(type == 1){

      if (event.currentTarget.checked) {

        this.companyUpdate.push(id);

      }else{

        const index: number = this.companyUpdate.indexOf(id);

        if (index !== -1) {
          this.companyUpdate.splice(index, 1);
        }
      }
    }

  }

  showEditPartnerModal(id: number) {

    this.spinner.show();
    this.isCheckUpdate = false;
    this.companyUpdate = [];

    this.apiPartners.getParnertById(id).subscribe(
      data => {
        this.partnerUpdate.id = data.id;
        this.partnerUpdate.company_name = data.company_name;
        this.partnerUpdate.company_fullname = data.company_fullname;
        this.partnerUpdate.address = data.address;
        this.partnerUpdate.url = data.url;
        this.partnerUpdate.phone = data.phone;
        this.partnerUpdate.email = data.email;

        if(data.group_company !== null){
          this.companyUpdate = JSON.parse(data.group_company);
        }
        this.spinner.hide();
        this.editModal.show();
        this.isCheckUpdate = false;
      },
      err => {
        this.spinner.hide();
        this.isCheckUpdate = false;
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_MODEL')});
      }
    );
  }

  addPartner() {

    let daysForm = moment(this.date_now).format('DD').toString();
    let monthForm = moment(this.date_now).format('MM').toString();
    let yearsForm = moment(this.date_now).format('YYYY').toString();
    let partner_code = "DFMSMBUS"+yearsForm+monthForm+daysForm;

    if (!this.partnerCreate.company_name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }

    if(this.partnerCreate.email){
      if (!this.emailPattern.test(this.partnerCreate.email)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_EMAIL'), 'warning');
        return;
      }
    }

    if(this.partnerCreate.phone){

      if (!this.numberPatten.test(this.partnerCreate.phone)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PHONE'), 'warning');
        return;
      }
    }

    this.isCreated = true;

    this.apiPartners.createPartner({
      company_name: this.partnerCreate.company_name,
      company_fullname: this.partnerCreate.company_fullname,
      address: this.partnerCreate.address,
      url: this.partnerCreate.url,
      phone: this.partnerCreate.phone,
      email: this.partnerCreate.email,
      partner_code: partner_code,
      group_company: this.companyCreate
    }).subscribe(
      res => {
        this.addModal.hide();
        this.partnerCreate = new PartnerForm();
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

  editPartner() {

    if (!this.partnerUpdate.company_name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }

    if(this.partnerUpdate.email){
      if (!this.emailPattern.test(this.partnerUpdate.email)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_EMAIL'), 'warning');
        return;
      }
    }

    if(this.partnerUpdate.phone){
      if (!this.numberPatten.test(this.partnerUpdate.phone)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PHONE'), 'warning');
        return;
      }
    }

    this.isUpdated = true;
    this.apiPartners.updatePartner({
      id: this.partnerUpdate.id,
      company_name: this.partnerUpdate.company_name,
      company_fullname: this.partnerUpdate.company_fullname,
      address: this.partnerUpdate.address,
      url: this.partnerUpdate.url,
      phone: this.partnerUpdate.phone,
      email: this.partnerUpdate.email,
      group_company: this.companyUpdate,
      is_check: this.is_checked
    }).subscribe(
      data => {
        this.editModal.hide();
        this.partnerUpdate =  new PartnerForm();
        this.isUpdated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
        this.is_checked = 0;
        this.isCheckUpdate = false
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
        this.isCheckUpdate = false
        this.is_checked = 0;
      }
    );
  }

  onChangedUpdateAppKey(value){

    if(value.currentTarget.checked){
      this.is_checked = 1
    }else{
      this.is_checked = 0
    }
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
        this.apiPartners.deletePartner(id).subscribe(
          res => {
            this.refreshView();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
          }
        );
      }
    });
  }
}
