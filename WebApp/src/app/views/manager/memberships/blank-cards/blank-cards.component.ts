import { Component, OnInit, ViewChild, ViewEncapsulation, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { RfidCard, RfidCardCreate, RfidCardUpdate } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';
import { QtSocketService } from '../../../../shared/qt-socket.service';
import { Subscription } from 'rxjs';
import { SocketComponent } from '../../../../shared/socket-component';
import { TranslateService } from '@ngx-translate/core';
import { ManagerRfidcardService } from '../../../../api/services';

@Component({
  selector: 'app-blank-cards',
  templateUrl: './blank-cards.component.html',
  styleUrls: ['./blank-cards.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class BlankCardsComponent implements OnInit, AfterViewInit, SocketComponent {

  @ViewChild('editModal') public editModal: ModalDirective;

  public rfidCard: RfidCard;
  public rfidCardCreate: RfidCardCreate;
  public rfidCardUpdate: RfidCardUpdate;
  public isCreated = false;
  public isUpdated = false;
  private socketSubscription: Subscription;

  constructor(
    private apiRfidCards: ManagerRfidcardService,
    private qtSocket: QtSocketService,
    private translate: TranslateService
  ) {
    this.rfidCard = new RfidCard();
    this.rfidCardCreate = new RfidCardCreate();
    this.rfidCardUpdate = new RfidCardUpdate();
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
    //     this.rfidCardCreate.rfid = data.toString().split(':').pop();
    //     this.rfidCardUpdate.rfid = data.toString().split(':').pop();
    //   }
    // );
  }

  ngAfterViewInit() {
    // this.socketUp();
    this.refreshView();
  }

  refreshView() {
    this.rfidCard = new RfidCard();
  }

  addRfidCard() {
    if (!this.rfidCardCreate.rfid) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_RFID'), 'warning');
      return;
    }

    if (!this.rfidCardCreate.barcode) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_BARCODE'), 'warning');
      return;
    }

    this.isCreated = true;
    this.apiRfidCards.managerCreateRfidcard({
      rfid: this.rfidCardCreate.rfid,
      barcode: this.rfidCardCreate.barcode,
    }).subscribe(
      res => {
        this.rfidCardCreate = new RfidCardCreate();
        this.isCreated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA')});
          }
        }
        this.isCreated = false;
      }
    );
  }

  searchRfidCard() {
    if (!this.rfidCard.rfid && !this.rfidCard.barcode) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('RECEIPT_ERROR_RFID_BARCODE'), 'warning');
      return;
    }

    if (!this.rfidCard.rfid) {
      this.rfidCard.rfid = '';
    } else if (!this.rfidCard.barcode) {
      this.rfidCard.barcode = '';
    }

    this.apiRfidCards.managerSearchRfidcard({
      rfid: this.rfidCard.rfid,
      barcode: this.rfidCard.barcode,
    }).subscribe(
      data => {
        if (data) {
          this.rfidCardUpdate.id = data.id;
          this.rfidCardUpdate.rfid = data.rfid;
          this.rfidCardUpdate.barcode = data.barcode;
          this.editModal.show();
        } else {
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NO_DATA'), 'warning');
          return;
        }
      }
    );
  }

  editRfidCard() {
    if (!this.rfidCardUpdate.rfid) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_RFID'), 'warning');
      return;
    }

    if (!this.rfidCardUpdate.barcode) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_BARCODE'), 'warning');
      return;
    }

    this.isUpdated = true;
    this.apiRfidCards.managerUpdateRfidcard({
      id: this.rfidCardUpdate.id,
      rfid: this.rfidCardUpdate.rfid,
      barcode: this.rfidCardUpdate.barcode,
    }).subscribe(
      res => {
        this.editModal.hide();
        this.rfidCardUpdate = new RfidCardUpdate();
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
}
