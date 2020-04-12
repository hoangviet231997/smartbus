import { Component, OnInit ,ViewChild, AfterViewInit } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap';
import swal from 'sweetalert2';
import { Router } from '@angular/router';
import { HttpErrorResponse} from '@angular/common/http';
import { MouseEvent } from '@agm/core';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { GroupKeyCompany,GroupKeyCompanyCreate,GroupKeyCompanyUpdate} from '../../../api/models';
import { AdminCompaniesService,AdminGroupKeyService,ManagerCompaniesService } from '../../../api/services';
import { empty } from 'rxjs';

@Component({
  selector: 'app-group-companies',
  templateUrl: './group-companies.component.html',
  styleUrls: ['./group-companies.component.css']
})
export class GroupCompaniesComponent implements OnInit{

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public companies: any = [];
  public companiesNotArr: any = [];
  public groupKeys: any = [];
  public arrCompanyIdCreate: any = [];
  public arrCompanyIdUpdate: any = [];
  public groupCompanyCreate: GroupKeyCompanyCreate;
  public groupCompanyUpdate: GroupKeyCompanyUpdate;

  constructor( 
    private apiCompanies: AdminCompaniesService,
    private apiCompaniesNotArr: ManagerCompaniesService,
    private apiGroupKeyCompanies: AdminGroupKeyService,
    private translate: TranslateService
  ){
    this.groupCompanyCreate = new GroupKeyCompanyCreate();
    this.groupCompanyUpdate = new GroupKeyCompanyUpdate();
  }

  ngOnInit() {
  }


  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView(){
    
    this.apiGroupKeyCompanies.listGroupKeyCompanies().subscribe(data => {
      this.groupKeys = data;
    });

    this.apiCompaniesNotArr.managerGetCompanyByNotArray().subscribe(data => {
      this.companiesNotArr = data;
    });
  }
  
  showAddGroupCompaniesModal(){
    this.addModal.show();
    this.groupCompanyCreate = new GroupKeyCompanyCreate();
  }

  showEditGroupCompaniesModal(id: number){

    this.arrCompanyIdUpdate = [];
    this.apiGroupKeyCompanies.getGroupKeyCompaniesById(id).subscribe(
      data => {
        this.groupCompanyUpdate.id = data['id'];
        this.groupCompanyUpdate.name = data['name'];
        this.groupCompanyUpdate.type = (data['type'] !== null) ? data['type'] : null;
        this.groupCompanyUpdate.key = data['key'];
        this.arrCompanyIdUpdate = data['companies'];
        this.companies = data['companies_tmp'];
        this.editModal.show();
      },
      err => {
        swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_ERROR_MODEL')});
      }
    );
  }

  addGroupCompanies(){
    //check name group
    if (!this.groupCompanyCreate.name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_NAME'), 'warning');
      return;
    }

    if (this.groupCompanyCreate.type == null) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_GROUP_KEY_TYPE'), 'warning');
      return;
    }

    this.apiGroupKeyCompanies.createGroupKeyCompanies({
      name: this.groupCompanyCreate.name,
      companies: this.arrCompanyIdCreate,
      type: this.groupCompanyCreate.type ? this.groupCompanyCreate.type : null
    }).subscribe(data => {
      this.addModal.hide();
      this.refreshView();
      swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
    });
  }

  editGroupCompanies(){
    //check name group
    if (!this.groupCompanyUpdate.name){
      swal(this.translate.instant('SWAL_WARN'),this.translate.instant('SWAL_ERROR_NAME'),'warning');
      return;
    }

    if (this.groupCompanyUpdate.type == null) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_GROUP_KEY_TYPE'), 'warning');
      return;
    }
    
    this.apiGroupKeyCompanies.updateGroupKeyCompanies({
      id: this.groupCompanyUpdate.id,
      name: this.groupCompanyUpdate.name,
      key: this.groupCompanyUpdate.key,
      companies: this.arrCompanyIdUpdate,
      type: this.groupCompanyUpdate.type ? this.groupCompanyUpdate.type : null
    }).subscribe(
      data => {
        this.editModal.hide();
        this.groupCompanyUpdate = new GroupKeyCompanyUpdate();
        this.groupCompanyUpdate.companies = [];
        
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      }
    );
  }

  deleteGroupCompanies(id: number){
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
        this.apiGroupKeyCompanies.deleteGroupKeyCompaniesById(id).subscribe(
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

  changeCheckCompanyCreate(event,id){

    if(event.currentTarget.checked){
      this.arrCompanyIdCreate.push(id);
    }
    else{
      let index = this.arrCompanyIdCreate.indexOf(id);
      if(index > -1 ){
        this.arrCompanyIdCreate.splice(index,1);
      }
    }
  }

  changeCheckCompanyUpdate(event,id){

    if(event.currentTarget.checked){
      this.arrCompanyIdUpdate .push(id);
    }
    else{
      let index = this.arrCompanyIdUpdate .indexOf(id);
      if(index > -1 ){
        this.arrCompanyIdUpdate .splice(index,1);
      }
    }
  }
}
