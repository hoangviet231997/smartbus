import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap';
import swal from 'sweetalert2';

import { ManagerCompaniesService } from '../../../api/services';
import { Company, CompanyUpdate } from '../../../api/models';
import { TranslateService } from '@ngx-translate/core';
import { ActivityLogsService } from '../../../shared/activity-logs.service';

@Component({
  selector: 'app-profiles',
  templateUrl: './profiles.component.html',
  styleUrls: ['./profiles.component.css']
})
export class ProfilesComponent implements OnInit, AfterViewInit {

  @ViewChild('editModal') public editModal: ModalDirective;

  public companyUpdate: CompanyUpdate;
  public isUpdated = false;
  public emailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
  public numberPatten = /^-?[\d.]+(?:e-?\d+)?$/;
  public permissions:any[] = [];

  public user_down: any = null;

  //property image
  public strImageBase64: any = '';
  public typeImage : any = '';
  public urlAvatar : any = '';

  constructor(
    private apiCompanies: ManagerCompaniesService,
    private translate: TranslateService,
    private activityLogs: ActivityLogsService
  ) {
    this.companyUpdate = new CompanyUpdate();
  }

  ngOnInit() {

    this.user_down = localStorage.getItem('token_shadow');

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.companyUpdate.id = data.id;
        this.companyUpdate.name = data.name;
        this.companyUpdate.phone = data.phone;
        this.companyUpdate.tax_code = data.tax_code;
        this.companyUpdate.address = data.address;
        this.companyUpdate.email = data.email;
        this.companyUpdate.logo = data.logo;
      }
    );
  }

  showEditCompanyModal() {
    this.editModal.show();
  }

  editCompany() {

    if(this.companyUpdate.email){
      if (!this.emailPattern.test(this.companyUpdate.email)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_EMAIL'), 'warning');
        return;
      }
    }

    if (!this.companyUpdate.phone) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOT_PHONE'), 'warning');
      return;
    }

    if(this.companyUpdate.phone){
      if (!this.numberPatten.test(this.companyUpdate.phone)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PHONE'), 'warning');
        return;
      }
    }

    if (!this.companyUpdate.tax_code) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOT_TAX_CODE'), 'warning');
      return;
    }

    if (!this.companyUpdate.address) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOT_ADDRESS'), 'warning');
      return;
    }

    this.isUpdated = false;
    this.apiCompanies.managerUpdateCompany({
      id: this.companyUpdate.id,
      address: this.companyUpdate.address,
      phone: this.companyUpdate.phone,
      tax_code: this.companyUpdate.tax_code,
      email: this.companyUpdate.email,
      logo: this.strImageBase64,
    }).subscribe(
      data => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'update';
        activity_log['subject_type'] = 'ticket';
        activity_log['subject_data'] = this.companyUpdate ? JSON.stringify(this.companyUpdate) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.editModal.hide();
        this.companyUpdate = new CompanyUpdate();
        this.isUpdated = false;
        this.strImageBase64 = '';
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
        this.isUpdated = false;
      }
    );
  }

  //handle logo
  onFileImageChange($event) : void {
    this.eventConvertBase64($event.target);
  }

  eventConvertBase64(inputValue: any): void {
    var file:File = inputValue.files[0];
    var myReader:FileReader = new FileReader();
    myReader.onloadend = (e) => {
      this.strImageBase64 = myReader.result;
      this.typeImage =  file.type;
    }
    myReader.readAsDataURL(file);
  }
}
