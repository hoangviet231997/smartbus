import { Component, OnInit, ViewChild, AfterViewInit, ViewEncapsulation } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { AdminDevicesService, AdminCompaniesService } from '../../../../api/services';
import { Router } from '@angular/router';
import { Device, DevModel, DeviceForm } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';
import { map } from 'rxjs/operators/map';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-devices',
  templateUrl: './devices.component.html',
  styleUrls: ['./devices.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class DevicesComponent implements OnInit, AfterViewInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;
  @ViewChild('assignModal') public assignModal: ModalDirective;

  public devices: Device[];
  public device: Device;
  public deviceForm: DeviceForm;
  public isCreated = false;
  public isUpdated = false;
  public isAssigned = false;
  public isDeleted = false;

  public valueSelected: any = {};
  public valueSelectedCompany: any = {};
  public devModelSelected: Array<any> = [];
  public companySelected: Array<any> = [];
  public _disabledV =  '0';
  public disabled = false;
  public modelItems: Array<any> = [];
  public companyItems: Array<any> = [];
  public deviceId = 0;

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  public timeoutSearchDevice;

  public txtIdentity: any = '';

  constructor(
    private apiDevices: AdminDevicesService, private apiCompanies: AdminCompaniesService, private router: Router,
    private translate: TranslateService, private spinner: NgxSpinnerService
  ) {
    this.device = new Device();
    this.valueSelected = {};
    this.valueSelectedCompany = {};
    this.deviceForm = new DeviceForm();
  }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.spinner.show(); 
    this.apiDevices.listDevicesResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {
        this.devices = resp.body;
        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
        this.spinner.hide(); 
      }
    );

    this.apiDevices.listDevModels().subscribe(
      deviceModels => {
        this.modelItems = [];
        for (let i = 0; i < deviceModels.length; i++) {
          this.modelItems.push({
            id: deviceModels[i].id,
            text: deviceModels[i].model
          });
        }
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

  showAddModal() {
    this.devModelSelected = [];
    this.valueSelected = [];
    this.device = new Device();
    this.addModal.show();
  }

  showEditModal(id: number, devModelId: any, devModelname: any) {
    this.spinner.show();
    this.devModelSelected = [];
    this.valueSelected = [];
    this.apiDevices.getDeviceById(id).subscribe(
      data => {
        this.deviceForm.identity = data.identity;
        this.deviceForm.id = data.id;
        this.devModelSelected.push({
          id: devModelId,
          text: devModelname
        });
        this.valueSelected = {id: devModelId, text: devModelname };
        this.spinner.hide();
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
      }
    );
  }

  showAssignModal(id: number, companyId: any, companyName: any) {
    this.companySelected = [];
    this.valueSelectedCompany = [];
    this.deviceId = id;
    if (companyId !== 0 &&  companyName !== 0) {
      this.companySelected.push({
        id: companyId,
        text: companyName
      });
      this.valueSelectedCompany = {id: companyId, text: companyName };
    }
    this.assignModal.show();
  }

  addDevice() {

    if (!this.device.identity) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_LP'), 'warning');
      return;
    }

    if (this.valueSelected.id === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_MDL'), 'warning');
      return;
    }

    this.isCreated = true;
    this.apiDevices.createDevice({
      device_model_id: this.valueSelected.id,
      identity: this.device.identity
    }).subscribe(
      res => {
        this.addModal.hide();
        this.device = new Device();
        this.isCreated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      },
      err => {
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
        this.isCreated = false;
      }
    );
  }

  editDevice() {

    if (!this.deviceForm.identity) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_LP'), 'warning');
      return;
    }

    if (this.valueSelected.id === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_MDL'), 'warning');
      return;
    }

    this.isUpdated = true;
    this.apiDevices.updateDevice({
      id: this.deviceForm.id,
      identity: this.deviceForm.identity,
      device_model_id: this.valueSelected.id
    }).subscribe(
      res => {
        this.editModal.hide();
        this.deviceForm = new DeviceForm();
        this.isUpdated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
        this.isUpdated = false;
      }
    );
  }

  delDevice(id: number) {
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
        this.apiDevices.deleteDevice(id).subscribe(
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

  saveAssignDevice() {

    if (this.valueSelectedCompany.id === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_COMPANY'), 'warning');
      return;
    }

    this.isAssigned = true;
    this.apiDevices.assignCompanyToDevice({
      companyId: this.valueSelectedCompany.id,
      deviceId: this.deviceId
    }).subscribe(
      res => {
        this.assignModal.hide();
        this.isAssigned = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
        this.isAssigned = false;
      }
    );
  }

  deleteAssignDevice() {
    if (this.valueSelectedCompany.id === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_COMPANY'), 'warning');
      return;
    }

    this.isDeleted = true;
    this.apiDevices.deleteAssignCompanyToDevice({
      companyId: this.valueSelectedCompany.id,
      deviceId: this.deviceId
    }).subscribe(
      res => {
        this.assignModal.hide();
        this.isDeleted = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
      }
    );
  }

  public get disabledV(): string {
    return this._disabledV;
  }

  public set disabledV(value: string) {
    this._disabledV = value;
    this.disabled = this._disabledV === '1';
  }

  public selected(value: any) {
    // console.log('Selected value is: ', value);
  }

  public removed(value: any) {
    // console.log('Removed value is: ', value);
  }

  public typed(value: any) {
    // console.log('New search input: ', value);
  }

  public refreshValue(value: any) {
    this.valueSelected = value;
  }

  public refreshValueCompany(value: any) {
    this.valueSelectedCompany = value;
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.refreshView();
  }

  getDeviceByIdentitySearch(){
    clearTimeout(this.timeoutSearchDevice);
    this.timeoutSearchDevice = setTimeout(()=>{
      if(this.txtIdentity !== ''){
        this.apiDevices.getDeviceByIdentitySearch(this.txtIdentity).subscribe(res => {
          this.devices = res;
        });
      }else{
        this.refreshView();
      }
    },500);
  }
}
