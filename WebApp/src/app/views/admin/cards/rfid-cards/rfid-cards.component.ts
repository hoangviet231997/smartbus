
import { AdminCardsService } from '../../../../api/services';
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
  selector: 'app-rfid-cards',
  templateUrl: './rfid-cards.component.html',
  styleUrls: ['./rfid-cards.component.css']
})
export class RfidCardsComponent implements OnInit {

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  public rfid_cards: any = [];
  public input_rfid: any = '';
  public timeoutSearchRfidCard;

  public qrcode : string = null;
  public qrcode_picture : string = null;
  public qrcode_code : string = null;

  constructor( private apiRfidCards: AdminCardsService, private spinner: NgxSpinnerService ) { }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.spinner.show(); 
    this.apiRfidCards.listRfidCardsResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {

        this.rfid_cards = resp.body;
        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
        this.spinner.hide(); 
      }
    );
  }
  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.refreshView();
  }

  getDataRfidCardByInputRfid(){

    clearTimeout(this.timeoutSearchRfidCard);
    
    this.timeoutSearchRfidCard = setTimeout(()=>{
      if(this.input_rfid!=='') {
        this.apiRfidCards.listRfidCardsByInputRfid(this.input_rfid).subscribe( data => {
            this.rfid_cards = data;
        });
      }else{
        this.refreshView();
      }   
    },500);
  }

  showPrintAllCard(data: object){
   
    this.qrcode = data['barcode'];
    setTimeout(()=>{
      let printContents, popupWin;
      printContents = document.getElementById('print-section').innerHTML;
      popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
      popupWin.document.open();
      popupWin.document.write(`
        <html>
          <head>
            <title>Print Card</title>
            <style>
              @page{ 
                size: 638px 1004px landscape;
                margin: 0;
              }
              .seri-card{
                text-align: center;
                font-family: Times, Times New Roman, Georgia, serif; 
                font-weight: bold;
                font-size: 13px;
              }
            </style>
          </head>
          <body onload="window.print();window.close()">
            ${printContents}
          </body>
        </html>`
      );
      popupWin.document.close();
    },100);
  }

  showPrintPictureQrcodeCard(data: object){
    
    this.qrcode = data['barcode'];
    setTimeout(()=>{
      let printContents, popupWin;
      printContents = document.getElementById('print-section-picture').innerHTML;
      popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
      popupWin.document.open();
      popupWin.document.write(`
        <html>
          <head>
            <title>Print Card</title>
            <style>
              @page{ 
                size: 638px 1004px landscape;
                margin: 0;
              }
              .seri-card{
                text-align: center;
                font-family: Times, Times New Roman, Georgia, serif; 
                font-weight: bold;
                font-size: 13px;
              }
            </style>
          </head>
          <body onload="window.print();window.close()">
            ${printContents}
          </body>
        </html>`
      );
      popupWin.document.close();
    },100);
  }

  showPrintBarcodeCard(data: object){

    this.qrcode = data['barcode'];
    setTimeout(()=>{
      let printContents, popupWin;
      printContents = document.getElementById('print-section-barcode').innerHTML;
      popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
      popupWin.document.open();
      popupWin.document.write(`
        <html>
          <head>
            <title>Print Card</title>
            <style>
              @page{ 
                size: 638px 1004px landscape;
                margin: 0;
              }
              .seri-card{
                text-align: center;
                font-family: Times, Times New Roman, Georgia, serif; 
                font-weight: bold;
                font-size: 13px;
              }
            </style>
          </head>
          <body onload="window.print();window.close()">
            ${printContents}
          </body>
        </html>`
      );
      popupWin.document.close();
    },100);
  }
}
