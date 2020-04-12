import { Component, OnInit, ViewChild, ViewEncapsulation, AfterViewInit, Pipe, PipeTransform } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import * as moment from 'moment';

import { ManagerUsersService, ManagerVehiclesService , ManagerReportsService, ManagerModuleCompanyService, ManagerShiftsService, ManagerCompaniesService, ManagerHistoryShiftsService } from '../../../../api/services';
import { User, UserSearch, ReceiptView, ReceiptForm, Permission, NumberConvert, Vehicle } from '../../../../api/models';
import { ReceiptSummary, ReceiptTransaction, UpdateShiftForm, RpTicketDestroyForm, RpShiftDestroyForm } from '../../../../api/models';
import { map } from 'rxjs/operators/map';
import { HttpErrorResponse } from '@angular/common/http';
import { saveAs } from 'file-saver/FileSaver';

import { QtSocketService } from '../../../../shared/qt-socket.service';
import { Subscription, empty } from 'rxjs';
import { SocketComponent } from '../../../../shared/socket-component';
import { isNumber } from 'util';

import { transliterate as tr, slugify } from 'transliteration';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { element } from '@angular/core/src/render3/instructions';

@Pipe({ name: 'filter' })
export class FilterPipe implements PipeTransform {
  public transform(arrayUsers: User[], filter: string): any[] {
    if (!arrayUsers || !arrayUsers.length) {
      return [];
    }
    if (!filter) {
      return arrayUsers;
    }
    return arrayUsers.filter(user => {
      return tr(user.fullname).toLowerCase().indexOf(tr(filter).toLowerCase()) >= 0;
    });
  }
}

