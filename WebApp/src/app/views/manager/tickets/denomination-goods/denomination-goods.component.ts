import { Component, OnInit, ViewChild, AfterViewInit, ViewEncapsulation } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { ManagerDenominationsService, ManagerModuleCompanyService } from '../../../../api/services';
import { Denomination } from '../../../../api/models';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import swal from 'sweetalert2';
import { HttpErrorResponse } from '@angular/common/http';
import { ActivityLogsService } from '../../../../shared/activity-logs.service';

@Component({
  selector: 'app-denomination-goods',
  templateUrl: './denomination-goods.component.html',
  styleUrls: ['./denomination-goods.component.css']
})
export class DenominationGoodsComponent implements OnInit, AfterViewInit {

  @ViewChild('addPriceModel') public addPriceModel: ModalDirective;

  public denominationGoodForm: Denomination;
  public denominations: any = [];
  public permissions: any;
  public numberPatten =  /^[-+]?(\d+|\d+\.\d*|\d*\.\d+)$/;
  public user_down: any = null;
  public style: any;
  public isModuleGoods = false;

  constructor(
    private apiDenomination: ManagerDenominationsService,
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
    private activityLogs: ActivityLogsService,
    private apiModuleCompanies: ManagerModuleCompanyService,
  ) {
    this.denominationGoodForm =  new Denomination;
  }

  ngOnInit() {

    this.user_down = localStorage.getItem('token_shadow');

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.getDenominations();
    this.apiModuleCompanies.listModuleCompany().subscribe(data => {
      data.forEach(element => {
        if(element['name'] === 'Module_Xe_Khach' ){
          this.isModuleGoods = true;
        }
      });
    })
  }

  showAddPriceModel() {
    this.addPriceModel.show();
    this.denominationGoodForm = new Denomination;
  }

  getDenominations() {
    this.spinner.show();
    this.apiDenomination.managerListDenomination('goods').subscribe(
      res => {
        this.denominations = res;
          this.spinner.hide();
      }
    );
  }

  addDenomination() {

    if(this.denominationGoodForm.price){
      if (!this.numberPatten.test(this.denominationGoodForm.price.toString())) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_FORMAT_DENOMINATION'), 'warning');
        return;
      }
    }

    if(!this.denominationGoodForm.price){
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_DENOMINATION'), 'warning');
      return;
    }

    this.spinner.show();
    this.apiDenomination.managerCreateDenomination({
      price: this.denominationGoodForm.price,
      type: 'goods',
      color: this.denominationGoodForm.color ? this.denominationGoodForm.color : null
    }).subscribe(
      res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'create';
        activity_log['subject_type'] = 'denomination-goods';
        activity_log['subject_data'] =JSON.stringify({
          price: this.denominationGoodForm.price,
          type: 'goods'
        });
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.spinner.hide();
        this.addPriceModel.hide();
        this.denominationGoodForm =  new Denomination();
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
            activity_log['subject_type'] = 'denomination-goods';
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
