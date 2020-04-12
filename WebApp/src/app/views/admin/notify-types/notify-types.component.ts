import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { map } from 'rxjs/operators/map';
import { Router } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import { MouseEvent } from '@agm/core';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { AdminNotifiesService } from '../../../api/services';
import { NotifyType, NotifyTypeFrom } from '../../../api/models';

@Component({
  selector: 'app-notify-types',
  templateUrl: './notify-types.component.html',
  styleUrls: ['./notify-types.component.css']
})
export class NotifyTypesComponent implements OnInit, AfterViewInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public notify_types:any = [];
  public notifyTypeCreate: NotifyTypeFrom;
  public notifyTypeUpdate: NotifyTypeFrom;
  public isCreated = false;
  public isUpdated = false;

  //property image
  public strImageBase64: any = '';
  public typeImage : any = '';
  // public urlAvatar : any = '';

  constructor(
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
    private apiNotifyTypes: AdminNotifiesService
  ){
    this.notifyTypeCreate = new NotifyTypeFrom();
    this.notifyTypeUpdate = new NotifyTypeFrom();
  }

  ngOnInit() { }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView(){
    this.spinner.show();
    this.apiNotifyTypes.listNotifyTypes().subscribe((data) => {
      this.notify_types = data;
      this.spinner.hide();
    });
  }

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

  showAddNotifyTypeModal() {
    this.notifyTypeCreate = new NotifyTypeFrom();
    this.addModal.show();
    this.strImageBase64 = '';
    this.typeImage = '';
  }

  showEditNotifyTypeModal(id: number) {
    this.apiNotifyTypes.getNotifyTypeById(id).subscribe(
      data => {
        this.notifyTypeUpdate.id = data.id;
        this.notifyTypeUpdate.name = data.name;
        this.notifyTypeUpdate.key = data.key;
        this.notifyTypeUpdate.route_link = data.route_link;
        this.notifyTypeUpdate.url_img = data.url_img;
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_MODEL')});
      }
    );
  }

  addNotifyType() {

    if (!this.notifyTypeCreate.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NOTIFY_TYPE_NAME'), 'warning');
      return;
    }

    if (!this.notifyTypeCreate.key) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NOTIFY_TYPE_KEY'), 'warning');
      return;
    }

    if (!this.notifyTypeCreate.route_link) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NOTIFY_TYPE_ROUTE_LINK'), 'warning');
      return;
    }

    if (this.strImageBase64) {
      if (this.typeImage !== 'image/jpeg' && this.typeImage !== 'image/png' && this.typeImage !== 'image/jpg') {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TICKET_DESTROY_IMAGE_FORMAT'), 'warning');
        return;
      }
    }

    this.isCreated = true;
    this.apiNotifyTypes.createNotifyType({
      name: this.notifyTypeCreate.name,
      key: this.notifyTypeCreate.key,
      route_link: this.notifyTypeCreate.route_link,
      url_img: this.strImageBase64 ? this.strImageBase64 : ''
    }).subscribe(
      res => {

        this.addModal.hide();
        this.notifyTypeCreate = new NotifyTypeFrom();
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

  editNotifyType() {

    if (!this.notifyTypeUpdate.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NOTIFY_TYPE_NAME'), 'warning');
      return;
    }

    if (!this.notifyTypeUpdate.key) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NOTIFY_TYPE_KEY'), 'warning');
      return;
    }

    if (!this.notifyTypeUpdate.route_link) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NOTIFY_TYPE_ROUTE_LINK'), 'warning');
      return;
    }

    if (this.strImageBase64) {
      if (this.typeImage !== 'image/jpeg' && this.typeImage !== 'image/png' && this.typeImage !== 'image/jpg') {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TICKET_DESTROY_IMAGE_FORMAT'), 'warning');
        return;
      }
    }

    this.isUpdated = true;
    this.apiNotifyTypes.updateNotifyType({
      id: this.notifyTypeUpdate.id,
      name: this.notifyTypeUpdate.name,
      key: this.notifyTypeUpdate.key,
      route_link: this.notifyTypeUpdate.route_link,
      url_img: this.strImageBase64 ? this.strImageBase64 : ''
    }).subscribe(
      data => {
        this.editModal.hide();
        this.notifyTypeUpdate =  new NotifyTypeFrom();
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

  deleteNotifyType(id: number) {
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
        this.apiNotifyTypes.deleteNotifyType(id).subscribe(
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

