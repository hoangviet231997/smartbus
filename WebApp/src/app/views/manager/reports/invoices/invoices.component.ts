import { Component, OnInit, AfterViewInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import swal from 'sweetalert2';
import { NgxSpinnerService } from 'ngx-spinner';
import * as moment from 'moment';
import { saveAs } from 'file-saver/FileSaver';
import {  ManagerReportsService } from '../../../../api/services';

@Component({
  selector: 'app-invoices',
  templateUrl: './invoices.component.html',
  styleUrls: ['./invoices.component.css']
})
export class InvoicesComponent implements OnInit, AfterViewInit {

  public bsRangeValue: Date[];
  public maxDate: Date;
  public isExport = false;
  public dataExport:any = null;
  
  public permissions:any = [];

  constructor(
    private translate: TranslateService,
    private spinner : NgxSpinnerService,
    private apiReport: ManagerReportsService,
    
  ) { 
    this.maxDate = new Date();
  }

  ngOnInit() {

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {

  }

  //export file exel for invoices
  exportInvoices(){

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (this.bsRangeValue.length !== 2) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    this.isExport = true;

    // if(this.dataExport != null){
      
      // this.isExport = false;
  
      // get filename
      // const contentDispositionHeader: string = this.dataExport.headers.get('Content-Disposition');
      // const parts: string[] = contentDispositionHeader.split(';');
      // const filename = parts[1].split('=')[1];

      // // convert data
      // const byteCharacters = atob(this.dataExport.body);
      // const byteNumbers = new Array(byteCharacters.length);
      // for (let i = 0; i < byteCharacters.length; i++) {
      //   byteNumbers[i] = byteCharacters.charCodeAt(i);
      // }
      // const byteArray = new Uint8Array(byteNumbers);
      // const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel'});

      // saveAs(blob, filename);

    // }
    // else{

      this.apiReport.managerReportsExportInvoicesResponse({
        to_date: moment(this.bsRangeValue[1]).format('YYYY-MM-DD'),
        from_date: moment(this.bsRangeValue[0]).format('YYYY-MM-DD')
      }).subscribe(data => {

        // this.dataExport = data;
        
        this.isExport = false;

        // get filename
        const contentDispositionHeader: string = data.headers.get('Content-Disposition');
        const parts: string[] = contentDispositionHeader.split(';');
        const filename = parts[1].split('=')[1];

        // convert data
        const byteCharacters = atob(data.body);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
          byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel'});

        saveAs(blob, filename);
      });
    }
  // });
}