@Component({
  selector: 'app-receipts',
  templateUrl: './receipts.component.html',
  styleUrls: ['./receipts.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class ReceiptsComponent implements OnInit, AfterViewInit, SocketComponent {

  @ViewChild('listUserModal') public listUserModal: ModalDirective;
  @ViewChild('detailReceiptModel') public detailReceiptModel: ModalDirective;
  @ViewChild('listUserByShiftByRole') public listUserByShiftByRole: ModalDirective;
  @ViewChild('addDestroyTicket') public addDestroyTicket: ModalDirective;
  @ViewChild('addDestroyShift') public addDestroyShift: ModalDirective;
  @ViewChild('listVehical') public listVehical: ModalDirective;
  @ViewChild('modalWarning') public modalWarning: ModalDirective;

  public input_User_Name = '';
  public update_shift: any = {};
  public users: User[];
  public user_roles: User[];
  public userSearch: UserSearch;
  public receipts: any = [];
  public receiptForm: ReceiptForm;
  public vehicle_Items: Vehicle[];

  // public typeImage: any = '';
  // public strImageBase64: any = '';

  public isDisabled: any = false;
  public ticketDestroyCreate: RpTicketDestroyForm;
  public shiftDestroyCreate: RpShiftDestroyForm;
  public summaries: ReceiptSummary[] = [];
  public transactions: ReceiptTransaction[] = [];

  private socketSubscription: Subscription;
  public searchUserName = '';
  public currentTime: Date = new Date();
  public maxDate: Date;

  public days: string;
  public months: string;
  public years: string;

  public payer_info: string = '';
  public route_name_info: string = '';
  public station_name_info: string = '';

  public timestamp_tmp: any = [];
  public receipts_tmp: any = [];
  public route_name_tmp: any = [];
  public collected_tmp: number[] = [];
  public station_name_tmp: any = [];
  public selectedReceipt: number[] = [];

  public convert_number_all: string;
  public convert_number_pos: string;
  public convert_number_deposit: string;
  public convert_number_goods: string;
  public date_started_ended = '';
  public time_started = '';
  public time_started_index = '';
  public time_ended = '';
  public time_ended_index = '';

  public totalSummary: number;
  public totalGoods: number;
  public inputUserName: string;

  public checkedTotalPrice: number;
  public checkedTotalPos: number;
  public checkedTotalDeposit: number;
  public checkedTotalCharge: number;
  public checkedTotalGoods: number;

  public shift_id: number = 0;
  public collected: number = 0;

  public isCheckAll = false;
  public isCheckAllTotal = false;
  public isFound = false;
  public isLoading = false;
  public isCollected = false;
  public laddaBtnDetail: boolean[];
  public isNow = false;
  public ended = false;

  public driver = true;
  public index_shift: number;
  public driver_name = '';
  public isExport = false;
  public isModuleGoos = false;
  public company: any;
  public shift: any;

  //private refresh: number = 0;

  public statusView: number = 1;
  public dateOnly: any = [];
  public dateAll: any;
  public dateDefault: any = [];
  public permissions: any[] = [];

  public inputLicensePlates: any = '';
  public vehicle_id: any;
  public licensePlatesInput: any;

  public user_admin: any = '';
  public info_warning: any;
  public is_warning: false;

  constructor(
    private apiUsers: ManagerUsersService,
    private apiReport: ManagerReportsService,
    private qtSocket: QtSocketService,
    private apiShifts: ManagerShiftsService,
    private apiCompanies: ManagerCompaniesService,
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
    private apiHistoryShift: ManagerHistoryShiftsService,
    private apiModuleCompanies: ManagerModuleCompanyService,
    private apiVehicles: ManagerVehiclesService,

  ) {
    this.userSearch = new UserSearch();
    this.userSearch.rfid = null;
    this.userSearch.barcode = null;
    this.receiptForm = new ReceiptForm();
    this.ticketDestroyCreate = new RpTicketDestroyForm();
    this.shiftDestroyCreate = new RpShiftDestroyForm();
    this.maxDate = new Date();
  }

  ngOnInit() {
    this.receiptForm.date = this.currentTime.toISOString().slice(0, 10);
    // this.company = JSON.parse(localStorage.getItem('user'));
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
    this.user_admin = localStorage.getItem('token_shadow');
  }

  socketDown() {
    // console.log('clean up blank card socket');
    // this.socketSubscription.unsubscribe();
  }

  socketUp() {
    // this.socketSubscription = this.qtSocket.onData().subscribe(
    //   data => {
    //     console.log('from subscription: ', data.toString());
    //     this.userSearch.rfid = data.toString().split(':').pop();
    //   }
    // );
  }

  ngAfterViewInit() {
    // this.socketUp();
    this.refreshView();
    this.getComapny();
  }

  getComapny() {
    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.company = data;
      }
    );
  }

  refreshView() {
    this.apiUsers.managerListUsersResponse({
      page: 1,
      limit: 99999
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {

        this.inputUserName = '';
        this.users = resp.body.filter(
          (user) => {
            if (user.role.name === 'driver' || user.role.name === 'subdriver') {
              return user;
            }
          });
      }
    );

    //get module company
    this.apiModuleCompanies.listModuleCompany().subscribe(data => {
      data.forEach(element => {
        if (element['name'] === 'Module_VC_Hang_Hoa') {
          this.isModuleGoos = true;
        }
      });
    })

  }

  searchOption(opt) {
    this.statusView = opt;
  }

  searchUser() {

    this.isLoading = true;

    if (!this.userSearch.rfid && !this.userSearch.barcode) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('RECEIPT_ERROR_RFID_BARCODE'), 'warning');
      return;
    }

    this.apiUsers.managerSearchUser({
      rfid: this.userSearch.rfid,
      barcode: this.userSearch.barcode,
    }).subscribe(
      resp => {
        if (resp.role.name !== 'driver' && resp.role.name !== 'subdriver') {
          this.isLoading = false;
          swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('RECEIPT_ERROR_NOT_DRIVE') });
          return;
        }

        this.receiptForm.user_id = resp.id;
        this.searchUserName = resp.fullname;
        this.isLoading = false;
      },
      err => {
        this.isLoading = false;
        swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_NO_DATA') });
      }
    );
  }

  showListUserModal() {
    this.listUserModal.show();
  }

  showListUserByShiftId(id: number, rl: number, index: number) {

    this.driver = true;

    this.input_User_Name = '';
    if (!id) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('RECEIPT_ERROR_NOT_SHIFT'), 'error');
      return;
    }

    //set shiftId
    if (rl == 0) this.driver = false;
    if (rl == 1) this.driver = true;
    this.index_shift = index;
    this.update_shift.shift_id = id;

    this.apiUsers.managerListUsersResponse({
      page: 1,
      limit: 99999
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {
        if (rl === 1) {
          this.user_roles = resp.body.filter(
            (user_role) => {
              if (user_role.role.name === 'driver') {
                return user_role;
              }
            });
        }
        if (rl === 0) {
          this.user_roles = resp.body.filter(
            (user_role) => {
              if (user_role.role.name === 'subdriver') {
                return user_role;

              }
            });
        }
      });

    //show viewChild  listUserByShiftByRole
    this.listUserByShiftByRole.show();
  }

  showdetailReceiptModal(id: number, index: number, collected: number) {

    //set shift_id = id
    this.shift_id = id;
    this.collected = collected;

    if (!id) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('RECEIPT_ERROR_NOT_SHIFT'), 'error');
      return;
    }

    this.laddaBtnDetail[index] = true;

    this.apiReport.managerReportsGetReceiptDetailByShiftId(id).subscribe(
      resp => {

        this.totalSummary = 0;
        this.summaries = resp.summary;
        for (let i = 0; i < this.summaries.length; i++) {
          if (this.summaries[i].price == 'pos' || this.summaries[i].price == 'deposit' || this.summaries[i].price == 'deposit_month' || this.summaries[i].price == 'pos_goods') {
            this.totalSummary += this.summaries[i].total_price;
          }
        }

        this.transactions = resp.transactions;
        this.detailReceiptModel.show();
        this.laddaBtnDetail[index] = false;
      },
      err => {
        this.laddaBtnDetail[index] = false;
        swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('RECEIPT_ERROR_SHOW_DETAIL') });
      }
    );
  }

  showModalDestroyTicket(
    transaction_id: number,
    shift_id: number,
    ticket_price_id: number,
    ticket_number: any,
    type: string,
    amount: number,
    created_at: string,
    subuser_id: number
  ) {
    this.addDestroyTicket.show();
    this.detailReceiptModel.hide();
    this.ticketDestroyCreate.transaction_id = transaction_id;
    this.ticketDestroyCreate.shift_id = shift_id;
    this.ticketDestroyCreate.ticket_price_id = ticket_price_id;
    this.ticketDestroyCreate.ticket_number = ticket_number;
    this.ticketDestroyCreate.type = type;
    this.ticketDestroyCreate.amount = amount;
    this.ticketDestroyCreate.printed_at = created_at;
    this.ticketDestroyCreate.subuser_id = subuser_id;
  }

  showPrintPreview(typePrint: string) {

    if (typePrint === "all") {

      if (this.selectedReceipt.length <= 0) {
        swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('RECEIPT_ERROR_CHECKED') });
        return;
      }

      if (this.collected_tmp.indexOf(0) >= 0) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('RECEIPT_ERROR_PRINT_NOT_COLLECTED'), 'warning');
        return;
      }

      let printContents, popupWin;
      printContents = document.getElementById('print-section').innerHTML;
      popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
      popupWin.document.open();
      popupWin.document.write(`
        <html>
          <head>
            <title></title>
            <style>
            @page { size: A4; }
            .tx-center{text-align: center}
            .tx-left{text-align: left}
            .tx-10{font-size: 15px; font-family: 'Times New Roman';}
            .tx-11{font-size: 12px; font-family: 'Times New Roman';}
            .tx-12{font-size: 20px; font-family: 'Times New Roman';}
            .w-10{width: 10cm}
            .w-3{width: 3cm;float:left}
            .fl{float:left}
            .fr{float:right}
            .w-4{width: 4cm}
            .w-2{width: 1.5cm}
            .pt-0{margin-top: 0}
            </style>
          </head>
          <body onload="window.print();window.close()">`+ printContents + `</body>
        </html>`
      );
      popupWin.document.close();
    }

    if (typePrint === "any") {

      let printContents, popupWin;
      printContents = document.getElementById('print-section').innerHTML;
      popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
      popupWin.document.open();
      popupWin.document.write(`
          <html>
            <head>
              <title></title>
              <style>
              @page { size: A4; }
              .tx-center{text-align: center}
              .tx-left{text-align: left}
              .tx-10{font-size: 15px; font-family: 'Times New Roman';}
              .tx-11{font-size: 12px; font-family: 'Times New Roman';}
              .tx-12{font-size: 20px; font-family: 'Times New Roman';}
              .w-10{width: 10cm}
              .w-3{width: 3cm;float:left}
              .fl{float:left}
              .fr{float:right}
              .w-4{width: 4cm}
              .w-2{width: 1.5cm}
              .pt-0{margin-top: 0}
              </style>
            </head>
            <body onload="window.print();window.close()">`+ printContents + `</body>
          </html>`
      );
      popupWin.document.close();
    }

    if (typePrint === "demo") {

      if (this.selectedReceipt.length <= 0) {
        swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('RECEIPT_ERROR_CHECKED') });
        return;
      }

      let printContents, popupWin;
      printContents = document.getElementById('print-section-demo').innerHTML;
      popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
      popupWin.document.open();
      popupWin.document.write(`
        <html>
          <head>
            <title></title>
            <style>
            @page { size: A4; }
            .tx-center{text-align: center}
            .tx-left{text-align: left}
            .tx-10{font-size: 15px; font-family: 'Times New Roman';}
            .tx-11{font-size: 12px; font-family: 'Times New Roman';}
            .tx-12{font-size: 20px; font-family: 'Times New Roman';}
            .w-10{width: 10cm}
            .w-3{width: 3cm;float:left}
            .fl{float:left}
            .fr{float:right}
            .w-4{width: 4cm}
            .w-2{width: 1.5cm}
            .pt-0{margin-top: 0}
            </style>
          </head>
          <body onload="window.print();window.close()">`+ printContents + `</body>
        </html>`
      );
      popupWin.document.close();


    }
  }

  showModalDestroyShift(receipt: object) {
    // console.log(receipt);
    this.addDestroyShift.show();
    this.shiftDestroyCreate.license_plates = receipt['license_plates'];
    this.shiftDestroyCreate.route_id = receipt['route_id'];
    this.shiftDestroyCreate.shift_id = receipt['shift_id'];
    this.shiftDestroyCreate.total_charge = receipt['total_charge'];
    this.shiftDestroyCreate.total_deposit = receipt['total_deposit'];
    this.shiftDestroyCreate.total_pos = receipt['total_price'];
    this.shiftDestroyCreate.work_time = receipt['date_time'];
  }

  chooseUser(id: number) {
    this.users.map(
      (user) => {
        if (user.id === id) {
          this.searchUserName = user.fullname;
          this.receiptForm.user_id = user.id;
          this.listUserModal.hide();
          if (this.statusView == 1) {
            this.searchReceipt();
          }
          if (this.statusView == 2) {
            this.searchDateOnly(1);
          }
        }
      });
  }

  //  ---------------If build server new , show comment there ----------------------------
  searchDateOnly(opt = null) {

    this.isLoading = true;
    this.ended = false;
    let dateForm = null;
    let dateTo = null;

    this.days = moment(this.maxDate).format('DD').toString();
    this.months = moment(this.maxDate).format('MM').toString();
    this.years = moment(this.maxDate).format('YYYY').toString();

    //this.refresh = 2;
    this.spinner.show();

    if (opt) {
      this.dateOnly = null;
    } else {
      dateForm = moment(this.dateOnly[0]).format('YYYY-MM-DD');
      dateTo = moment(this.dateOnly[1]).format('YYYY-MM-DD');
      this.receiptForm.user_id = null;
      this.searchUserName = '';
    }

    this.apiReport.managerReportsViewNotCollectMoneyReceipt({
      date: dateForm,
      date_to: dateTo,
      user_id: this.receiptForm.user_id
    }).subscribe(resp => {

      this.isNow = moment(this.receiptForm.date).format('YYYY-MM-DD') === moment(this.currentTime).format('YYYY-MM-DD') ? true : false;
      if (resp.length > 0 && !resp[0].date_time) {
        this.receipts = [];
        this.ended = true;
      } else {
        this.receipts = resp;
      }
      this.spinner.hide();

      this.checkedTotalPos = 0;
      this.checkedTotalDeposit = 0;
      this.checkedTotalCharge = 0;
      this.checkedTotalGoods = 0;

      this.checkedTotalPrice = this.checkedTotalPos + this.checkedTotalDeposit + this.checkedTotalGoods;
      this.laddaBtnDetail = new Array<boolean>(this.receipts.length);
      this.laddaBtnDetail.fill(false);
      this.isFound = true;
      this.isLoading = false;
    });

    this.isCheckAll = false;
    this.isCheckAllTotal = false;

    this.selectedReceipt = [];
    this.collected_tmp = [];
    this.receipts_tmp = [];
    this.timestamp_tmp = [];
  }

  searchDateAll() {
    this.isLoading = true;
    this.ended = false;

    this.days = moment(this.maxDate).format('DD').toString();
    this.months = moment(this.maxDate).format('MM').toString();
    this.years = moment(this.maxDate).format('YYYY').toString();

    //this.refresh = 2;
    this.spinner.show();
    this.apiReport.managerReportsViewAllReceipt({
      date: moment(this.dateAll).format('YYYY-MM-DD'),
      date_to: null,
      user_id: null
    }).subscribe(resp => {
      this.isNow = moment(this.receiptForm.date).format('YYYY-MM-DD') === moment(this.currentTime).format('YYYY-MM-DD') ? true : false;
      if (resp.length > 0 && !resp[0].date_time) {
        this.receipts = [];
        this.ended = true;
      } else {
        this.receipts = resp;
      }
      this.spinner.hide();

      this.checkedTotalPos = 0;
      this.checkedTotalDeposit = 0;
      this.checkedTotalCharge = 0;
      this.checkedTotalGoods = 0;

      this.checkedTotalPrice = this.checkedTotalPos + this.checkedTotalDeposit + this.checkedTotalGoods;
      this.laddaBtnDetail = new Array<boolean>(this.receipts.length);
      this.laddaBtnDetail.fill(false);
      this.isFound = true;
      this.isLoading = false;
    });

    this.isCheckAll = false;
    this.isCheckAllTotal = false;

    this.selectedReceipt = [];
    this.collected_tmp = [];
    this.receipts_tmp = [];
    this.timestamp_tmp = [];
  }

  searchReceipt() {

    this.isLoading = true;
    this.ended = false;

    this.days = moment(this.maxDate).format('DD').toString();
    this.months = moment(this.maxDate).format('MM').toString();
    this.years = moment(this.maxDate).format('YYYY').toString();

    //this.refresh = 1;

    if (!this.receiptForm.user_id) {
      this.isFound = false;
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('RECEIPT_ERROR_USR'), 'warning');
      return;
    }

    if (this.dateDefault.length <= 0) {
      this.isLoading = false;
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('RECEIPT_ERROR_DATE'), 'warning');
      return;
    }

    this.spinner.show();

    this.apiReport.managerReportsViewReceipt({
      date: moment(this.dateDefault[0]).format('YYYY-MM-DD'),
      date_to: moment(this.dateDefault[1]).format('YYYY-MM-DD'),
      user_id: this.receiptForm.user_id,
      vehicle_id: this.vehicle_id
    }).subscribe(
      resp => {
        this.isNow = moment(this.receiptForm.date).format('YYYY-MM-DD') === moment(this.currentTime).format('YYYY-MM-DD') ? true : false;
        if (resp.length > 0 && !resp[0].date_time) {
          this.receipts = [];
          this.ended = true;
        } else {
          this.receipts = resp;
        }
        this.spinner.hide();

        this.checkedTotalPos = 0;
        this.checkedTotalDeposit = 0;
        this.checkedTotalCharge = 0;
        this.checkedTotalGoods = 0;

        this.checkedTotalPrice = this.checkedTotalPos + this.checkedTotalDeposit + this.checkedTotalGoods;
        this.laddaBtnDetail = new Array<boolean>(this.receipts.length);
        this.laddaBtnDetail.fill(false);
        this.isFound = true;
        this.isLoading = false;

      }
    );

    this.isCheckAll = false;
    this.isCheckAllTotal = false;

    this.selectedReceipt = [];
    this.collected_tmp = [];
    this.receipts_tmp = [];
    this.timestamp_tmp = [];
  }

  tiketDestroyCreate() {

    if (!this.ticketDestroyCreate.description) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TICKET_DESTROY_DES'), 'warning');
      return;
    }
    this.isDisabled = true;

    this.apiReport.managerReportsAddTicketDestroyInTransaction({
      transaction_id: this.ticketDestroyCreate.transaction_id,
      shift_id: this.ticketDestroyCreate.shift_id,
      ticket_price_id: this.ticketDestroyCreate.ticket_price_id,
      ticket_number: this.ticketDestroyCreate.ticket_number,
      type: this.ticketDestroyCreate.type,
      amount: this.ticketDestroyCreate.amount,
      printed_at: this.ticketDestroyCreate.printed_at,
      description: this.ticketDestroyCreate.description ? this.ticketDestroyCreate.description : "",
      image: 'image error',
      subuser_id: this.ticketDestroyCreate.subuser_id,
    }).subscribe(
      res => {
        this.addDestroyTicket.hide();
        this.ticketDestroyCreate = new RpTicketDestroyForm();
        swal(this.translate.instant('SWAL_DESTROY_PLEASE_SUCCESS'), this.translate.instant('SWAL_DESTROY_PLEASE_DES_SUCCESS'), 'success');

        if (this.statusView == 1) this.searchReceipt();

        if (this.statusView == 2) {
          if (this.receiptForm.user_id) {
            this.searchDateOnly(1);
          } else {
            this.searchDateOnly();
          }
        }

        if (this.statusView == 3) this.searchDateAll();
        this.isDisabled = false;
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          } else if (err.status === 422) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD') });
          }
        }
      }
    );

    //  ---------------If build server new , show comment there ----------------------------

    // if(!this.strImageBase64){
    //   swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TICKET_DESTROY_IMAGE'), 'warning');
    //   return;
    // }else{
    //   if(this.typeImage == 'image/jpeg' || this.typeImage == 'image/png'){

    //     this.apiReport.managerReportsAddTicketDestroyInTransaction({
    //       transaction_id: this.ticketDestroyCreate.transaction_id,
    //       shift_id: this.ticketDestroyCreate.shift_id,
    //       ticket_price_id: this.ticketDestroyCreate.ticket_price_id,
    //       ticket_number:  this.ticketDestroyCreate.ticket_number,
    //       type: this.ticketDestroyCreate.type,
    //       amount:  this.ticketDestroyCreate.amount,
    //       printed_at: this.ticketDestroyCreate.printed_at,
    //       description: this.ticketDestroyCreate.description ? this.ticketDestroyCreate.description : "",
    //       image: 'image error',
    //     }).subscribe(
    //       res => {
    //         this.addDestroyTicket.hide();
    //         this.ticketDestroyCreate = new RpTicketDestroyForm();
    //         swal(this.translate.instant('SWAL_DESTROY_PLEASE_SUCCESS'), this.translate.instant('SWAL_DESTROY_PLEASE_DES_SUCCESS'), 'success');

    //         if(this.statusView == 1 ) this.searchReceipt();

    //         if(this.statusView == 2 ){
    //           if (this.receiptForm.user_id) {
    //             this.searchDateOnly(1);
    //           } else {
    //             this.searchDateOnly();
    //           }
    //         }

    //         if(this.statusView == 3 )this.searchDateAll();
    //       },
    //       err => {
    //         if (err instanceof HttpErrorResponse) {
    //           if (err.status === 404) {
    //             swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
    //           } else if (err.status === 422) {
    //             swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD')});
    //           }
    //         }
    //       }
    //     );
    //   }else{

    //     swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TICKET_DESTROY_IMAGE_FORMAT'), 'warning');
    //     return ;
    //   }
    // }
  }

  createShiftDestroy() {
    if (!this.shiftDestroyCreate.description) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_SHIFT_DESTROY_DES'), 'warning');
      return;
    }

    this.apiReport.managerReportsAddShiftDestroy({
      shift_id: this.shiftDestroyCreate.shift_id,
      total_charge: this.shiftDestroyCreate.total_charge,
      total_deposit: this.shiftDestroyCreate.total_deposit,
      total_pos: this.shiftDestroyCreate.total_pos,
      route_id: this.shiftDestroyCreate.route_id,
      license_plates: this.shiftDestroyCreate.license_plates,
      description: this.shiftDestroyCreate.description ? this.shiftDestroyCreate.description : "",
      work_time: this.shiftDestroyCreate.work_time
    }).subscribe(
      res => {
        this.addDestroyShift.hide();
        this.shiftDestroyCreate = new RpShiftDestroyForm();
        swal(this.translate.instant('SWAL_DESTROY_PLEASE_SUCCESS'), this.translate.instant('SWAL_SHIFT_DESTROY_PLEASE_DES_SUCCESS'), 'success');

        if (this.statusView == 1) this.searchReceipt();

        if (this.statusView == 2) {
          if (this.receiptForm.user_id) {
            this.searchDateOnly(1);
          } else {
            this.searchDateOnly();
          }
        }

        if (this.statusView == 3) this.searchDateAll();

        this.isDisabled = false;
      },

      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          } else if (err.status === 422) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD') });
          }
        }
      }
    );
  }

  onReceiptChangedTotal(event) {

    this.isCheckAll = !this.isCheckAll;
    this.checkedTotalDeposit = 0;
    this.checkedTotalPos = 0;
    this.checkedTotalCharge = 0;
    this.checkedTotalGoods = 0;
    this.checkedTotalPrice = this.checkedTotalDeposit + this.checkedTotalPos + this.checkedTotalGoods;

    if (this.isCheckAll) {

      this.selectedReceipt = [];
      this.collected_tmp = [];
      this.receipts_tmp = [];
      this.timestamp_tmp = [];

      this.isCheckAllTotal = true;
      this.receipts.forEach(e => {
        this.checkedTotalPos += e.total_price;
        this.checkedTotalDeposit += e.total_deposit;
        this.checkedTotalCharge += e.total_charge;
        this.checkedTotalGoods += e.total_goods;
        this.checkedTotalPrice = this.checkedTotalPos + this.checkedTotalDeposit + this.checkedTotalGoods;
        this.selectedReceipt.push(e.shift_id);
        this.collected_tmp.push(e.collected);
        this.receipts_tmp.push(e);
        this.timestamp_tmp.push(e.date_time);
      });
    } else {
      this.selectedReceipt = [];
      this.collected_tmp = [];
      this.receipts_tmp = [];
      this.timestamp_tmp = [];
    }

    if (this.receipts_tmp.length > 0) {
      this.setDiplayInfoReceipts();
    }
  }

  onReceiptChanged(event, receipt, i) {

    if (event.currentTarget.checked) {

      const index: number = this.selectedReceipt.indexOf(receipt.shift_id);
      const index_time: number = this.timestamp_tmp.indexOf(receipt.date_time);
      const index_receipt: number = this.receipts_tmp.indexOf(receipt);
      const index_collected: number = this.collected_tmp.indexOf(receipt.collected);

      if (index === -1) this.selectedReceipt.push(receipt.shift_id);
      if (index_time === -1) this.timestamp_tmp.push(receipt.date_time);
      if (index_receipt === -1) this.receipts_tmp.push(receipt);
      if (index_collected === -1) this.collected_tmp.push(receipt.collected);

      this.checkedTotalPos += receipt.total_price;
      this.checkedTotalDeposit += receipt.total_deposit;
      this.checkedTotalCharge += receipt.total_charge;
      this.checkedTotalGoods += receipt.total_goods;
      this.checkedTotalPrice = this.checkedTotalPos + this.checkedTotalDeposit + this.checkedTotalGoods;

    } else {

      const index: number = this.selectedReceipt.indexOf(receipt.shift_id);
      const index_receipt: number = this.receipts_tmp.indexOf(receipt);
      const index_time: number = this.timestamp_tmp.indexOf(receipt.date_time);
      const index_collected: number = this.collected_tmp.indexOf(receipt.collected);

      if(index_receipt !== -1) this.receipts_tmp.splice(index_receipt, 1);
      if(index_collected !== -1) this.collected_tmp.splice(index_collected, 1);
      if(index_time !== -1) this.timestamp_tmp.splice(index_time, 1);
      if(index !== -1) {

        this.selectedReceipt.splice(index, 1);
        this.checkedTotalPos -= receipt.total_price;
        this.checkedTotalDeposit -= receipt.total_deposit;
        this.checkedTotalCharge -= receipt.total_charge;
        this.checkedTotalGoods -= receipt.total_goods;
        this.checkedTotalPrice = this.checkedTotalPos + this.checkedTotalDeposit + this.checkedTotalGoods;
      }
    }
    if (this.receipts_tmp.length > 0) {
      this.setDiplayInfoReceipts();
    }
  }
  
  exportReceipt() {

    if (this.selectedReceipt.length <= 0) {
      swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('RECEIPT_ERROR_CHECKED') });
      return;
    }

    if (this.collected_tmp.indexOf(0) >= 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('RECEIPT_ERROR_EXPORT_NOT_COLLECTED'), 'warning');
      return;
    }

    this.isExport = true;
    this.apiReport.managerReportsExportReceiptResponse({
      date: moment(this.receiptForm.date).format('YYYY-MM-DD'),
      shifts: this.selectedReceipt
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      data => {
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
        const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel' });

        saveAs(blob, filename);
      }
    );
  }

  //function export file exel for transaction
  exportFileExel() {

    if (!this.shift_id) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('RECEIPT_ERROR_NOT_SHIFT'), 'error');
      return;
    }

    this.apiReport.managerReportsExportReceiptTransactionByShiftIdResponse(this.shift_id).subscribe(data => {

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
      const blob = new Blob([byteArray], { type: 'application/vnd.ms-excel' });

      saveAs(blob, filename);
    });
  }

  updateCollected() {

    if (this.selectedReceipt.length <= 0) {
      swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('RECEIPT_ERROR_CHECKED') });
      return;
    }

    if (this.collected_tmp.indexOf(1) >= 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('RECEIPT_ERROR_COLLECTED'), 'warning');
      return;
    }

    let dataUpdate = [];

    this.receipts_tmp.forEach(element => {
      // let shift = this.receipts_tmp.filter(item => item.shift_id == element);
      if (element.collected == 0) {
        let data = {
          "shift_id": element.shift_id,
          "total_price": element.total_price,
          "deposit": element.total_deposit,
          "shift_time": element.date_time
        }
        dataUpdate.push(data);
      }
    });

    if (dataUpdate.length > 0) {
      swal({
        title: this.translate.instant('SWAL_WARN'),
        text: this.translate.instant('RECEIPT_WARN_UPDATE'),
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: this.translate.instant('SWAL_UPDATE'),
        cancelButtonText: this.translate.instant('SWAL_CANCEL')
      }).then((result) => {
        if (result.value) {
          this.spinner.show();
          this.apiShifts.managerShiftsUpdateCollected({
            shifts: dataUpdate
          }).subscribe(
            data => {

              if (data) {

                this.spinner.hide();
                
                let printContents, popupWin;
                printContents = document.getElementById('print-section').innerHTML;
                popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
                popupWin.document.open();
                popupWin.document.write(`
                  <html>
                    <head>
                      <title></title>
                      <style>
                      @page { size: A4; }
                      .tx-center{text-align: center}
                      .tx-left{text-align: left}
                      .tx-10{font-size: 15px; font-family: 'Times New Roman';}
                      .tx-11{font-size: 12px; font-family: 'Times New Roman';}
                      .tx-12{font-size: 20px; font-family: 'Times New Roman';}
                      .w-10{width: 10cm}
                      .w-3{width: 3cm;float:left}
                      .fl{float:left}
                      .fr{float:right}
                      .w-4{width: 4cm}
                      .w-2{width: 1.5cm}
                      .pt-0{margin-top: 0}
                      </style>
                    </head>
                    <body onload="window.print();window.close()">`+ printContents + `</body>
                  </html>`
                );
                popupWin.document.close();
              }

              this.isCollected = false;
              this.selectedReceipt = [];
              this.collected_tmp = [];
              this.receipts_tmp = [];
              this.timestamp_tmp = [];
              this.checkedTotalPos = 0;
              this.checkedTotalDeposit = 0;
              this.checkedTotalGoods = 0;
              this.checkedTotalPrice = this.checkedTotalPos + this.checkedTotalDeposit + this.checkedTotalGoods;

              //create history shift
              if (this.statusView == 1) {
                this.searchReceipt();
              }

              if (this.statusView == 2) {
                //this.showViewAllReceiptCollectMoneyModal();
                if (this.receiptForm.user_id) {
                  this.searchDateOnly(1);
                } else {
                  this.searchDateOnly();
                }
              }

              if (this.statusView == 3) {
                //this.showViewAllReceiptModal();
                this.searchDateAll();
              }

              swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), ' ', 'success');
            }
          );
        } else {
          this.isCheckAll = false;
          this.isCheckAllTotal = false;
          this.isCollected = false;
          this.selectedReceipt = [];
          this.collected_tmp = [];
          this.receipts_tmp = [];
          this.timestamp_tmp = [];

          //create history shift
          if (this.statusView == 1) { this.searchReceipt(); }
          //this.showViewAllReceiptCollectMoneyModal();
          if (this.statusView == 2) { if (this.receiptForm.user_id) { this.searchDateOnly(1); } else { this.searchDateOnly(); } }
          //this.showViewAllReceiptModal();
          if (this.statusView == 3) { this.searchDateAll(); }
        }
      });
    }
  }

  updateCollectedBt(shiftId: number, receipt) {

    let collectData = [];

    if (shiftId !== undefined) {

      let shift = this.receipts.filter(item => item.shift_id == shiftId);

      let data = {
        "shift_id": shift[0].shift_id,
        "total_price": shift[0].total_price,
        "deposit": shift[0].total_deposit,
        "shift_time": shift[0].date_time
      }
      collectData.push(data);
      this.receipts_tmp.push(receipt);

      this.checkedTotalPos = shift[0].total_price;
      this.checkedTotalDeposit = shift[0].total_deposit;
      this.checkedTotalCharge = shift[0].total_charge;
      this.checkedTotalGoods = shift[0].total_goods;
      this.checkedTotalPrice = this.checkedTotalPos + this.checkedTotalDeposit + this.checkedTotalGoods;

      this.setDiplayInfoReceipts();

    }
    if (shiftId === undefined) { this.isCollected = true; }
    swal({
      title: this.translate.instant('SWAL_WARN'),
      text: this.translate.instant('RECEIPT_WARN_UPDATE'),
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: this.translate.instant('SWAL_UPDATE'),
      cancelButtonText: this.translate.instant('SWAL_CANCEL')
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.apiShifts.managerShiftsUpdateCollected({
          shifts: collectData
        }).subscribe(
          data => {

            if (data) {
              this.showPrintPreview("any");
              this.spinner.hide();
            }
            this.isCollected = false;
            this.selectedReceipt = [];
            this.collected_tmp = [];
            this.receipts_tmp = [];
            this.timestamp_tmp = [];
            this.checkedTotalPos = 0;
            this.checkedTotalDeposit = 0;
            this.checkedTotalGoods = 0;
            this.checkedTotalPrice = this.checkedTotalPos + this.checkedTotalDeposit + this.checkedTotalGoods;
            //create history shift
            if (this.statusView == 1) this.searchReceipt();
            if (this.statusView == 2) {
              if (this.receiptForm.user_id) {
                this.searchDateOnly(1);
              } else { this.searchDateOnly(); }
            }
            if (this.statusView == 3) this.searchDateAll();
            swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
          }
        );
      } else {
        this.isCheckAll = false;
        this.isCollected = false;
        this.selectedReceipt = [];
        this.collected_tmp = [];
        this.receipts_tmp = [];
        this.timestamp_tmp = [];
      }
    });
  }

  //function save user to be select for shift by shift_id
  saveUserByShiftId(user_id: number, user_name: string) {

    if (!user_id) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('RECEIPT_ERROR_NOT_USER'), 'error');
      return;
    }

    if (this.driver) {
      this.update_shift.user_id = user_id;
      this.update_shift.subdriver_id = 0;
    } else {
      this.update_shift.user_id = 0;
      this.update_shift.subdriver_id = user_id;
    }

    this.driver_name = user_name;

    this.apiReport.updateShifts(this.update_shift).subscribe(data => {

      if (this.driver) {
        this.receipts[this.index_shift].driver_name = this.driver_name;
      } else {
        this.receipts[this.index_shift].subdriver_name = this.driver_name;
      }

      this.driver = true;
      this.index_shift = -1;
    });

    //show viewChild listUserByShiftByRole
    this.listUserByShiftByRole.hide();
  }

  setDiplayInfoReceipts() {

    let count_receipts_tmp = this.receipts_tmp.length - 1;
    if (this.receipts_tmp.length > 0) {
      this.payer_info = this.receipts_tmp[count_receipts_tmp].license_plates + ' - ' + this.receipts_tmp[count_receipts_tmp].driver_name + ' - ' + this.receipts_tmp[count_receipts_tmp].subdriver_name;
      this.route_name_info = this.receipts_tmp[count_receipts_tmp].route_name;
      this.station_name_info = this.receipts_tmp[count_receipts_tmp].from_station;
    }

    if (this.timestamp_tmp.length > 0) {

      let date_min = '';
      let date_max = '';

      this.timestamp_tmp.forEach(element => {
        let element_split = element.split(' <=> ');
        if (date_min === '' && date_max === '') {
          date_min = element_split[0];
          date_max = element_split[1];
        } else {

          let date_min_compare = new Date(this.funCutAndFormatDateByString(element_split[0])).getTime();
          let date_max_compare = new Date(this.funCutAndFormatDateByString(element_split[1])).getTime();

          if (date_min_compare < (new Date(this.funCutAndFormatDateByString(date_min)).getTime())) {
            date_min = element_split[0];
          }

          if (date_max_compare > (new Date(this.funCutAndFormatDateByString(date_max)).getTime())) {
            date_max = element_split[1];
          }
        }
      });

      this.date_started_ended = date_min + ' đến ' + date_max;
    }

    this.convert_number_pos = this.convertNumberToString(this.checkedTotalPos);

    this.convert_number_deposit = this.convertNumberToString(this.checkedTotalDeposit);

    this.convert_number_goods = this.convertNumberToString(this.checkedTotalGoods);

    this.convert_number_all = this.convertNumberToString(this.checkedTotalPrice);    
  }

  convertNumberToString(number){

    var num_parts = number.toLocaleString('vi-VN').split('.');
    num_parts = num_parts.reverse();

    var output = '';
    var phan_cach = ['ngàn', 'triệu', 'tỷ'];
    var so = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    var chuc = ['lẻ', 'mười', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    var don_vi = ['', 'một', 'hai', 'ba', 'bốn', 'lăm', 'sáu', 'bảy', 'tám', 'chín'];

    for (let index = 0; index < num_parts.length; index++) {

      var element = num_parts[index].split('').reverse().join('');
      var hang_don_vi = parseInt(element[0]);
      var hang_chuc = element.length > 1 ? parseInt(element[1]) : -1;
      var hang_tram = element.length > 2 ? parseInt(element[2]) : -1;
      var block = '';

      if (element == '000') {
        if (index >= 1) output = ' lẻ' +output;
        continue;
      }

      // hang tram
      if (hang_tram >= 0) {
          block += so[hang_tram] + ' trăm';
      }

      // hang chuc
      if (hang_chuc >= 0) {
          if (hang_chuc == 0) {
              if (hang_don_vi > 0) {
                  block += ' lẻ';
              }
          } else if (hang_chuc == 1) {
              block += ' ' + chuc[hang_chuc];
          } else {
              block += ' ' + chuc[hang_chuc] + ' mươi';
          }
      }

      // hang don vi
      if (hang_don_vi == 1) {
          if (hang_chuc > 1)
              block += ' mốt';
          else
              block += ' một';
      } else if (hang_don_vi == 5) {
          if (hang_chuc > 0)
              block += ' lăm';
          else
              block += ' năm';
      } else {
          block += ' ' + don_vi[hang_don_vi];
      }

      if (index > 0) {
          block += ' ' +phan_cach[(index - 1) % 3];
      }

      output = block + ' ' + output;
    }

    if(output !== ''){
      return output+ 'đồng';
      // return output.toLowerCase().substring(0,1).toUpperCase()+output.substring(1)+ 'đồng';
    }else{
      return 'không đồng';
    }
  }

  funCutAndFormatDateByString(strParam: string) {
    let str = strParam.split(" ");
    let str1 = str[0].split("-");
    let strResult = str1[2] + "-" + str1[1] + "-" + str1[0] + " " + str[1];
    return strResult;
  }


  //---------------------------
  // showViewAllReceiptCollectMoneyModal(){

  //   this.isLoading = true;
  //   this.ended = false;

  //   this.days = moment(this.receiptForm.date).format('DD').toString();
  //   this.months = moment(this.receiptForm.date).format('MM').toString();
  //   this.years = moment(this.receiptForm.date).format('YYYY').toString();

  //   this.refresh = 2;

  //   if (!this.receiptForm.date) {
  //     this.isFound = false;
  //     this.isLoading = false;
  //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('RECEIPT_ERROR_DATE'), 'warning');
  //     return;
  //   }

  //   this.apiReport.managerReportsViewNotCollectMoneyReceipt({
  //     date: moment(this.receiptForm.date).format('YYYY-MM-DD'),
  //   }).subscribe(resp =>{

  //     this.isNow = moment(this.receiptForm.date).format('YYYY-MM-DD')===moment(this.currentTime).format('YYYY-MM-DD')?true:false;
  //     if(resp.length > 0 && !resp[0].date_time){
  //         this.receipts = [];
  //         this.ended = true;
  //     } else {
  //         this.receipts = resp;
  //     }
  //     this.checkedTotalPrice = 0;
  //     this.laddaBtnDetail = new Array<boolean>(this.receipts.length);
  //     this.laddaBtnDetail.fill(false);
  //     this.isFound = true;
  //     this.isLoading = false;
  //   });
  // }

  // showViewAllReceiptModal(){

  //   this.isLoading = true;
  //   this.ended = false;

  //   this.days = moment(this.receiptForm.date).format('DD').toString();
  //   this.months = moment(this.receiptForm.date).format('MM').toString();
  //   this.years = moment(this.receiptForm.date).format('YYYY').toString();

  //   this.refresh = 3;

  //   if (!this.receiptForm.date) {
  //     this.isFound = false;
  //     this.isLoading = false;
  //     swal(this.translate.instant('SWAL_WARN'), this.translate.instant('RECEIPT_ERROR_DATE'), 'warning');
  //     return;
  //   }

  //   this.apiReport.managerReportsViewAllReceipt({
  //     date: moment(this.receiptForm.date).format('YYYY-MM-DD'),
  //   }).subscribe(resp =>{
  //     this.isNow = moment(this.receiptForm.date).format('YYYY-MM-DD')===moment(this.currentTime).format('YYYY-MM-DD')?true:false;
  //     if(resp.length > 0 && !resp[0].date_time){
  //         this.receipts = [];
  //         this.ended = true;
  //     } else {
  //         this.receipts = resp;
  //     }
  //     this.checkedTotalPrice = 0;
  //     this.laddaBtnDetail = new Array<boolean>(this.receipts.length);
  //     this.laddaBtnDetail.fill(false);
  //     this.isFound = true;
  //     this.isLoading = false;
  //   });
  // }

  showListVehicleModal() {
    this.getlistVehicleAll();
    this.listVehical.show();
  }

  chooseVehicle(vehicel_id) {

    if (vehicel_id == 0) {
      this.vehicle_id = vehicel_id;
      this.licensePlatesInput = this.translate.instant('BTN_VIEW_RECEIPT');
    } else {
      this.vehicle_Items.map(
        (vehicle) => {
          if (vehicle.id == vehicel_id) {
            this.licensePlatesInput = vehicle.license_plates;
            this.vehicle_id = vehicle.id;
          }
      });
    }
    this.listVehical.hide();
    this.searchReceipt();
  }

  getlistVehicleAll(){
    this.apiVehicles.getlistVehicleAllResponse().pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      res => {
        this.inputLicensePlates = '';
        this.vehicle_Items = res.body.filter(
          (vehicle) => {
            return vehicle;
        });
      }
    );
  }

  showInfoWarning(receipt) {
    this.modalWarning.show();
    this.info_warning = receipt;
  }
}
