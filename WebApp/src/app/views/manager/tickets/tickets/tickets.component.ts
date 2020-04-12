import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { ManagerTicketTypesService, ManagerModuleCompanyService } from '../../../../api/services';
import { TicketType, TicketTypeForm } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';
import { map } from 'rxjs/operators/map';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivityLogsService } from '../../../../shared/activity-logs.service';

@Component({
  selector: 'app-tickets',
  templateUrl: './tickets.component.html',
  styleUrls: ['./tickets.component.css']
})
export class TicketsComponent implements OnInit, AfterViewInit {
  registerForm: FormGroup;
  submitted = false;


  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public ticketTypes: TicketType[];
  public ticket: TicketType;
  public ticketCreate: TicketTypeForm;
  public language = 'vn';
  public ticketUpdate: TicketTypeForm;
  public type = 'usedOnce';
  public isCreated = false;
  public isUpdated = false;

  public user_down: any = null;

  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;
  public permissions: any[] = [];
  public numberPatten = /^-?[\d.]+(?:e-?\d+)?$/;

  public key_input: any = '';
  public style_search: any = '';

  public timeoutSearchTicket;


  // public isModuleCardMonthKm = false;
  public isModuleCardMonthChargeLimit = false;

  constructor(
    private apiTicketTypes: ManagerTicketTypesService,
    private translate: TranslateService,
    private apiModuleCompanies: ManagerModuleCompanyService,
    private spinner: NgxSpinnerService,
    private activityLogs: ActivityLogsService
  ) {
    this.ticket = new TicketType();
    this.ticketCreate = new TicketTypeForm();
    this.ticketUpdate = new TicketTypeForm();
    this.ticketCreate.type = 0;
  }

  ngOnInit() {
    this.user_down = localStorage.getItem('token_shadow');
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.key_input = '';
    this.style_search = '';
    this.refreshView();

    //get module company
    this.apiModuleCompanies.listModuleCompany().subscribe(data => {
      data.forEach(element => {
        // if(element['name'] === 'Module_TT_Km' ){
        //   this.isModuleCardMonthKm = true;
        // }
        if (element['name'] === 'Module_TT_SL_Quet') {
          this.isModuleCardMonthChargeLimit = true;
        }
      });
    })
  }

