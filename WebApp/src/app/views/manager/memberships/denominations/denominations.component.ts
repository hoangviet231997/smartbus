import { Component, OnInit, ViewChild, AfterViewInit, ViewEncapsulation } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { Denomination } from '../../../../api/models';
import { ManagerRfidcardService , ManagerDenominationsService} from '../../../../api/services';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import swal from 'sweetalert2';
import { HttpErrorResponse } from '@angular/common/http';
import { ActivityLogsService } from '../../../../shared/activity-logs.service';

@Component({
  selector: 'app-denominations',
  templateUrl: './denominations.component.html',
  styleUrls: ['./denominations.component.css'],
  encapsulation:ViewEncapsulation.None,
})
export class DenominationsComponent implements OnInit, AfterViewInit {

  @ViewChild('addPriceModel') public addPriceModel: ModalDirective;

  public priceModel: Denomination;
  public denominations: any = [];
  public permissions: any;
  public numberPatten =  /^[-+]?(\d+|\d+\.\d*|\d*\.\d+)$/;
  public user_down: any = null;

  constructor(
    private managerCardsService: ManagerRfidcardService,
    private apiDenomination: ManagerDenominationsService,
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
    private activityLogs: ActivityLogsService
  ) {
    this.priceModel =  new Denomination;
  }

  ngOnInit() {

    this.user_down = localStorage.getItem('token_shadow');

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.getDenominations();
  }

  showAddPriceModel() {
    this.addPriceModel.show();
  }

  getDenominations() {
    this.spinner.show();
    this.apiDenomination.managerListDenomination('nfc').subscribe(
      res => {
        this.denominations = res;
        this.spinner.hide(); 
      }
    );
  }

  addDenomination() {

    if(this.priceModel.price){
      if (!this.numberPatten.test(this.priceModel.price.toString())) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_FORMAT_DENOMINATION'), 'warning');
        return;
      }
    }

    if(!this.priceModel.price){
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_DENOMINATION'), 'warning');
      return;
    }

    this.spinner.show();
    this.apiDenomination.managerCreateDenomination({
      price: this.priceModel.price,
      type: 'nfc'
    }).subscribe(
      res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'create';
        activity_log['subject_type'] = 'denomination-prepaid';
        activity_log['subject_data'] =JSON.stringify({
          price: this.priceModel.price,
          type: 'nfc'
        });
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.spinner.hide();
        this.addPriceModel.hide();
        this.priceModel =  new Denomination();
        this.getDenominations();
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      },
      err => {
        this.spinner.hide();
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD')});
          }
        }
      }
    );
  }

  deleteDenomination(id: number) {
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
        this.apiDenomination.managerDeleteDenominationById(id).subscribe(
          res => {

            //call service create activity log
            var activity_log: any = [];
            activity_log['user_down'] =  this.user_down ? this.user_down : null;
            activity_log['action'] = 'delete';
            activity_log['subject_type'] = 'denomination-prepaid';
            activity_log['subject_data'] =JSON.stringify({
              id: id
            });
            var activityLog = this.activityLogs.createActivityLog(activity_log);

            this.getDenominations();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
          }
        );
      }
    });
  }
}
