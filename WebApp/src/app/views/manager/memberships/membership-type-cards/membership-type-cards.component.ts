import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import {MembershipTypeForm} from '../../../../api/models';
import {ManagerMembershiptypesService} from '../../../../api/services';
import swal from 'sweetalert2';
import { HttpErrorResponse } from '@angular/common/http';
import { map } from 'rxjs/operators/map';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivityLogsService } from '../../../../shared/activity-logs.service';

@Component({
  selector: 'app-membership-type-cards',
  templateUrl: './membership-type-cards.component.html',
  styleUrls: ['./membership-type-cards.component.css']
})
export class MembershipTypeCardsComponent implements OnInit {


  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public isCreated = false;
  public isUpdated = false;

  public type: any;
  public membershipTypeCreate: MembershipTypeForm;
  public membershipTypeUpdate: MembershipTypeForm;
  public membershpTypes: any = [];

  public user_down: any= null;
  public permissions:any[] = [];

  constructor(
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
    private apiMembershipType: ManagerMembershiptypesService,
    private activityLogs: ActivityLogsService
  ) {
    this.membershipTypeCreate = new MembershipTypeForm();
    this.membershipTypeUpdate = new MembershipTypeForm();
   }

  ngOnInit() {

    this.user_down = localStorage.getItem('token_shadow');
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView(){
    this.spinner.show(); 
    this.apiMembershipType.managerListMembershipTypes().subscribe(data=>{
      this.membershpTypes = data;
      this.spinner.hide(); 
    });
  }

  showAddMembershipTypeModal() {
    this.addModal.show();
  }

  showEditMembershipTypeModal(id: number){

    if(!id){
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEMBER_ID'), 'warning');
      return;
    }

    this.spinner.show();
    this.apiMembershipType.managerGetMembershipTypeById(id).subscribe(
      data => {

        this.membershipTypeUpdate.id = data.id;
        this.membershipTypeUpdate.name = data.name;
        this.membershipTypeUpdate.deduction = data.deduction;
        if(data.code == 0)this.type = 'prepaidcard';
        if(data.code == 1)this.type = 'monthcard';
        this.spinner.hide();
        this.editModal.show();
      },
      err => {
        this.spinner.hide();
      }
    );
  }

  addMembershipType(){

    if (!this.membershipTypeCreate.name) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_NAME'), 'warning');
      return;
    }

    if (this.membershipTypeCreate.deduction < 0 || this.membershipTypeCreate.deduction > 100) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_DEDUCTIYON_LENGHT'), 'warning');
      return;
    }
    if ( this.membershipTypeCreate.deduction === undefined) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_DEDUCTIYON'), 'warning');
      return;
    }
    if ( !this.type) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_CARD_TPE'), 'warning');
      return;
    }

    if(this.type === 'prepaidcard'){this.membershipTypeCreate.code = 0}
    if(this.type === 'monthcard'){this.membershipTypeCreate.code = 1}
    this.isCreated = true;

    this.apiMembershipType.manmagerCreateMembershipType({
      name: this.membershipTypeCreate.name,
      deduction: this.membershipTypeCreate.deduction,
      code: this.membershipTypeCreate.code
    }).subscribe(res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'create';
        activity_log['subject_type'] = 'membership_type';
        activity_log['subject_data'] = this.membershipTypeCreate ? JSON.stringify({
          name: this.membershipTypeCreate.name,
          deduction: this.membershipTypeCreate.deduction,
          code: this.membershipTypeCreate.code
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.addModal.hide();
        this.membershipTypeCreate = new MembershipTypeForm();
        this.isCreated = false;
        this.refreshView();
        swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      },
      err => {
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_CREATE_FAILD')});
          }
        }
        this.isCreated = false;
      }
    );
  }

  editMembershipType(){

    if (!this.membershipTypeUpdate.name) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_NAME'), 'warning');
      return;
    }

    if (this.membershipTypeUpdate.deduction < 0 || this.membershipTypeUpdate.deduction > 100) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_DEDUCTIYON_LENGHT'), 'warning');
      return;
    }
    if ( this.membershipTypeUpdate.deduction === undefined) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_DEDUCTIYON'), 'warning');
      return;
    }
    if ( !this.type) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_CARD_TPE'), 'warning');
      return;
    }


    let key_code = null;
    if(this.type == "prepaidcard"){ key_code = 0};
    if(this.type == "monthcard"){ key_code = 1};
    this.isUpdated = true;

    this.apiMembershipType.managerUpdateMembershipType({
      id: this.membershipTypeUpdate.id,
      name: this.membershipTypeUpdate.name,
      deduction: this.membershipTypeUpdate.deduction,
      code: key_code,
    }).subscribe(data =>{

      //call service create activity log
      var activity_log: any = [];
      activity_log['user_down'] =  this.user_down ? this.user_down : null;
      activity_log['action'] = 'update';
      activity_log['subject_type'] = 'membership_type';
      activity_log['subject_data'] = this.membershipTypeUpdate ? JSON.stringify({
        id: this.membershipTypeUpdate.id,
        name: this.membershipTypeUpdate.name,
        deduction: this.membershipTypeUpdate.deduction,
        code: key_code,
      }) : '';
      var activityLog = this.activityLogs.createActivityLog(activity_log);

      this.editModal.hide();
      this.membershipTypeUpdate = new MembershipTypeForm();
      this.isUpdated = false;
      this.refreshView();
      swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
    },
    err => {
      if (err instanceof HttpErrorResponse) {
        if (err.status === 404) {
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
        } else if (err.status === 422) {
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
        }
      }
      this.isUpdated = false;
    });
  }

  // deleteMembershipType(id:number){

  //   if(!id){
  //     swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEMBER_ID'), 'warning');
  //     return;
  //   }

  //   swal({
  //       title: this.translate.instant('SWAL_ERROR_SURE'),
  //       text: this.translate.instant('SWAL_ERROR_REMOVE'),
  //       type: 'warning',
  //       showCancelButton: true,
  //       confirmButtonColor: '#3085d6',
  //       cancelButtonColor: '#d33',
  //       confirmButtonText: this.translate.instant('SWAL_OK'),
  //       cancelButtonText: this.translate.instant('SWAL_CANCEL')
  //     }).then((result) => {
  //       if (result.value) {
  //         if(id){
  //           this.spinner.show();
  //           this.apiMembershipType.managerDeleteMembershipType(id).subscribe(
  //             res => {
  //               this.refreshView();
  //               this.spinner.hide();
  //               swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
  //             },
  //             err => {
  //               this.spinner.hide();
  //               swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
  //             }
  //           );
  //         }
  //       }
  //     });
  // }

}
