
import { Component, OnInit, ViewChild, ViewEncapsulation, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';

import { AdminCardsService } from '../../../../api/services';
import { RfidCard, RfidCardCreate, RfidCardUpdate } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';

import { QtSocketService } from '../../../../shared/qt-socket.service';
import { Subscription } from 'rxjs';
import { SocketComponent } from '../../../../shared/socket-component';

@Component({
  selector: 'app-print-blank-cards',
  templateUrl: './print-blank-cards.component.html',
  styleUrls: ['./print-blank-cards.component.css']
})

export class PrintBlankCardsComponent implements OnInit {

  @ViewChild('editModal') public editModal: ModalDirective;

  public rfid: any = '';
  public isCreated = false;
  public qrcode: string = null;

  constructor(
    private apiRfidCards: AdminCardsService,
  ) {
  }

  ngOnInit() {
  }

  addAndPrintRfidCard() {

    if (!this.rfid || this.rfid === null || this.rfid == undefined) {
      swal('Warning', 'Please specify RFID!', 'warning');
      return;
    }

    if(this.rfid){
      if(this.rfid.length != 8){
        swal('Warning', 'The string length must be 8!', 'warning');
        return;
      }
    }

    this.isCreated = true;
    this.apiRfidCards.createAndPrintRfidCard({
      rfid: this.rfid
    }).subscribe(
      res => {
        // this.showPrintCard(res);
        this.rfid = '';
        this.isCreated = false;
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

  // showPrintCard(data: RfidCard){

  //   this.qrcode = data.barcode;

  //   setTimeout(()=>{
  //     let printContents, popupWin;
  //     printContents = document.getElementById('print-section').innerHTML;
  //     popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
  //     popupWin.document.open();
  //     popupWin.document.write(`
  //       <html>
  //         <head>
  //           <title>Print Card</title>
  //           <style>
  //             @page{ 
  //               size: 638px 1004px landscape;
  //               margin: 0;
  //             }
  //             .seri-card{
  //               text-align: center;
  //               font-family: Times, Times New Roman, Georgia, serif; 
  //               font-weight: bold;
  //               font-size: 15px;
  //             }
  //           </style>
  //         </head>
  //         <body onload="window.print();window.close()">
  //           ${printContents}
  //         </body>
  //       </html>`
  //     );
  //     popupWin.document.close();
  //   },100);
  // }
}

