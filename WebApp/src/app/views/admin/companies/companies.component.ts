import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { map } from 'rxjs/operators/map';
import { Router } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import { MouseEvent } from '@agm/core';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { AdminCompaniesService, AuthService } from '../../../api/services';
import { Company, CompanyCreate, CompanyUpdate, UploadFile } from '../../../api/models';
@Component({
  selector: 'app-companies',
  templateUrl: './companies.component.html',
  styleUrls: ['./companies.component.css']
})
export class CompaniesComponent implements OnInit, AfterViewInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;
  @ViewChild('uploadBackgroundCardModal') public uploadBackgroundCardModal: ModalDirective;

  public companies: Company[];
  public company: Company;
  public companyCreate: CompanyCreate;
  public companyUpdate: CompanyUpdate;
  public isCreated = false;
  public isUpdated = false;
  public emailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
  public numberPatten = /^-?[\d.]+(?:e-?\d+)?$/;

  // google maps zoom level
  zoom = 6;

  // initial center position for the map
  lat = 12.6496222;
  lng = 104.3004339;
  clickedLat = 0;
  clickedLng = 0;
  public uploadFiles: any[] = [];
  public uploadFile: UploadFile;
  public company_id = 0;
  befores = [];
  afters = [];

  //pagination
  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;
  public timeoutSearchCompany;

  //search
  public style_search: any = '';
  public key_input: any= '';

  constructor(
    private apiCompanies: AdminCompaniesService, private apiAuths: AuthService, private router: Router,
    private translate: TranslateService, private spinner: NgxSpinnerService
  ){
      this.company = new Company();
      this.companyCreate = new CompanyCreate();
      this.companyUpdate = new CompanyUpdate();
      this.uploadFile = new UploadFile();
  }

  ngOnInit() { }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.getListComapnies();
  }

  ngAfterViewInit() { this.refreshView(); }

  refreshView() { this.getListComapnies(); }

  getListComapnies(){

    this.spinner.show();
    this.apiCompanies.listCompaniesResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe((companies) => {

      this.companies = companies.body;
      this.spinner.hide();
      this.key_input = '';

      this.paginationTotal = companies.headers.get('pagination-total');
      this.paginationCurrent = companies.headers.get('pagination-current');
      this.paginationLast = companies.headers.get('pagination-last');
    });
  }

  getDataCompanyByInput(){

    clearTimeout(this.timeoutSearchCompany);
    if (this.style_search == '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_COMPANY_SEARCH_ACTIVED'), 'warning');
      return;
    }

    this.timeoutSearchCompany = setTimeout(() => {
      if (this.key_input !== '') {
        this.spinner.show();
        this.apiCompanies.managerListCompanyByInputAndByTypeSearch({
          style_search: this.style_search,
          key_input: this.key_input
        }).pipe(
          map(_r => {
            return _r;
          })
        ).subscribe(data => {
          this.companies = data;
          this.spinner.hide();
        });
      } else {
        this.getListComapnies();
      }
    }, 500);
  }

  showAddCompanyModal() {

    this.addModal.show();
    this.clickedLat = 0;
    this.clickedLng = 0;
    this.zoom = 6;
  }

  showEditCompanyModal(id: number) {

    this.spinner.show();

    this.apiCompanies.getCompanyById(id).subscribe(
      data => {
        this.companyUpdate.id = data.id;
        this.companyUpdate.name = data.name;
        this.companyUpdate.subname = data.subname;
        this.companyUpdate.fullname = data.fullname;
        this.companyUpdate.address = data.address;
        this.companyUpdate.tax_code = data.tax_code;
        this.companyUpdate.phone = data.phone;
        this.companyUpdate.email = data.email;
        this.companyUpdate.print_at = data.print_at;

        if (data.lat === 0 && data.lng === 0) {

          this.companyUpdate.lat = this.lat;
          this.companyUpdate.lng = this.lng;
          this.zoom = 6;
        } else {

          this.companyUpdate.lat = data.lat;
          this.companyUpdate.lng = data.lng;
          this.clickedLat = data.lat;
          this.clickedLng = data.lng;
          this.zoom = 15;
        }
        this.spinner.hide();
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_MODEL')});
      }
    );
  }

  addCompany() {

    if (!this.companyCreate.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }

    if(this.companyCreate.email){
      if (!this.emailPattern.test(this.companyCreate.email)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_EMAIL'), 'warning');
        return;
      }
    }

    if(this.companyCreate.phone){

      if (!this.numberPatten.test(this.companyCreate.phone)) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('TABLE_PHONE'), 'warning');
        return;
      }
    }

    if (!this.companyCreate.username) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_USER'), 'warning');
      return;
    }

    if (!this.companyCreate.password) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PASS'), 'warning');
      return;
    }

    this.isCreated = true;

    this.apiCompanies.createCompany({
      name: this.companyCreate.name,
      subname: this.companyCreate.subname,
      fullname: this.companyCreate.fullname,
      address: this.companyCreate.address,
      print_at: this.companyCreate.print_at,
      tax_code: this.companyCreate.tax_code,
      phone: this.companyCreate.phone,
      email: this.companyCreate.email,
      username: this.companyCreate.username,
      password: this.companyCreate.password,
      lat: this.clickedLat,
      lng: this.clickedLng
    }).subscribe(
      res => {

        this.addModal.hide();
        this.companyCreate = new CompanyCreate();
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

  editCompany() {

    if (!this.companyUpdate.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }

    if (!this.emailPattern.test(this.companyUpdate.email)) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_EMAIL'), 'warning');
      return;
    }

    if (!this.numberPatten.test(this.companyUpdate.phone) && this.companyUpdate.phone) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PHONE'), 'warning');
      return;
    }

    this.isUpdated = true;
    this.apiCompanies.updateCompany({
      id: this.companyUpdate.id,
      name: this.companyUpdate.name,
      subname: this.companyUpdate.subname,
      fullname: this.companyUpdate.fullname,
      tax_code: this.companyUpdate.tax_code,
      address: this.companyUpdate.address,
      print_at: this.companyUpdate.print_at,
      phone: this.companyUpdate.phone,
      email: this.companyUpdate.email,
      lat: this.clickedLat,
      lng: this.clickedLng
    }).subscribe(
      data => {
        this.editModal.hide();
        this.companyUpdate =  new CompanyUpdate();
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

  deleteCompany(id: number) {
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
        this.apiCompanies.deleteCompany(id).subscribe(
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

  loginAsCompany(id: number, lat, lng, layout_cards) {
    this.apiAuths.loginAsCompany(id).subscribe(
      res => {

        // save old data
        const token = localStorage.getItem('token');
        const user = localStorage.getItem('user');
        // const role = localStorage.getItem('role');
        // const permissions = localStorage.getItem('permissions');
        localStorage.setItem('token_shadow', token);
        localStorage.setItem('user_shadow', user);
        // localStorage.setItem('role_shadow', role);
        // localStorage.setItem('permissions_shadow', permissions);
        // localStorage.setItem('company_id', id.toString());
        // localStorage.setItem('company_layout_cards', layout_cards);
        // localStorage.setItem('company_lat', lat);
        // localStorage.setItem('company_lng', lng);
        // set data new
        localStorage.setItem('token', res.token);
        localStorage.setItem('user', JSON.stringify(res.user));

        // const roleName = res.user.role.name;
        // localStorage.setItem('role', roleName);
        this.router.navigate(['/manager']);
      },
      err => {
        if(err.error.length > 0 && err.error[0] == 'permission_denied'){
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_PERMISSION_DENIED')});
        }else{
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_LOGIN')});
        }
      }
    );
  }

  mapClicked($event: MouseEvent) {
    this.clickedLat = $event.coords.lat;
    this.clickedLng = $event.coords.lng;
  }

  markerDragEnd($event: MouseEvent) {
    this.clickedLat = $event.coords.lat;
    this.clickedLng = $event.coords.lng;
  }

  showUploadFileModal(company) {

    this.uploadBackgroundCardModal.show();

    this.company_id = company.id;
    this.befores = [];
    this.afters = [];
    this.uploadFiles = [];
  }

  //before upload
  onSelectFileBefore(event) {
    if (event.target.files && event.target.files[0]) {
      var filesAmount = event.target.files.length;
      for (let i = 0; i < filesAmount; i++) {
        var reader = new FileReader();

        reader.onload = (event1: any) => {
          this.befores.push(event1.target.result);
          var  obj = {
            opt_print : 'before',
            img : event1.target.result,
          }

          this.uploadFiles.push(obj);
          this.uploadFile = new UploadFile();
        }

        reader.readAsDataURL(event.target.files[i]);
      }
    }
  }

  //after upload
  onSelectFileAfter(event) {
    if (event.target.files && event.target.files[0]) {
      var filesAmount = event.target.files.length;
      for (let i = 0; i < filesAmount; i++) {
        var reader = new FileReader();

        reader.onload = (event1: any) => {

          this.afters.push(event1.target.result);

          // this.uploadFile.opt_print = "after";
          // let file = event.target.files[i];
          // let fileNames = file.name.split(".");
          // this.uploadFile.extention = fileNames[fileNames.length - 1];
          // this.uploadFile.data = event1.target.result;
          // this.uploadFiles.push(this.uploadFile);
          // this.uploadFile = new UploadFile();

          var  obj = {
            opt_print : 'after',
            img : event1.target.result,
          }

          this.uploadFiles.push(obj);
          this.uploadFile = new UploadFile();
        }
        reader.readAsDataURL(event.target.files[i]);
      }
    }
  }

  uploadBackgroundCard() {

    if (this.uploadFiles.length == 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('error_option_background'), 'warning');
      return;
    }
    this.spinner.show();
    this.apiCompanies.uploadFile({
      company_id: this.company_id,
      data: JSON.stringify(this.uploadFiles)
    }).subscribe(resp => {

      this.spinner.hide();
      this.uploadBackgroundCardModal.hide();
      this.refreshView();
    });
  }
}
