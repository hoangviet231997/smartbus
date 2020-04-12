import { Component, OnInit, ViewChild, AfterViewInit,OnDestroy,Pipe, ViewEncapsulation } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import swal from 'sweetalert2';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { AppNotifyForm } from '../../../../api/models';
import { ManagerAppNotifyService } from '../../../../api/services';
import { HttpErrorResponse } from '@angular/common/http';
import { ApiConfiguration } from '../../../../api/api-configuration';
import { NgxSpinnerService } from 'ngx-spinner';
import * as io from 'socket.io-client';

@Component({
  selector: 'app-app-notifies',
  templateUrl: './app-notifies.component.html',
  styleUrls: ['./app-notifies.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class AppNotifiesComponent implements OnInit, AfterViewInit, OnDestroy {

  @ViewChild('modalAddAppNotify') public modalAddAppNotify: ModalDirective;
  @ViewChild('modalEditAppNotify') public modalEditAppNotify: ModalDirective;

  public createAppNotify: AppNotifyForm;
  public updateAppNotify: AppNotifyForm;

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  public style_search: any = '';
  public key_input: any= '';
  public timeoutSearchAppNotify;
  public strImageBase64: any;
  public typeImage: any;
  public permissions:any = [];
  public app_notifies: any;
  public socket;
  public defaulIcon: any = '';

  constructor(
    private apiAppNotify: ManagerAppNotifyService,
    private translate: TranslateService,
    private config: ApiConfiguration,
    private spinner: NgxSpinnerService,
  ) {
    this.createAppNotify = new AppNotifyForm;
    this.updateAppNotify = new AppNotifyForm;
    this.defaulIcon = "assets/img/icon_notify.png";
  }

  ngOnInit() {
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngOnDestroy() { }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.getDataAppNotify();
  }

  getDataAppNotify() {

    this.spinner.show();
    this.apiAppNotify.managerListAppNotifyResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).subscribe(
      resp => {
        this.key_input = '';
        this.app_notifies = resp.body;

        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
        this.spinner.hide();
      }
    );
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.getDataAppNotify();
  }

  getDataAppNotifyByInput() {
    clearTimeout(this.timeoutSearchAppNotify);
    this.timeoutSearchAppNotify = setTimeout(() => {
      if (this.key_input !== '') {
        this.apiAppNotify.managerSearchAppNotifyByInput({
          style_search: 'name',
          key_input: this.key_input
        }).subscribe(data => {
          this.app_notifies = data;
        });
      } else {
        this.getDataAppNotify();
      }
    }, 500);
  }

  onFileImageChange($event) {
    this.eventConvertBase64($event.target);
  }

  eventConvertBase64(inputValue: any): void {
    var file: File = inputValue.files[0];
    var myReader: FileReader = new FileReader();
    myReader.onloadend = (e) => {
      this.strImageBase64 = myReader.result;
      this.typeImage = file.type;
    }
    myReader.readAsDataURL(file);
  }

  showModalAddAppNotify() {
    this.createAppNotify = new AppNotifyForm();
    this.modalAddAppNotify.show();
    this.strImageBase64 = ''
  }

  addAppNotify() {

    if (this.typeImage) {
      if (this.typeImage !== 'image/jpeg' && this.typeImage !== 'image/png' && this.typeImage !== 'image/jpg') {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_IMAGE_FORMAT'), 'warning');
        return;
      }
    }

    if (!this.createAppNotify.name || this.createAppNotify.name === '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_NAME'), 'warning');
      return;
    }

    // if (!this.strImageBase64 || this.strImageBase64 === '') {
    //   swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_AVATAR'), 'warning');
    //   return;
    // }

    if (!this.createAppNotify.weigth) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_STT'), 'warning');
      return;
    }

    if (!this.createAppNotify.description || this.createAppNotify.description === '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_DESCRIPTION'), 'warning');
      return;
    }

    if (this.createAppNotify.description.length >= 256) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_DESCRIPTION_256'), 'warning');
      return;
    }

    this.apiAppNotify.managerCreateAppNotify({
      name: this.createAppNotify.name,
      url_img: this.strImageBase64,
      description: this.createAppNotify.description,
      content: this.createAppNotify.content,
      weigth: this.createAppNotify.weigth
    }).subscribe((data) => {
      //send data notify to server socket
      if (data) {
        this.socket = io(this.config.getStrUrlSocket());
        this.socket.emit('receiveDataNotifyWeb', { data: JSON.stringify(data), company_id: data['company_id'] });
      }
      this.getDataAppNotify();
      this.modalAddAppNotify.hide();
      swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
    },
      (err) => {
        if (err instanceof HttpErrorResponse) {
          if (err.status == 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          }
        }
      }
    );
  }

  showModalEditAppNotify(id: number) {
    this.modalEditAppNotify.show();
    this.strImageBase64 = '';
    this.apiAppNotify.managerGetAppNotifyById(id).subscribe((resp) => {
      this.updateAppNotify.weigth = resp.weigth;
      this.updateAppNotify.url_img = resp.url_img;
      this.updateAppNotify.name = resp.name;
      this.updateAppNotify.description = resp.description;
      this.updateAppNotify.content = resp.content;
      this.updateAppNotify.id = id;
    });
  }

  editAppNotify() {

    if (this.typeImage) {
      if (this.typeImage !== 'image/jpeg' && this.typeImage !== 'image/png' && this.typeImage !== 'image/jpg') {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_IMAGE_FORMAT'), 'warning');
        return;
      }
    }

    if (!this.updateAppNotify.name || this.updateAppNotify.name === '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_NAME'), 'warning');
      return;
    }

    // if (!this.strImageBase64 || this.strImageBase64 === '') {
    //   swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_AVATAR'), 'warning');
    //   return;
    // }

    if (!this.updateAppNotify.weigth) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_STT'), 'warning');
      return;
    }

    if (!this.updateAppNotify.description || this.updateAppNotify.description === '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_DESCRIPTION'), 'warning');
      return;
    }

    if (this.updateAppNotify.description.length >= 256) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NOTI_DESCRIPTION_256'), 'warning');
      return;
    }

    this.apiAppNotify.managerUpdateAppNotify({
      id: this.updateAppNotify.id,
      name: this.updateAppNotify.name,
      url_img: this.strImageBase64 ? this.strImageBase64 : null,
      description: this.updateAppNotify.description,
      content: this.updateAppNotify.content,
      weigth: this.updateAppNotify.weigth
    }).subscribe((resp) => {
      this.getDataAppNotify();
      this.modalEditAppNotify.hide();
      swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
    },
      (err) => {
        if (err instanceof HttpErrorResponse) {
          if (err.status == 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          }
        }
      }
    );
  }

  deleteAppNotify(id: number) {
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
        this.apiAppNotify.managerDeleteAppNotify(id).subscribe((resp) => {
          swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          this.getDataAppNotify();
        });
      }
    });
  }
}
