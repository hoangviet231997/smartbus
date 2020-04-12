import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { AdminDevicesService } from '../../../../api/services';
import swal from 'sweetalert2';
import { DevModel, DevModelForm, Firmware, FirmwareForm } from '../../../../api/models';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-device-models',
  templateUrl: './device-models.component.html',
  styleUrls: ['./device-models.component.css']
})
export class DeviceModelsComponent implements OnInit, AfterViewInit {

  @ViewChild('addDeviceModel') public addDeviceModel: ModalDirective;
  @ViewChild('editDeviceModal') public editDeviceModal: ModalDirective;
  @ViewChild('addFirmwareModal') public addFirmwareModal: ModalDirective;
  @ViewChild('editFirmwareModal') public editFirmwareModal: ModalDirective;

  public deviceModels: DevModel[];
  public deviceModel: DevModel;
  public deviceModelForm: DevModelForm;
  public chckBoxFeatures = ['3G', 'GPRS', 'WIFI', 'LAN', 'RS232', 'GPS', 'PRINTER', 'MIFARE', 'ICCARD', 'BARCODE'];
  public firmwares: Firmware[];
  public firmware: Firmware;
  public firmwareForm: FirmwareForm;
  public isCreated = false;
  public isUpdated = false;

  constructor(
    private apiDevices: AdminDevicesService, private router: Router, private translate: TranslateService, private spinner: NgxSpinnerService
  ) {
    this.deviceModel = new DevModel();
    this.deviceModel.features = [];
    this.deviceModelForm =  new DevModelForm();
    this.deviceModelForm.features = [];
    this.firmware = new Firmware();
    this.firmwareForm = new FirmwareForm();
  }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.spinner.show(); 
    this.apiDevices.listDevModels().subscribe(
      deviceModels => {
        this.deviceModels = deviceModels;
        this.spinner.hide(); 
      }
    );
  }

  showAddDeviceModel() {
    this.addDeviceModel.show();
  }

  showEditDeviceModal(id: number ) {
    this.spinner.show();
    this.apiDevices.getDevModelById(id).subscribe(
      data => {
        this.deviceModelForm.id = data.id;
        this.deviceModelForm.model = data.model;
        this.deviceModelForm.name = data.name;
        this.deviceModelForm.features = data.features;
        this.spinner.hide();
        this.editDeviceModal.show();
        this.listFirmwares(data.id);
      },
      err => {
        this.spinner.hide();
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA')});
      }
    );
  }

  createDeviceModel() {
    if (!this.deviceModel.name) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }
    if (!this.deviceModel.model) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MDL'), 'warning');
      return;
    }

    this.isCreated = true;
    this.apiDevices.createDevModel({
      model: this.deviceModel.model,
      name: this.deviceModel.name,
      features: this.deviceModel.features,
    }).subscribe(
      res => {
        this.addDeviceModel.hide();
        this.deviceModel = new DevModel();
        this.deviceModel.features = [];
        this.isCreated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      },
      err => {
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD')});
        this.isCreated = false;
      },
    );
  }

  onFeatureChanged(event, feature, type: string) {

    if (event.currentTarget.checked) {

      if (type === 'add') {
        this.deviceModel.features.push(feature);
      } else {
        this.deviceModelForm.features.push(feature);
      }
    } else {

      if (type === 'add') {
        const index: number =  this.deviceModel.features.indexOf(feature);
        if (index !== -1) {
          this.deviceModel.features.splice(index, 1);
        }
      } else {
        const index: number =  this.deviceModelForm.features.indexOf(feature);
        if (index !== -1) {
          this.deviceModelForm.features.splice(index, 1);
        }
      }
    }
  }

  updateDeviceModel() {

    if (!this.deviceModelForm.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }

    if (!this.deviceModelForm.model) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_MDL'), 'warning');
      return;
    }

    this.isUpdated = true;
    this.apiDevices.updateDevModel({
      id: this.deviceModelForm.id,
      name: this.deviceModelForm.name,
      model: this.deviceModelForm.model,
      features: this.deviceModelForm.features,
    }).subscribe(
      res => {
        this.editDeviceModal.hide();
        this.deviceModelForm =  new DevModelForm();
        this.deviceModelForm.features = [];
        this.isUpdated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
        this.isUpdated = false;
      }
    );
  }

  deleteDeviceModel(id: number) {
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
        this.apiDevices.deleteDevModel(id).subscribe(
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

  showAddFirmwareModal() {
    this.firmware = new Firmware();
    this.addFirmwareModal.show();
  }

  showEditFirmwareModal(firmwareId: number) {
    this.spinner.show();
    this.apiDevices.getFirmwareByIdAndModelId({
      modelId: this.deviceModelForm.id,
      firmwareId: firmwareId
    }).subscribe(
      data => {
        this.firmwareForm.id = data.id,
        this.firmwareForm.server_ip = data.server_ip,
        this.firmwareForm.username = data.username,
        this.firmwareForm.password = data.password,
        this.firmwareForm.path = data.path,
        this.firmwareForm.version = data.version,
        this.firmwareForm.filename = data.filename,
        this.spinner.hide();
        this.editFirmwareModal.show();
      },
      err => {
        this.spinner.hide();
      }
    );
  }

  listFirmwares(id: number) {
    this.apiDevices.listFirmwares(this.deviceModelForm.id).subscribe(
      firmwares => {
        this.firmwares = firmwares;
      }
    );
  }

  createFirmware() {
    if (!this.firmware.server_ip) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_IP'), 'warning');
      return;
    }

    if (!this.firmware.username) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_USER'), 'warning');
      return;
    }

    if (!this.firmware.password) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PASS'), 'warning');
      return;
    }

    if (!this.firmware.path) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PATH'), 'warning');
      return;
    }

    if (!this.firmware.version) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_VER'), 'warning');
      return;
    }

    if (!this.firmware.filename) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_FILE'), 'warning');
      return;
    }

    this.isCreated = true;
    this.apiDevices.createFirmware({
      modelId: this.deviceModelForm.id,
      body: this.firmware
    }).subscribe(
      res => {
        this.addFirmwareModal.hide();
        this.firmware = new Firmware();
        this.isCreated = false;
        this.listFirmwares(this.deviceModelForm.id);
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      },
      err => {
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD')});
        this.isCreated = false;
      }
    );
  }

  updateFirmware() {
    if (!this.firmwareForm.server_ip) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_IP'), 'warning');
      return;
    }

    if (!this.firmwareForm.username) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_USER'), 'warning');
      return;
    }

    if (!this.firmwareForm.password) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PASS'), 'warning');
      return;
    }

    if (!this.firmwareForm.path) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_PATH'), 'warning');
      return;
    }

    if (!this.firmwareForm.version) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_VER'), 'warning');
      return;
    }

    if (!this.firmwareForm.filename) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_FILE'), 'warning');
      return;
    }

    this.isUpdated = true;
    this.apiDevices.updateFirmware({
      modelId: this.deviceModelForm.id,
      body: this.firmwareForm
    }).subscribe(
      res => {
        this.editFirmwareModal.hide();
        this.firmwareForm = new FirmwareForm();
        this.isUpdated = false;
        this.listFirmwares(this.deviceModelForm.id);
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
        this.isUpdated = false;
      }
    );
  }

  deleteFirmware(id: number) {
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
        this.apiDevices.deleteFirmware({
          modelId: this.deviceModelForm.id,
          firmwareId: id
        }).subscribe(
          res => {
            this.listFirmwares(this.deviceModelForm.id);
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
}
