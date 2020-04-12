import { Component, OnInit, ViewChild, AfterViewInit ,ViewEncapsulation} from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { ManagerTicketTypesService, ManagerDevicesService,  } from '../../../../api/services';
import { TicketType, TicketTypeForm, TicketAllocateForm } from '../../../../api/models';
import { HttpErrorResponse } from '@angular/common/http';
import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-ticket-providers',
  templateUrl: './ticket-providers.component.html',
  styleUrls: ['./ticket-providers.component.css'],
  encapsulation: ViewEncapsulation.None
})

export class TicketProvidersComponent implements OnInit {

  public ticketTypes: TicketType[];
  public ticket_allocates = [];
  public ticketAllocateForm: TicketAllocateForm;
  public statusView = 0;
  public permissions:any[] = [];
  public ticketPriceItems: Array<any> = [];
  public deviceItems: Array<any> = [];
  public bsRangeValue: Date[];
  public maxDate: Date;
  public isLoading = false;

  public valueSelectedDevice: Array<any> = [];
  public valueSelectedPrice: Array<any> = [];

  public codeRemovedDevice: Array<any> = [];
  public codeRemovedPrice: Array<any> = [];

  constructor(
    private apiTicketTypes: ManagerTicketTypesService,
    private apiDevices: ManagerDevicesService,
    private translate: TranslateService,
    private spinner: NgxSpinnerService
  ) {
    this.maxDate = new Date();
    this.ticketAllocateForm = new TicketAllocateForm();
    this.valueSelectedDevice = [];
    this.valueSelectedPrice = [];
  }

  ngOnInit() {
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.statusView = 0;
    this.refreshView();
  }

  refreshView() {

    this.spinner.show();
    this.apiTicketTypes.managerlistTicketAllocates().subscribe(
      resp => {
        this.ticketTypes = resp;
        this.spinner.hide();
      }
    );
  }

  refreshValueDevice(value:any):void {
    this.valueSelectedDevice = value;
  }

  refreshValuePrice(value:any):void {
    this.valueSelectedPrice = value;
  }

  public selectedDevice(value: any) {
    this.valueSelectedDevice = value;
    this.getDataTickeAllocate();
  }
  public selectedPrice(value: any) {
    this.valueSelectedPrice = value;
    this.getDataTickeAllocate();
  }

  public removedDevice(value: any){

    if( this.valueSelectedDevice['id'] == value.id){
      this.valueSelectedDevice['id'] = null;
    }
    this.getDataTickeAllocate();
  }

  public removedPrice(value: any){

    if( this.valueSelectedPrice['id'] == value.id){
      this.valueSelectedPrice['id'] = null;
    }
    this.getDataTickeAllocate();
  }

  getDataTickeAllocate(): void{

    this.isLoading = true;
    this.spinner.show();

    // console.log(this.bsRangeValue, this.valueSelectedDevice.id, this.valueSelectedPrice.id);
    this.apiTicketTypes.managerlistTicketAllocateSearchs({
      to_date: this.bsRangeValue ? moment(this.bsRangeValue[1]).format('YYYY-MM-DD'): null ,
      from_date: this.bsRangeValue ? moment(this.bsRangeValue[0]).format('YYYY-MM-DD') : null,
      ticket_type_id: this.valueSelectedPrice ? this.valueSelectedPrice['id'] : null,
      device_id: this.valueSelectedDevice ? this.valueSelectedDevice['id'] : null,
    }).subscribe(data => {
      this.isLoading = false;
      this.ticket_allocates = data;
      this.spinner.hide();

    });
  }

  searchStatusTicketProvides(statusView: number){

    this.isLoading = true;

    this.statusView = statusView;
    this.valueSelectedDevice = [];
    this.valueSelectedPrice = [];
    this.spinner.show();

    this.apiTicketTypes.managerlistTicketTypes({
      page: 1,
      limit: 999999999
    }).subscribe(
      resp => {
        this.ticketPriceItems = [];
        for (let i = 0; i < resp.length; i++) {
          this.ticketPriceItems.push({
            id: resp[i].id,
            text: resp[i].ticket_prices[resp[i].ticket_prices.length - 1].price + '('+resp[i].order_code+' - '+((resp[i].type == 0) ? "Vé lượt":"Vé tháng")+')'
          });
        }
      }
    );

    this.apiDevices.managerListDevices({
      page: 1,
      limit: 9999999999
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {
        this.deviceItems = [];
        for (let i = 0; i < resp.length; i++) {
          this.deviceItems.push({
            id: resp[i].id,
            text: resp[i].identity
          });
        }
        this.spinner.hide();
      }
    );
  }

  callBack(){
    this.statusView = 0;
    this.refreshView();
  }
}
