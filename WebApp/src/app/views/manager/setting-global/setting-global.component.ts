import { Component, OnInit } from '@angular/core';
import { ManagerSettingGlobalService} from '../../../api/services';
import { SettingGlobal} from '../../../api/models';
import { NgxSpinnerService } from 'ngx-spinner';
import { TranslateService } from '@ngx-translate/core';
import swal from 'sweetalert2';
import { HttpErrorResponse } from '@angular/common/http';
import { ActivityLogsService } from '../../../shared/activity-logs.service';

@Component({
  selector: 'app-setting-global',
  templateUrl: './setting-global.component.html',
  styleUrls: ['./setting-global.component.css']
})
export class SettingGlobalComponent implements OnInit {

  public settingGlobals: any[] = [];
  public settingGlobalCreated: SettingGlobal;

  public isAddSettingGlobal = false;
  public user_down = null;

  constructor(
    private apiSettingGlobal: ManagerSettingGlobalService,
    private spinner: NgxSpinnerService,
    private translate: TranslateService,
    private activityLogs: ActivityLogsService
  ) {
    this.settingGlobalCreated = new SettingGlobal();
  }

  ngOnInit() {
    this.user_down = localStorage.getItem('token_shadow');
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView() {
    this.spinner.show();
    this.apiSettingGlobal.managerGetSettingGlobal().subscribe(data => {
      this.settingGlobals = data;
      this.spinner.hide();
    });
  }

  showAddSettingGlobal(){

    if(this.isAddSettingGlobal) this.isAddSettingGlobal = false;
    else this.isAddSettingGlobal = true;

    this.settingGlobalCreated.key = '';
    this.settingGlobalCreated.value = '';
  }

  saveSettingGlobal(){

    if (this.settingGlobalCreated.key == '') {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_SETTING_GLOBAL_KEY'), 'warning');
      return;
    }

    if (this.settingGlobalCreated.value == '') {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_SETTING_GLOBAL_VALUE'), 'warning');
      return;
    }

    this.spinner.show();
    this.apiSettingGlobal.createSettingGlobal({
      key: this.settingGlobalCreated.key,
      value: this.settingGlobalCreated.value
    }).subscribe(data => {

      //call service create activity log
      var activity_log: any = [];
      activity_log['user_down'] =  this.user_down ? this.user_down : null;
      activity_log['action'] = 'create';
      activity_log['subject_type'] = 'setting_global';
      activity_log['subject_data'] = JSON.stringify({
        key: this.settingGlobalCreated.key,
        value: this.settingGlobalCreated.value
      });
      var activityLog = this.activityLogs.createActivityLog(activity_log);

      this.refreshView();
      this.isAddSettingGlobal = false;
      this.spinner.hide();

    },err => {
      if (err instanceof HttpErrorResponse) {
        if (err.status === 404) {
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
        }
        this.spinner.hide();
      }
    }
    );
  }

  removeSettingGlobal(key, value){

    if(this.settingGlobals.length  == 1){
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_SETTING_GLOBAL_ARRAY_LIMIT_1'), 'warning');
      return;
    }

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
        this.apiSettingGlobal.deleteSettingGlobal({key,value}).subscribe(data => {

          //call service create activity log
          var activity_log: any = [];
          activity_log['user_down'] =  this.user_down ? this.user_down : null;
          activity_log['action'] = 'delete';
          activity_log['subject_type'] = 'setting_global';
          activity_log['subject_data'] = JSON.stringify({key,value});
          var activityLog = this.activityLogs.createActivityLog(activity_log);

          this.refreshView();
          this.isAddSettingGlobal = false;
          this.spinner.hide();
        },err => {
            this.spinner.hide();
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD') });
          }
        );
      }
    });
  }
}
