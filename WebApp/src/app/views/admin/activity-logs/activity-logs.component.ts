import { Component, OnInit, ViewChild, AfterViewInit,ViewEncapsulation } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { AdminActivityLogsService, AdminCompaniesService} from '../../../api/services';
import { map } from 'rxjs/operators/map';
import * as moment from 'moment';
import { templateJitUrl } from '@angular/compiler';
@Component({
  selector: 'app-activity-logs',
  templateUrl: './activity-logs.component.html',
  styleUrls: ['./activity-logs.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class ActivityLogsComponent implements OnInit {

  @ViewChild('detailModal') public detailModal: ModalDirective;

  public activity_logs:any = [];
  public bsRangeValue: Date[];
  public canDelete = false;
  public companyItems: any = [];
  public detailActivityLogs:any = {};
  public isChecked = false;
  public isCheckedAll = false;
  public isLoading = false;
  public maxDate: Date;
  public selectedItems: any = [];
  public valueSelectedCompany: any = []

  public currentPage = 1;
  public limitPage = 10;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  constructor(
    private apiActivityLog: AdminActivityLogsService,
    private apiCompanies: AdminCompaniesService,
    private translate: TranslateService,
    private spinner: NgxSpinnerService
  ) {
    this.maxDate = new Date();
    this.valueSelectedCompany = [];
  }

  ngOnInit() {
  }

  ngAfterViewInit() {

    this.refreshView();
    this.apiCompanies.listCompanies({
      page: 0,
      limit: 9999
    }).subscribe(
      companies => {
        this.companyItems = [];
        for (let i = 0; i < companies.length; i++) {
          this.companyItems.push({
            id: companies[i].id,
            text: companies[i].name
          });
        }
      }
    );
  }

  refreshView() {

    this.spinner.show();

    this.apiActivityLog.listActivityLogResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {

        this.spinner.hide();

        this.activity_logs = resp.body;
        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
      }
    );

    this.canDelete = false;
    this.selectedItems = [];//Reset the selected checkboxes when changing the page
    this.isChecked = false;
    this.isCheckedAll = false;
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.refreshView();
  }

  refreshValueCompany(value:any):void {
    this.valueSelectedCompany = value;
  }

  public selectedCompany(value: any) {
    this.valueSelectedCompany = value;
    this.searchDataActivity();
  }

  public removedCompany(value: any) {

    if( this.valueSelectedCompany['id'] == value.id){
      this.valueSelectedCompany['id'] = null;
    }
    this.searchDataActivity();
  }

  searchDataActivity() {

    if(this.valueSelectedCompany || this.bsRangeValue){
      this.spinner.show();
      this.apiActivityLog.searchActivityLog({
        company_id: this.valueSelectedCompany ? this.valueSelectedCompany['id'] : null,
        to_date: this.bsRangeValue ? moment(this.bsRangeValue[1]).format('YYYY-MM-DD'): null ,
        from_date: this.bsRangeValue ? moment(this.bsRangeValue[0]).format('YYYY-MM-DD') : null,
      }).subscribe(res => {
        this.spinner.hide();
        this.activity_logs = res;
      });
    }

    if((this.valueSelectedCompany === undefined || this.valueSelectedCompany.length == 0) && (this.bsRangeValue === undefined || !this.bsRangeValue)){
      this.refreshView();
    }
  }

  deleteActivityLog(id: number) {
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
        this.apiActivityLog.deleteActivityLog(id).subscribe(
          res => {
            this.refreshView();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
          }
        );
      }
    });
  }

  deleteAllActivityLog() {
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
        let activity_arr = JSON.stringify(this.selectedItems);
        this.apiActivityLog.deleteActivityLogAll({
          activity_arr: activity_arr
        }).subscribe(
          res => {
            this.refreshView();
            setTimeout(() => {
              if(this.activity_logs.length == 0){
                if(this.currentPage > 0) this.currentPage -= 1;
                this.refreshView();
              }
            }, 200);
            this.isChecked = false;
            this.isCheckedAll = false;
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
          }
        );
      }
    });
  }

  showDetailActivityLogModal(id: number) {
    this.detailModal.show();
    this.spinner.show()
    this.apiActivityLog.getActivityLogById(id).subscribe(
      res => {
       this.detailActivityLogs = res;
       this.spinner.hide();
      }
    );
  }

  onActivityLogChanged(event: any, item: any) {
    const index: number = this.selectedItems.indexOf(item.id);

    if(event.target.checked) {
      if(index == -1) {
        this.selectedItems.push(item.id);
        this.canDelete = true;
      }
    }

    if(!event.target.checked) {
      if(index != -1) {
        this.selectedItems.splice(index, 1);
      }

      if(this.selectedItems.length == 0) {
        this.canDelete = false;
      }
    }
  }

  onActivityLogChangedAll() {
    this.isChecked = !this.isChecked;

    if(this.isChecked) {
      this.isCheckedAll = true;
      this.canDelete = true;
      this.activity_logs.forEach(e => {
        const index: number = this.selectedItems.indexOf(e.id);
        if(index == -1) {
          this.selectedItems.push(e.id);
        }
      });
    }
    else {
      this.selectedItems = [];
      this.canDelete = false;
    }
  }

}
