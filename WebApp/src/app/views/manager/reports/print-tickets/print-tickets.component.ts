import { AfterViewInit, Component, OnInit, ViewEncapsulation } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import * as moment from 'moment';
import { NgxSpinnerService } from 'ngx-spinner';
import swal from 'sweetalert2';
import { ManagerCompaniesService, ManagerReportsService, ManagerTicketTypesService } from '../../../../api/services';

@Component({
  selector: 'app-print-tickets',
  templateUrl: './print-tickets.component.html',
  styleUrls: ['./print-tickets.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class PrintTicketsComponent implements OnInit, AfterViewInit {

  public bsRangeValue: any;;
  public maxDate: Date;
  public ticket_arr:any = [];

  public ticketPriceItems: any = [];
  public valueSelectedPrice: any = [];
  public company: any = {
    fullname: '',
    address: '',
    tax_code: '',
    phone:'',
    print_at: '',
    company_id: 0
  }

  public permissions:any = [];

  constructor(
    private translate: TranslateService,
    private apiReports: ManagerReportsService,
    private spinner : NgxSpinnerService,
    private apiTicketTypes: ManagerTicketTypesService,
    private apiCompanies: ManagerCompaniesService,
  ) {
    this.maxDate = new Date();
    this.valueSelectedPrice = [];
  }



  ngOnInit() {
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }


  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {

    this.spinner.show();
    this.apiTicketTypes.managerListTicketTypesByTypeParam(-1).subscribe(
      resp => {
        this.ticketPriceItems = [];
        
        for (let i = 0; i < resp.length; i++) {
          this.ticketPriceItems.push({
            id: resp[i].ticket_prices[resp[i].ticket_prices.length - 1].id,
            text: resp[i].ticket_prices[resp[i].ticket_prices.length - 1].price.toLocaleString()+'('+resp[i].order_code+' - '+((resp[i].type == 0) ? "Vé lượt":"Vé tháng")+')'
          });
        }
        this.spinner.hide();
      }
    );

    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.company.fullname = data.fullname;
        this.company.address = data.address;
        this.company.tax_code = data.tax_code;
        this.company.phone = data.phone;
        this.company.print_at = data.print_at;
        this.company.company_id = data.id;
      }
    );
  }

  getDataTicket(){

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.bsRangeValue) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if(!this.valueSelectedPrice['id']){
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_EXPORT_PRINT_TKT_PRICE'), 'warning');
      return;
    }

    this.spinner.show();
    //get data print ticket
    this.apiReports.managerReportsPrintTickets({
      to_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
      from_date: moment(this.bsRangeValue).format('YYYY-MM-DD'),
      price_id: this.valueSelectedPrice ? this.valueSelectedPrice['id'] : 0,
      }).subscribe(
        resp => {
          this.ticket_arr = resp;
          this.spinner.hide();
    });
  }

  setSeriTicketNumber(tktNumber, lenghtArr,){

    let strSeriCard = '';
    let check = false;
    var n =  lenghtArr.length - 1;

    for(var i = tktNumber.length - 1; i >= 0; i--){
      if(check) {
        for(var j = n; j >= 0; j-- ){
          lenghtArr[j] = tktNumber.charAt(i);
          check = true;
          n = j - 1;
          break;
        }
      }else {
        for(var j = n; j > 0; j-- ){
          lenghtArr[j] = tktNumber.charAt(i);
          check = true;
          n = j - 1;
          break;
        }
      } 
    }
    for(var m = 0; m < lenghtArr.length; m++){
      strSeriCard += lenghtArr[m];
    }

    return strSeriCard;
  }

  showPrintPreviewTicket(){

    let count_ticket_arr = this.ticket_arr.length;

    if (this.bsRangeValue === undefined) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if (!this.bsRangeValue) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_RP_DATE'), 'warning');
      return;
    }

    if(count_ticket_arr == 0){
      swal(this.translate.instant('SWAL_INFO'), this.translate.instant('SWAL_RP_NOT_DATA'), 'info');
    }

    if(count_ticket_arr > 0){

      let popupWin, layout = '';
      for(let i = 0 ; i < count_ticket_arr; i = i+2){

        layout += '<tr>';
       
        for(let j = i ; j < i+2; j++){

          if(this.ticket_arr[j] !== undefined){
           
            let station_way = JSON.parse(this.ticket_arr[j]['station_data']);

            let arrLenght = ['0','0','0','0','0','0','0'];
            let ticket_number = this.setSeriTicketNumber(this.ticket_arr[j]['ticket_number'],arrLenght);
            
            if (this.company.company_id == 6) {

              if (this.ticket_arr[j]['ticket_type'] == 0) {

                let discount = (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']) > 0 ? (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']).toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4') : '';
                let collected = this.ticket_arr[j]['amount'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4');

                layout += '<td>'
                  + '<div class="tx-10 tx-center tx-transform"><span> <strong>' + this.company.fullname + ' </strong></span></div>'
                  + '<div class="tx-10 tx-center"><span>' + this.company.address + '</span></div>'

                  + '<div class=" tx-10">' + this.translate.instant('LBL_PRINT_TICKET_ORDER_CODE') + ': <span>' + this.ticket_arr[j]['order_code'] + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_TAX_CODE') + ': <span>' + this.company.tax_code + '</span></div>'
                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_NUMBER') + ': <span>' + ticket_number + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_SIGN') + ': <span>' + this.ticket_arr[j]['sign'] + '</span></div>'

                  + '<div class="tx-10 tx-center tx-transform"><strong>' + this.translate.instant('LBL_PRINT_TICKET_TITLE') + '</strong></div>'
                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE1') + ')</span></div>'


                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_ROUTE_NUMBER') + ': <strong>' + this.ticket_arr[j]['route_number'] + '</strong>' + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_BUS_STATION') + ': <strong>' + this.ticket_arr[j]['station_name'] + '</strong></div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_STAFF') + ': ' + this.ticket_arr[j]['staff'] + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_PRICE') + ': <strong>' + this.ticket_arr[j]['price'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4') + '</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_DISCOUNT') + ': <strong>' + discount + '</strong></div> '

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_COLLECTED') + ': <strong>' + collected + '</strong> </div>'

                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE2') + ')</span></div>'

                  + '<div class="tx-10"><span>' + this.translate.instant('LBL_PRINT_TICKET_PRINT_DATE') + ': </span><strong>' + moment(this.ticket_arr[j]['activated']).format('HH:mm:ss DD-MM-YYYY') + '</strong></div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_PRINT_AT') + ': <span>' + (this.company.print_at ? this.company.print_at : '') + '</span></div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_BALANCE') + ': <span>' + (this.ticket_arr[j]['balance'] ? this.ticket_arr[j]['balance'] + 'Đ' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;') + '</span>' + this.translate.instant('LBL_PRINT_TICKET_HOTLINE') + ': <span>' + this.company.phone + '</span></div>'
                  // + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_BALANCE') + ': <span><strong>' + (this.ticket_arr[j]['balance'] ? this.ticket_arr[j]['balance']+'Đ' : '') + '</strong></span></div>'
                  + '</td>';

              } else {

                let discount = (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']) > 0 ? (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']).toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4_MONTH') : '';
                let collected = this.ticket_arr[j]['amount'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4_MONTH');

                layout += '<td>'
                  + '<div class="tx-10 tx-center tx-transform"><span> <strong>' + this.company.fullname + ' </strong></span></div>'
                  + '<div class="tx-10 tx-center"><span>' + this.company.address + '</span></div>'


                  + '<div class=" tx-10">' + this.translate.instant('LBL_PRINT_TICKET_ORDER_CODE') + ': <span>' + this.ticket_arr[j]['order_code'] + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_TAX_CODE') + ': <span>' + this.company.tax_code + '</span></div>'
                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_NUMBER') + ': <span>' + ticket_number + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_SIGN') + ': <span>' + this.ticket_arr[j]['sign'] + '</span></div>'

                  + '<div class="tx-10 tx-center tx-transform"><strong>' + this.translate.instant('LBL_PRINT_TICKET_TITLE_MONTH') + '</strong></div>'
                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE1') + ')</span></div>'


                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_ROUTE_NUMBER') + ': <strong>' + this.ticket_arr[j]['route_number'] + '</strong></div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_STATION_WAY') + ': ' + ((station_way != null) ? (station_way[0] + ' - ' + station_way[1]) : "Áp dụng trên toàn tuyến") + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_BARCODE') + ': ' + this.ticket_arr[j]['barcode'] + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_FULLNAME') + ': ' + this.ticket_arr[j]['fullname'] + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_PRICE') + ': <strong>' + this.ticket_arr[j]['price'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4_MONTH') + '<br></strong>' + this.translate.instant('LBL_PRINT_TICKET_DISCOUNT') + ': <strong>' + discount + '</strong></div> '

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_COLLECTED') + ': <strong>' + collected + '</strong> </div>'

                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE2') + ')</span></div>'

                  + '<div class="tx-10"><span>' + this.translate.instant('LBL_PRINT_TICKET_PRINT_DATE') + ': </span><strong>' + moment(this.ticket_arr[j]['activated']).format('HH:mm:ss DD-MM-YYYY') + '</strong></div>'

                  + '<div class="tx-10"><span>' + this.translate.instant('LBL_PRINT_TICKET_HOTLINE') + ': </span><strong>' + this.company.phone + '</strong></div>'
                  + '</td>';
              }

            } else if (this.company.company_id == 11) {

              if (this.ticket_arr[j]['ticket_type'] == 0) {

                // let discount = (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']) > 0 ? (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']).toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4') : '';
                // let collected = this.ticket_arr[j]['amount'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4');
                layout += '<td>'
                  + '<div class="tx-10 tx-center tx-transform"><span> <strong>' + this.company.fullname + ' </strong></span></div>'
                  + '<div class="tx-10 tx-center"><span>' + this.company.address + '</span></div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_TAX_CODE') + ': <span>' + this.company.tax_code + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_ORDER_CODE') + ': <span>' + this.ticket_arr[j]['order_code'] + '</span></div>'
                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_NUMBER') + ': <span>' + ticket_number + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_SIGN') + ': <span>' + (this.ticket_arr[j]['sign'] ? this.ticket_arr[j]['sign'] : '') + '</span></div>'

                  + '<div class="tx-10 tx-center tx-transform"><strong>' + this.translate.instant('LBL_PRINT_TICKET_TITLE') + '</strong></div>'
                  + '<div class="tx-10 tx-center"> <strong>' + this.translate.instant('LBL_PRINT_TICKET_ROUTE_NUMBER') + ' ' + this.ticket_arr[j]['route_number'] + '</strong></div>'
                  + '<div class="tx-10 tx-center"> <strong>' + this.ticket_arr[j]['route_name'] + '</strong></div>'
                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE1') + ')</span></div>'
                    
                  + '<div class="tx-10"><span>' + 'Ngày giờ in' + ': </span>' + moment(this.ticket_arr[j]['activated']).format('DD-MM-YYYY HH:mm:ss') + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_BSX') + ': ' + this.ticket_arr[j]['license_plates'] + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_STAFF') + ': ' + this.ticket_arr[j]['staff'] + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_PRICE') + ': <strong>' + this.ticket_arr[j]['price'].toLocaleString('de-DE') + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4') + '</strong></div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_PRINT_AT') + ': <span>' + (this.company.fullname ? this.company.fullname : '') + '</span></div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_HOTLINE') + ': <span>' + this.company.phone + '</span></div>'

                  + '</td>';

              } else {

                let discount = (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']) > 0 ? (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']).toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4_MONTH') : '';
                let collected = this.ticket_arr[j]['amount'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4_MONTH');

                layout += '<td>'
                  + '<div class="tx-10 tx-center tx-transform"><span> <strong>' + this.company.fullname + ' </strong></span></div>'
                  + '<div class="tx-10 tx-center"><span>' + this.company.address + '</span></div>'


                  + '<div class=" tx-10">' + this.translate.instant('LBL_PRINT_TICKET_ORDER_CODE') + ': <span>' + this.ticket_arr[j]['order_code'] + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_TAX_CODE') + ': <span>' + this.company.tax_code + '</span></div>'
                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_NUMBER') + ': <span>' + ticket_number + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_SIGN') + ': <span>' + this.ticket_arr[j]['sign'] + '</span></div>'

                  + '<div class="tx-10 tx-center tx-transform"><strong>' + this.translate.instant('LBL_PRINT_TICKET_TITLE_MONTH') + '</strong></div>'
                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE1') + ')</span></div>'


                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_ROUTE_NUMBER') + ': <strong>' + this.ticket_arr[j]['route_number'] + '</strong></div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_STATION_WAY') + ': ' + ((station_way != null) ? (station_way[0] + ' - ' + station_way[1]) : "Áp dụng trên toàn tuyến") + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_BARCODE') + ': ' + this.ticket_arr[j]['barcode'] + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_FULLNAME') + ': ' + this.ticket_arr[j]['fullname'] + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_PRICE') + ': <strong>' + this.ticket_arr[j]['price'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4_MONTH') + '<br></strong>' + this.translate.instant('LBL_PRINT_TICKET_DISCOUNT') + ': <strong>' + discount + '</strong></div> '

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_COLLECTED') + ': <strong>' + collected + '</strong> </div>'

                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE2') + ')</span></div>'

                  + '<div class="tx-10"><span>' + this.translate.instant('LBL_PRINT_TICKET_PRINT_DATE') + ': </span><strong>' + moment(this.ticket_arr[j]['activated']).format('HH:mm:ss DD-MM-YYYY') + '</strong></div>'

                  + '<div class="tx-10"><span>' + this.translate.instant('LBL_PRINT_TICKET_HOTLINE') + ': </span><strong>' + this.company.phone + '</strong></div>'
                  + '</td>';
              }

            } else if (this.company.company_id == 19) {

              if (this.ticket_arr[j]['ticket_type'] == 0) {

                let discount = (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']) > 0 ? (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']).toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4') : '';
                let collected = this.ticket_arr[j]['amount'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4');

                layout += '<td>'
                  + '<div class="tx-10 tx-center"><span><strong>' + this.company.fullname + '</strong></span></div>'
                  + '<div class="tx-10 tx-center"><span>' + this.company.address + '</span></div>'

                  + '<div class=" tx-10">' + this.translate.instant('LBL_PRINT_TICKET_TAX_CODE') + ': <span>' + this.company.tax_code + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_ORDER_CODE') + ': <span>' + this.ticket_arr[j]['order_code'] + '</span></div>'
                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_NUMBER') + ': <span>' + ticket_number + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_SIGN') + ': <span>' + this.ticket_arr[j]['sign'] + '</span></div>'

                  + '<div class="tx-10 tx-center tx-transform"><strong>' + this.translate.instant('LBL_PRINT_TICKET_TITLE') + '</strong></div>'
                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE1') + ')</span></div>'


                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_ROUTE_NUMBER') + ': <strong>' + this.ticket_arr[j]['route_number'] + '</strong>' + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_BSX') + ': <strong>' + this.ticket_arr[j]['license_plates'] + '</strong></div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_STATION_UP') + ': ' + this.ticket_arr[j]['station_name'] + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_STATION_DOWN') + ': ' + ((this.ticket_arr[j]['station_down']) ? this.ticket_arr[j]['station_down'] : "") + '</div>'

                  // + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_PRICE') + ': <strong>' + this.ticket_arr[j]['price'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4') + '</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_DISCOUNT') + ': <strong>' + discount + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4') + '</strong></div> '
                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_PRICE') + ': <strong>' + this.ticket_arr[j]['price'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4') + '</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_DISCOUNT') + ': <strong>' + discount + '</strong></div> '

                  // + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_COLLECTED') + ': <strong>' + collected + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4') + '</strong> </div>'
                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_COLLECTED') + ': <strong>' + collected + '</strong> </div>'

                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE2') + ')</span></div>'

                  + '<div class="tx-10"><span>' + this.translate.instant('LBL_PRINT_TICKET_PRINT_DATE') + ': </span><strong>' + moment(this.ticket_arr[j]['activated']).format('HH:mm:ss DD-MM-YYYY') + '</strong></div>'

                  // +'<div class="tx-10">'+this.translate.instant('LBL_PRINT_TICKET_PRINT_AT')+': <span>'+this.company.print_at+'</span></div>'
                  // +'<div class="tx-10">'+this.translate.instant('LBL_PRINT_TICKET_TAX_CODE')+': <span>'+this.company.tax_code +'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' +this.translate.instant('LBL_PRINT_TICKET_HOTLINE')+': <span>'+this.company.phone+'</span></div>'
                  + '<div class="tx-10"><span>' + this.translate.instant('LBL_PRINT_TICKET_HOTLINE') + ': </span>' + this.company.phone + '</div>'
                  // +'<div class="tx-10">'+this.translate.instant('LBL_PRINT_TICKET_BALANCE')+': <span></span></div>'
                  + '</td>';
              } else {

                let discount = (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']) > 0 ? (this.ticket_arr[j]['price'] - this.ticket_arr[j]['amount']).toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4_MONTH') : '';
                let collected = this.ticket_arr[j]['amount'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4_MONTH');

                layout += '<td>'
                  + '<div class="tx-10 tx-center"><span><strong>' + this.company.fullname + '</strong></span></div>'
                  + '<div class="tx-10 tx-center"><span>' + this.company.address + '</span></div>'


                  + '<div class=" tx-10">' + this.translate.instant('LBL_PRINT_TICKET_TAX_CODE') + ': <span>' + this.company.tax_code + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_ORDER_CODE') + ': <span>' + this.ticket_arr[j]['order_code'] + '</span></div>'
                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_NUMBER') + ': <span>' + ticket_number + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + this.translate.instant('LBL_PRINT_TICKET_SIGN') + ': <span>' + this.ticket_arr[j]['sign'] + '</span></div>'

                  + '<div class="tx-10 tx-center tx-transform"><strong>' + this.translate.instant('LBL_PRINT_TICKET_TITLE_MONTH') + '</strong></div>'
                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE1') + ')</span></div>'


                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_ROUTE_NUMBER') + ': <strong>' + this.ticket_arr[j]['route_number'] + '</strong></div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_STATION_WAY') + ': ' + ((station_way != null) ? (station_way[0] + ' - ' + station_way[1]) : "Áp dụng trên toàn tuyến") + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_BARCODE') + ': ' + this.ticket_arr[j]['barcode'] + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_FULLNAME') + ': ' + this.ticket_arr[j]['fullname'] + '</div>'

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_PRICE') + ': <strong>' + this.ticket_arr[j]['price'].toLocaleString() + ' ' + this.translate.instant('LBL_PRINT_TICKET_NOTE4_MONTH') + '<br></strong>' + this.translate.instant('LBL_PRINT_TICKET_DISCOUNT') + ': <strong>' + discount + '</strong></div> '

                  + '<div class="tx-10">' + this.translate.instant('LBL_PRINT_TICKET_COLLECTED') + ': <strong>' + collected + '</strong> </div>'

                  + '<div class="tx-10 tx-center"><span>(' + this.translate.instant('LBL_PRINT_TICKET_NOTE2') + ')</span></div>'

                  + '<div class="tx-10"><span>' + this.translate.instant('LBL_PRINT_TICKET_PRINT_DATE') + ': </span><strong>' + moment(this.ticket_arr[j]['activated']).format('HH:mm:ss DD-MM-YYYY') + '</strong></div>'

                  + '<div class="tx-10"><span>' + this.translate.instant('LBL_PRINT_TICKET_HOTLINE') + ': </span>' + this.company.phone + '</div>'
                  + '</td>';
              }

            } else {
              swal(this.translate.instant('SWAL_INFO'), this.translate.instant('SWAL_RP_NOT_LAYOUT'), 'info');
            }
          }
        }
        layout += '</tr>';
      }
      popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
      popupWin.document.open();
      popupWin.document.write(`
        <html>
          <head>
            <title></title>
            <style>
            @page{ size: A4; }
            table{ margin:auto;border-collapse:collapse;border: 1px solid black;page-break-inside:auto }
            tr{ page-break-inside:avoid; page-break-after:auto }
            td {border: 1px solid black;padding: 10px}
            .w-2{ width: 0.30cm; }
            .fl{float:left}
            .tx-center{text-align: center}
            .tx-left{text-align: left}
            .tx-right{text-align: right}
            .tx-justify{text-align: justify}
            .tx-transform{text-transform: uppercase;}
            .tx-10{font-family: 'Arial';font-size: 11px;}
            </style>
          </head>
          <body onload="window.print();window.close()">
            <div class="d-none">
              <table>
              `+ layout + `
              </table>
            </div>
          </body>
         </html>`
      );
      popupWin.document.close();
    }
  }

  refreshValuePrice(value:any):void {
    this.valueSelectedPrice = value;
  }

  public selectedPrice(value: any) {
    this.valueSelectedPrice = value;
    this.getDataTicket();
  }

  public removedPrice(value: any){

    if( this.valueSelectedPrice['id'] == value.id){
      this.valueSelectedPrice['id'] = null;
    }
    this.getDataTicket();
  }
}