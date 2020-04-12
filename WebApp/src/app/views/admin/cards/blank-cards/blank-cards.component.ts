import { Component, OnInit, ViewChild, ViewEncapsulation, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';

import { ManagerRfidcardService } from '../../../../api/services';
import { RfidCard, RfidCardCreate, RfidCardUpdate } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';

import { QtSocketService } from '../../../../shared/qt-socket.service';
import { Subscription } from 'rxjs';
import { SocketComponent } from '../../../../shared/socket-component';

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
  public isSearching = false;
  private socketSubscription: Subscription;

  constructor(private apiRfidCards: ManagerRfidcardService, private qtSocket: QtSocketService) {
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
    // this.refreshView();
  }

  refreshView() {
    this.rfidCard = new RfidCard();
  }

  addRfidCard() {
    if (!this.rfidCardCreate.rfid) {
      swal('Warning', 'Please specify RFID!', 'warning');
      return;
    }

    if (!this.rfidCardCreate.barcode) {
      swal('Warning', 'Please specify Barcode!', 'warning');
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
        swal('Save successfully', '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: 'ERROR', text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: 'ERROR', text: 'Missing required field.'});
          }
        }
        this.isCreated = false;
      }
    );
  }

  searchRfidCard() {
    if (!this.rfidCard.rfid && !this.rfidCard.barcode) {
      swal('Warning', 'Please specify RFID or Barcode!', 'warning');
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
          swal('Warning', 'Can not find the data!', 'warning');
          return;
        }
      }
    );
  }

  editRfidCard() {
    if (!this.rfidCardUpdate.rfid) {
      swal('Warning', 'Please specify RFID!', 'warning');
      return;
    }

    if (!this.rfidCardUpdate.barcode) {
      swal('Warning', 'Please specify Barcode!', 'warning');
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
        swal('Save successfully', '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: 'ERROR', text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: 'ERROR', text: 'Update RFID Card error'});
          }
        }
        this.isUpdated = false;
      }
    );
  }
}
