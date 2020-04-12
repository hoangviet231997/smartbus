import { Component, OnInit, ViewChild, ViewEncapsulation, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import * as moment from 'moment';

import { ManagerReportsService, ManagerCompaniesService } from '../../../../api/services';
import { ShiftDestroyForm,ShiftDestroyView } from '../../../../api/models';

import { map } from 'rxjs/operators/map';
import { HttpErrorResponse } from '@angular/common/http';
import { saveAs } from 'file-saver/FileSaver';

import { transliterate as tr, slugify } from 'transliteration';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';


@Component({
  selector: 'app-shift-destroys',
  templateUrl: './shift-destroys.component.html',
  styleUrls: ['./shift-destroys.component.css']
})
export class ShiftDestroysComponent implements OnInit {

  public bsRangeValue: Date[];
  public maxDate: Date;
  public selectedTypeAccept : any = null;
  public shift_destroys: any = [];
  public permissions:any[] = [];


  public daysForm: any;
  public monthForm: any;
  public yearsForm: any;

  public daysTo: any;
  public monthTo: any;
  public yearsTo: any;
  public company: any;

  public isLoading = false;
  public isViewHistoryShiftDestroy: any = 0;

  constructor(
    private translate: TranslateService,
    private apiReports: ManagerReportsService,
    private spinner : NgxSpinnerService,
    private apiCompanies: ManagerCompaniesService
  ) { 
    this.maxDate = new Date();
  }

  ngOnInit() {
    this.getComapny();
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }
  getComapny() {

    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.company = data;
      }
    );
  }

  getDataShiftsDestroy(){

    this.isLoading = true;

    if( this.isViewHistoryShiftDestroy == 0 ){

      if (this.bsRangeValue === undefined) {
        this.isLoading = false;
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
        return;
      }
  
      if (this.bsRangeValue.length !== 2) {
        this.isLoading = false;
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
        return;
      }
    }
    
    if(this.isViewHistoryShiftDestroy == 1){

      if (this.bsRangeValue === undefined) {
        this.isLoading = false;
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
        return;
      }
  
      if (this.bsRangeValue.length !== 2) {
        this.isLoading = false;
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
        return;
      }

      if (!this.selectedTypeAccept) {
        this.isLoading = false;
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_TICKET_DESTROY_HISTORY'), 'warning');
        return;
      }
    }

    if(this.bsRangeValue.length > 0 ){

      this.daysForm = moment(this.bsRangeValue[0]).format('DD').toString();
      this.monthForm = moment(this.bsRangeValue[0]).format('MM').toString();
      this.yearsForm = moment(this.bsRangeValue[0]).format('YYYY').toString();
  
      this.daysTo = moment(this.bsRangeValue[1]).format('DD').toString();
      this.monthTo = moment(this.bsRangeValue[1]).format('MM').toString();
      this.yearsTo = moment(this.bsRangeValue[1]).format('YYYY').toString();
    }

    this.spinner.show();
    this.apiReports.managerReportsViewShiftDestroys({
      to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD'),
      accept: this.selectedTypeAccept
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      data => {
        this.shift_destroys = data;
        this.spinner.hide();
    });
  }

  acceptShifttDestroy(id: number ,type: string){

    if(!id || !type){
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEMBER_ID'), 'warning');
      return;
    }

    swal({
      title: this.translate.instant('SWAL_ERROR_SURE'),
      // text: this.translate.instant('SWAL_ERROR_REMOVE'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: this.translate.instant('SWAL_OK'),
      cancelButtonText: this.translate.instant('SWAL_CANCEL')
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.apiReports.managerReportsAcceptShiftDestroy({
          id: id,
          type: type
        }).subscribe(
          res => {
            this.getDataShiftsDestroy();
            this.spinner.hide();
            swal(this.translate.instant('SWAL_ACCEPTED'), res.msg, 'success');
          },
          err => {
            this.spinner.hide();
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
          }
        );
      }
    });
  }

  showViewHistoryShiftDestroy(){
    this.isViewHistoryShiftDestroy = 1;
    this.bsRangeValue = null;
    this.selectedTypeAccept = null;
    this.shift_destroys = [];
    this.isLoading = false;
  }

  callBack(){
    this.isViewHistoryShiftDestroy = 0;
    this.bsRangeValue = null;
    this.selectedTypeAccept = null;
    this.shift_destroys = [];
    this.isLoading = false;
  }
  
  // showPrintPreview(){

  //   this.isLoading = true;

  //   if( this.isViewHistoryShiftDestroy == 0 ){
  //     if (this.bsRangeValue === undefined) {
  //       this.isLoading = false;
  //       swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
  //       return;
  //     }
  
  //     if (this.bsRangeValue.length !== 2) {
  //       this.isLoading = false;
  //       swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
  //       return;
  //     }
  //   }
    
  //   if(this.isViewHistoryShiftDestroy == 1){
  //     if (this.bsRangeValue === undefined) {
  //       this.isLoading = false;
  //       swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
  //       return;
  //     }
  
  //     if (this.bsRangeValue.length !== 2) {
  //       this.isLoading = false;
  //       swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
  //       return;
  //     }

  //     if (!this.selectedTypeAccept) {
  //       this.isLoading = false;
  //       swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_TICKET_DESTROY_HISTORY'), 'warning');
  //       return;
  //     }
  //   }

  //   let printContents, popupWin;
  //   printContents = document.getElementById('print-section').innerHTML;
  //   popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
  //   popupWin.document.open();

  //   popupWin.document.write(`
  //   <html>
  //     <head>
  //       <title></title>
  //       <style>
  //       @page { size: A4; size: landscape }
  //       .tx-bold{ font-weight: bold;}
  //       .tx-center{text-align: center}
  //       .tx-left{text-align: left}
  //       .tx-right{text-align: right}
  //       .tx-10{font-size: 13px; font-family: 'Times New Roman';}
  //       .tx-11{font-size: 11px; font-family: 'Times New Roman';}
  //       .tx-12{font-size: 16px; font-family: 'Times New Roman';}
  //       .w-10{width: 10cm}
  //       .w-3{width: 3cm;float:left}
  //       .fl{float:left}
  //       .fr{float:right}
  //       .w-4{width: 4cm}
  //       .w-2{width: 1.5cm}
  //       .pt-0{margin-top: 0}
  //       </style>
  //     </head>
  //     <body onload="window.print();window.close()">`+ printContents +`</body>
  //   </html>`
  //   );
    
  //   popupWin.document.close();

  // }

}