  refreshView() {
    this.spinner.show();
    this.apiTicketTypes.managerlistTicketTypesResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {
        this.ticketTypes = resp.body;
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

  showAddTicketModal() {

    this.type = 'usedOnce';
    this.ticketCreate.type = 0;
    this.addModal.show();
  }

  showEditTicketModal(id: number) {

    this.spinner.show();
    this.apiTicketTypes.managerGetTicketTypeById(id).subscribe(
      data => {
        this.spinner.hide();
        this.ticketUpdate.id = data.id;
        this.ticketUpdate.name = data.name;
        this.ticketUpdate.price = data.ticket_prices[data.ticket_prices.length - 1].price;
        this.ticketUpdate.order_code = data.order_code;
        this.ticketUpdate.sign = data.sign;
        this.ticketUpdate.sign_form = data.sign_form;
        this.ticketUpdate.description = data.description;
        this.ticketUpdate.duration = data.duration / 3600;
        this.ticketUpdate.number_km = data.number_km ? data.number_km / 1000 : null;
        this.ticketUpdate.sale_of = data.sale_of;
        this.language = data.language;
        this.language = data.language;
        this.ticketUpdate.type = data.type;
        this.ticketUpdate.charge_limit = data.ticket_prices[data.ticket_prices.length - 1].charge_limit;
        this.ticketUpdate.limit_number = data.ticket_prices[data.ticket_prices.length - 1].limit_number ? data.ticket_prices[data.ticket_prices.length - 1].limit_number : null;
        if (data.duration > 0) {
          this.type = 'limit';
        } else {
          this.type = 'usedOnce';
        }
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
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

  addTicket() {

    if (!this.ticketCreate.name) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }

    if (this.ticketCreate.price < 5000 || this.ticketCreate.price > 1000000) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_PRICE'), 'warning');
      return;
    }
    if (this.ticketCreate.price === undefined) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_PRICE_EMPTY'), 'warning');
      return;
    }

    if (this.type === 'limit') {

      if (this.ticketCreate.duration === undefined) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TIME_TICKET'), 'warning');
        return;
      }

      if (this.ticketCreate.duration < 0) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TIME_TICKET'), 'warning');
        return;
      }
    } else {
      this.ticketCreate.duration = 0;
    }

    if (this.ticketCreate.type == 0) {

      if (this.ticketCreate.number_km) {

        if (!this.numberPatten.test(this.ticketCreate.number_km.toString())) {
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_KM_FORMAT'), 'warning');
          return;
        }
        if (this.ticketCreate.number_km < 0) {
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_KM_FORMAT'), 'warning');
          return;
        }
      }

      if (this.ticketCreate.sale_of) {

        if (!this.numberPatten.test(this.ticketCreate.sale_of.toString())) {
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_SALE_OF_TKT_FORMAT'), 'warning');
          return;
        }
        if (this.ticketCreate.sale_of < 0 || this.ticketCreate.sale_of > 100) {
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_SALE_OF_TKT_FORMAT'), 'warning');
          return;
        }
      }
    }

    if (this.ticketCreate.limit_number) {

      if (!this.numberPatten.test(this.ticketCreate.limit_number.toString())) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_LIMIT_NUMBER_TKT_FORMAT'), 'warning');
        return;
      }
      if (this.ticketCreate.limit_number < 0) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_LIMIT_NUMBER_TKT_FORMAT'), 'warning');
        return;
      }
    }

    this.isCreated = true;
    this.apiTicketTypes.managerCreateTicketType({
      name: this.ticketCreate.name,
      price: this.ticketCreate.price,
      order_code: this.ticketCreate.order_code,
      sign: this.ticketCreate.sign,
      sign_form: '',
      description: this.ticketCreate.description,
      duration: this.ticketCreate.duration,
      number_km: this.ticketCreate.number_km ? this.ticketCreate.number_km : null,
      limit_number: this.ticketCreate.limit_number ? this.ticketCreate.limit_number : null,
      sale_of: this.ticketCreate.sale_of ? this.ticketCreate.sale_of : null,
      language: this.language,
      type: this.ticketCreate.type,
      charge_limit: this.ticketCreate.charge_limit ? this.ticketCreate.charge_limit : null,
    }).subscribe(
      res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'create';
        activity_log['subject_type'] = 'ticket';
        activity_log['subject_data'] = this.ticketCreate ? JSON.stringify({
          name: this.ticketCreate.name,
          price: this.ticketCreate.price,
          order_code: this.ticketCreate.order_code,
          sign: this.ticketCreate.sign,
          sign_form: '',
          description: this.ticketCreate.description,
          duration: this.ticketCreate.duration,
          number_km: this.ticketCreate.number_km ? this.ticketCreate.number_km : null,
          limit_number: this.ticketCreate.limit_number ? this.ticketCreate.limit_number : null,
          sale_of: this.ticketCreate.sale_of ? this.ticketCreate.sale_of : null,
          language: this.language,
          type: this.ticketCreate.type,
          charge_limit: this.ticketCreate.charge_limit ? this.ticketCreate.charge_limit : null,
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.addModal.hide();
        this.ticketCreate = new TicketTypeForm();
        this.isCreated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          } else if (err.status === 422) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD') });
          }
        }
        this.isCreated = false;
      }
    );
  }

  editTicket() {

    if (!this.ticketUpdate.name) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }

    if (this.ticketUpdate.price < 0) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_PRICE'), 'warning');
      return;
    }

    if (this.type === 'limit') {

      if (this.ticketUpdate.duration === undefined) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TIME_TICKET'), 'warning');
        return;
      }

      if (this.ticketUpdate.duration < 0) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TIME_TICKET'), 'warning');
        return;
      }
    } else {
      this.ticketUpdate.duration = 0;
    }

    if (this.ticketUpdate.number_km) {

      if (!this.numberPatten.test(this.ticketUpdate.number_km.toString())) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_KM_FORMAT'), 'warning');
        return;
      }
      if (this.ticketUpdate.number_km < 0) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_KM_FORMAT'), 'warning');
        return;
      }
    }

    if (this.ticketUpdate.limit_number) {

      if (!this.numberPatten.test(this.ticketUpdate.limit_number.toString())) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_LIMIT_NUMBER_TKT_FORMAT'), 'warning');
        return;
      }
      if (this.ticketUpdate.limit_number < 0) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_LIMIT_NUMBER_TKT_FORMAT'), 'warning');
        return;
      }
    }

    if (this.ticketUpdate.sale_of) {

      if (!this.numberPatten.test(this.ticketUpdate.sale_of.toString())) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_SALE_OF_TKT_FORMAT'), 'warning');
        return;
      }
      if (this.ticketUpdate.sale_of < 0 || this.ticketUpdate.sale_of > 100) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_SALE_OF_TKT_FORMAT'), 'warning');
        return;
      }
    }

    this.isUpdated = true;
    this.apiTicketTypes.managerUpdateTicketType({
      id: this.ticketUpdate.id,
      name: this.ticketUpdate.name,
      price: this.ticketUpdate.price,
      order_code: this.ticketUpdate.order_code,
      sign: this.ticketUpdate.sign,
      sign_form: this.ticketUpdate.sign_form,
      description: this.ticketUpdate.description,
      duration: this.ticketUpdate.duration,
      number_km: this.ticketUpdate.number_km ? this.ticketUpdate.number_km : null,
      limit_number: this.ticketUpdate.limit_number ? this.ticketUpdate.limit_number : null,
      sale_of: this.ticketUpdate.sale_of ? this.ticketUpdate.sale_of : null,
      language: this.language,
      type: this.ticketUpdate.type,
      charge_limit: this.ticketUpdate.charge_limit ? this.ticketUpdate.charge_limit : null,
    }).subscribe(
      data => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'update';
        activity_log['subject_type'] = 'ticket';
        activity_log['subject_data'] = this.ticketUpdate ? JSON.stringify({
          id: this.ticketUpdate.id,
          name: this.ticketUpdate.name,
          price: this.ticketUpdate.price,
          order_code: this.ticketUpdate.order_code,
          sign: this.ticketUpdate.sign,
          sign_form: this.ticketUpdate.sign_form,
          description: this.ticketUpdate.description,
          duration: this.ticketUpdate.duration,
          number_km: this.ticketUpdate.number_km ? this.ticketUpdate.number_km : null,
          limit_number: this.ticketUpdate.limit_number ? this.ticketUpdate.limit_number : null,
          sale_of: this.ticketUpdate.sale_of ? this.ticketUpdate.sale_of : null,
          language: this.language,
          type: this.ticketUpdate.type,
          charge_limit: this.ticketUpdate.charge_limit ? this.ticketUpdate.charge_limit : null,
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.editModal.hide();
        this.ticketUpdate = new TicketTypeForm();
        this.isUpdated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          } else if (err.status === 422) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD') });
          }
        }
        this.isUpdated = false;
      }
    );
  }

  deleteTicket(id: number) {
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
        this.spinner.show();
        this.apiTicketTypes.managerDeleteticketType(id).subscribe(
          res => {

            //call service create activity log
            var activity_log: any = [];
            activity_log['user_down'] =  this.user_down ? this.user_down : null;
            activity_log['action'] = 'delete';
            activity_log['subject_type'] = 'ticket';
            activity_log['subject_data'] = JSON.stringify({id:id});
            var activityLog = this.activityLogs.createActivityLog(activity_log);

            this.refreshView();
            this.spinner.hide();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            this.spinner.hide();
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD') });
          }
        );
      }
    });
  }

  getInputTicket() {

    clearTimeout(this.timeoutSearchTicket);
    this.timeoutSearchTicket = setTimeout(() => {
      if (this.key_input !== '') {
        this.apiTicketTypes.managerSearchTicketTypesByKeyWord({
          style_search: this.style_search,
          key_input: this.key_input
        }).subscribe((res) => {
          this.ticketTypes = res;
        });
      } else {
        this.refreshView();
      }
    }, 500);
  }
}
