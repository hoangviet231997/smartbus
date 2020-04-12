import { Component, OnInit, ViewEncapsulation, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { AdminDevicesService, AdminCompaniesService } from '../../../../api/services';
import swal from 'sweetalert2';
import { DevModel, DevModelForm, Firmware, FirmwareForm } from '../../../../api/models';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { version } from 'moment';
import { HttpErrorResponse, HttpClient } from '@angular/common/http';
import { Location } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { saveAs } from 'file-saver/FileSaver';

@Component({
  selector: 'app-device-firmware-version',
  templateUrl: './device-firmware-version.component.html',
  styleUrls: ['./device-firmware-version.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class DeviceFirmwareVersionComponent implements OnInit {

  @ViewChild('addModal') public addModal: ModalDirective;

  public modelItems: any = [];
  public firmwareCreate: FirmwareForm;
  public fileToUpload: any = null;
  public isCreated = false;
  public firmwareVersions: any = [];
  public extentFile: any;
  public companies = [];
  public timeoutSearchFirmwareVersion;
  public style_search: any = '';
  public key_input: any = '';

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  constructor(
    private apiDevices: AdminDevicesService,
    private apiCompanies: AdminCompaniesService,
    private router: Router,
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
    private route: ActivatedRoute,
    private location: Location
  ) {
    this.firmwareCreate = new FirmwareForm();
  }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {

    this.getListFirmwareVersions();
    this.apiDevices.listDevModels().subscribe(
      data => {
        this.modelItems = data;
      }
    );

    this.apiCompanies.listCompanies({
      page: 0,
      limit: 9999
    }).subscribe(
      companies => {
        this.companies = companies;
      }
    );
  }

  getListFirmwareVersions() {
    this.spinner.show(); 
    this.apiDevices.listFirmwareVersionsResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).subscribe(
      data => {
        this.firmwareVersions = data.body;
        this.spinner.hide(); 
        this.key_input = '';

        this.paginationTotal = data.headers.get('pagination-total');
        this.paginationCurrent = data.headers.get('pagination-current');
        this.paginationLast = data.headers.get('pagination-last');
      }
    );
  }

  showAddModal(){

    this.firmwareCreate.device_model_id = null;

    this.addModal.show();
  }

  createFirmwareDeviceVersion(){

    if (!this.firmwareCreate.version) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_FIRMWARE_VERSION'), 'warning');
      return;
    }

    if (!this.firmwareCreate.device_model_id) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_SELECT_MDL'), 'warning');
      return;
    }

    if(this.firmwareCreate.device_model_id != 3 && this.firmwareCreate.device_model_id !== null){

      if (!this.firmwareCreate.company_id) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_FIRMWARE_COMPANIES'), 'warning');
        return;
      }

      if (!this.firmwareCreate.update_type) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_FIRMWARE_UPDATE_TYPE'), 'warning');
        return;
      }
    }

    this.isCreated = true;
    this.apiDevices.createFirmWareDeviceVersion({
        server_ip: 'smartbus',
        username: 'smartbus',
        password: 'smartbus',
        path: 'public/file/',
        version: this.firmwareCreate.version,
        filename: 'smartbus',
        device_model_id: this.firmwareCreate.device_model_id,
        note: this.firmwareCreate.note,
        company_id: this.firmwareCreate.company_id,
        update_type: this.firmwareCreate.update_type
      }).subscribe(
        res => {
          this.addModal.hide();
          this.firmwareCreate = new FirmwareForm();
          this.isCreated = false;
          swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
          this.refreshView();
        },
        err => {if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
          }
        }
          this.isCreated = false;
        }
      );
  }

  deleteFirmwareDeviceVersion(id){
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
        this.apiDevices.deleteFirmWareDeviceVersion(id).subscribe(
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

  getDataFirmwareByInput(){
    clearTimeout(this.timeoutSearchFirmwareVersion);
    if (this.style_search == '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_FIRMWARE_SEARCH_ACTIVED'), 'warning');
      return;
    }

    this.timeoutSearchFirmwareVersion = setTimeout(() => {
      if (this.key_input !== '') {
        this.spinner.show();
        this.apiDevices.searchFirmwareByInputAndByTypeSearch({
          style_search: this.style_search,
          key_input: this.key_input
        }).subscribe(data => {
          this.firmwareVersions = data;
          this.spinner.hide();
        });
      } else {
        this.getListFirmwareVersions();
      }
    }, 500);
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.getListFirmwareVersions();
  }

}
