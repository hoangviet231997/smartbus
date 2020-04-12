import { Component, OnInit, ViewChild, AfterViewInit, ViewEncapsulation } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { Router } from '@angular/router';
import { MembershipTmp, MembershipForm } from '../../../../api/models';
import { ManagerMembershipsTmpService, ManagerMembershiptypesService, ManagerModuleCompanyService, ManagerBusStationsService} from '../../../../api/services';
import { HttpErrorResponse } from '@angular/common/http';
import { map } from 'rxjs/operators/map';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { transliterate as tr, slugify } from 'transliteration';
import * as moment from 'moment';
import { ActivatedRoute } from '@angular/router';
import { AppHeaderComponent } from '../../../../shared/app-header/app-header.component'
@Component({
  selector: 'app-memberships-tmp',
  templateUrl: './memberships-tmp.component.html',
  styleUrls: ['./memberships-tmp.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class MembershipsTmpComponent implements OnInit, AfterViewInit{

  @ViewChild('acceptMembershipsTmpModal') public acceptMembershipsTmpModal: ModalDirective;
  @ViewChild('appHeaderComponent') appHeaderComponent: AppHeaderComponent;

  public emailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
  public numberPatten =  /^[-+]?(\d+|\d+\.\d*|\d*\.\d+)$/;

  public membershipsTmp:MembershipTmp[] = [];
  public membershipFormAccept: MembershipForm;
  public membershipTypesPrepaid:any = [];
  public groupBusStationCardPrepaidsTmp:any = [];
  public groupBusStationCardPrepaids:any = [];

  //orther
  public maxDate: Date = new Date();
  public typeCardAccept: any = 0;
  public permissions:any[] = [];
  public style_search: any = '';
  public key_input: any= '';
  public timeoutSearchUser;
  public isModuleCardPrepaidKm = false;
  public isModuleCardPrepaidChargeLimit = false;
  public chooseTypeExpirationAccept: any = 0;
  public selectedGroupBusStationIDAccept = -1;
  public isUpdate = false;

  //property image
  public strImageBase64: any = '';
  public typeImage : any = '';
  public urlAvatar : any = '';

  //pagenation
  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;

  //property route params
  public membershipTmpId:any = 0;

  constructor(
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
    private apiMembershipTmp: ManagerMembershipsTmpService,
    private apiMembershiptype: ManagerMembershiptypesService,
    private apiModuleCompanies: ManagerModuleCompanyService,
    private apiGroupBusStations: ManagerBusStationsService,
    private route: ActivatedRoute,
    private router: Router
  ) {
    this.membershipFormAccept = new MembershipForm;
  }

  ngOnInit() {
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }
    this.getRouteParams();
  }

  getRouteParams() {
    this.route.queryParams.subscribe(
      params => {
        if (params.subjectId !== undefined) {
          this.membershipTmpId = (params.subjectId !== undefined) ? parseInt(params.subjectId) : 0;
          this.apiMembershipTmp.managerListMembershipsTmpByInputAndByTypeSearch({
            style_search: 'id',
            key_input: this.membershipTmpId
          }).subscribe(data => {
            this.membershipsTmp = data;
          });

          this.getListMembershipTypes();
          this.getListModuleCompanies();
          this.getListGroupBusStations();
        }else{
          this.membershipTmpId = 0
        }
      }
    );
  }

  onFileImageChange($event) : void {
    this.eventConvertBase64($event.target);
  }

  eventConvertBase64(inputValue: any): void {
    var file:File = inputValue.files[0];
    var myReader:FileReader = new FileReader();
    myReader.onloadend = (e) => {
      this.strImageBase64 = myReader.result;
      this.typeImage =  file.type;
    }
    myReader.readAsDataURL(file);
  }

  changeSelectExpirationDate(){
    this.membershipFormAccept.start_expiration_date = moment(this.maxDate).format('YYYY-MM-DD HH:mm:ss');
    this.membershipFormAccept.expiration_date = null;
    this.membershipFormAccept.duration = null;
  }

  changeInputNumberExpirationDate(){
    if(this.membershipFormAccept.duration !== null){
      let exp = moment(this.maxDate, 'YYYY-MM-DD HH:mm:ss').add(this.membershipFormAccept.duration, 'days');
      this.membershipFormAccept.expiration_date = moment(exp).format('YYYY-MM-DD 23:59:59');
      this.membershipFormAccept.start_expiration_date = moment(this.maxDate).format('YYYY-MM-DD HH:mm:ss');
    }else{
      this.membershipFormAccept.expiration_date = null;
      this.membershipFormAccept.start_expiration_date = moment(this.maxDate).format('YYYY-MM-DD HH:mm:ss');
    }
  }

  ngAfterViewInit() {
    this.refreshView();
  }

  refreshView(){

    this.route.queryParams.subscribe(
      params => {
        if (params.subjectId !== undefined) {
          this.membershipTmpId = (params.subjectId !== undefined) ? parseInt(params.subjectId) : 0;
          this.apiMembershipTmp.managerListMembershipsTmpByInputAndByTypeSearch({
            style_search: 'id',
            key_input: this.membershipTmpId
          }).subscribe(data => {
            this.membershipsTmp = data;
          });

        }else{
          this.membershipTmpId = 0;
          this.getListMembershipsTmp();
          this.getListMembershipTypes();
          this.getListModuleCompanies();
          this.getListGroupBusStations();
        }
      }
    );

  }

  getListMembershipsTmp(){

    //remove query param
    this.router.navigate([], {queryParams: { subjectId: undefined},queryParamsHandling: 'merge'});

    this.apiMembershipTmp.managerlistMembershipsTmpResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {

        this.key_input = '';
        this.membershipsTmp = resp.body;
        this.paginationTotal = resp.headers.get('pagination-total');
        this.paginationCurrent = resp.headers.get('pagination-current');
        this.paginationLast = resp.headers.get('pagination-last');
      }
    );
  }

  getListMembershipTypes(){
    //get MBS type
    this.membershipTypesPrepaid = [];
    this.apiMembershiptype.managerListMembershipTypes().subscribe( data => {
      data.forEach(element => {
        if(element.code == 0) this.membershipTypesPrepaid.push(element);
      });
    });
  }

  getListModuleCompanies(){
    //get module company
    this.apiModuleCompanies.listModuleCompany().subscribe(data => {
      data.forEach(element => {
        if(element['name'] === 'Module_TTT_Km' ) this.isModuleCardPrepaidKm = true;
        if(element['name'] === 'Module_TTT_SL_Quet') this.isModuleCardPrepaidChargeLimit = true;
      });
    });
  }

  getListGroupBusStations(){
    //get group bus stations
    this.groupBusStationCardPrepaidsTmp = [];
    this.apiGroupBusStations.managerlistGroupBusStation({
      page: 1,
      limit: 99999999
    }).subscribe(resp => {
      resp.forEach(element => {
        if(element.type == "prepaid") {
          let obj = { id: element.id, text: element.name }
          this.groupBusStationCardPrepaidsTmp.push(obj);
        }
      });
    });
  }

  getDataMembershipTmpByInput(){

    clearTimeout(this.timeoutSearchUser);

    if (this.style_search == '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEMBERSHIP_TMP_SEARCH_ACTIVED'), 'warning');
      return;
    }

    //remove query param
    this.router.navigate([], {queryParams: { subjectId: undefined},queryParamsHandling: 'merge'});

    this.timeoutSearchUser = setTimeout(() => {
      if (this.key_input !== '') {
        this.apiMembershipTmp.managerListMembershipsTmpByInputAndByTypeSearch({
          style_search: this.style_search,
          key_input: this.key_input
        }).pipe(
          map(_r => {
            return _r;
          })
        ).subscribe(data => {
          this.membershipsTmp = data;
        });
      } else {
        this.getListMembershipsTmp();
      }
    }, 500);
  }

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.getListMembershipsTmp();
  }

  deleteMembershipsTmp(id){
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
        this.apiMembershipTmp.managerDeleteMembershipTmp(id).subscribe(
          res => {

            this.refreshView();

            //refesh notifies
            this.appHeaderComponent.getDataNotifies();

            this.spinner.hide();
            swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          },
          err => {
            this.spinner.hide();
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_DEL_FAILD')});
          }
        );
      }
    });
  }

  showAcceptMemebershipsTmp(id){

    this.strImageBase64 = '';
    this.typeImage = '';
    this.chooseTypeExpirationAccept = 1;
    this.selectedGroupBusStationIDAccept = -1;
    this.spinner.show();
    this.groupBusStationCardPrepaids = [];
    this.groupBusStationCardPrepaidsTmp.forEach( e => {
      this.groupBusStationCardPrepaids.push(e);
    })
    console.log(this.groupBusStationCardPrepaids);

    this.apiMembershipTmp.managerGetMembershipTmpById(id).subscribe(
      data => {

        this.membershipFormAccept.id = data.id,
        this.membershipFormAccept.membershiptype_id = data.membershiptype_id ;
        this.membershipFormAccept.expiration_date = null;
        this.membershipFormAccept.start_expiration_date = moment(this.maxDate).format('YYYY-MM-DD HH:mm:ss');
        this.membershipFormAccept.fullname = data.fullname;
        this.membershipFormAccept.phone = data.phone;
        this.membershipFormAccept.email = data.email;
        this.membershipFormAccept.address = data.address;
        this.membershipFormAccept.birthday = data.birthday;
        this.membershipFormAccept.cmnd = data.cmnd;
        this.membershipFormAccept.gender = data.gender;
        this.membershipFormAccept.avatar = data.avatar;
        this.membershipFormAccept.company_id = data.company_id;
        this.urlAvatar = '../img/avatar-membership/'+data.avatar;

        this.membershipFormAccept.rfid = null;
        this.membershipFormAccept.duration = null;

        this.acceptMembershipsTmpModal.show();
        this.spinner.hide();
      }
    );
  }

  updateAccept(){

    if (this.membershipFormAccept.fullname === null || this.membershipFormAccept.fullname === '' ) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_FULLNAME'), 'warning');
      return;
    }

    if(this.membershipFormAccept.phone){
      if (!this.numberPatten.test(this.membershipFormAccept.phone)) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_PHONE'), 'warning');
        return;
      }
    }

    if(this.membershipFormAccept.email){
      if (!this.emailPattern.test(this.membershipFormAccept.email)) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_EMAIL'), 'warning');
        return;
      }
    }

    if (this.membershipFormAccept.cmnd) {
      if (!this.numberPatten.test(this.membershipFormAccept.cmnd) || this.membershipFormAccept.cmnd.length < 9) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CMND'), 'warning');
        return;
      }
    }

    if(!this.membershipFormAccept.cmnd && !this.membershipFormAccept.phone) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CMND_OR_PHONE'), 'warning');
      return;
    }

    if (this.typeImage) {
      if (this.typeImage !== 'image/jpeg' && this.typeImage !== 'image/png' && this.typeImage !== 'image/jpg') {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TICKET_DESTROY_IMAGE_FORMAT'), 'warning');
        return;
      }
    }

    if (!this.membershipFormAccept.rfid) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_RFID'), 'warning');
      return;
    }

    if (this.membershipFormAccept.rfid) {

      if (this.membershipFormAccept.rfid.length != 8) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_RFID_LENGTH_8'), 'warning');
        return;
      }

      if(this.isUpperCase(this.membershipFormAccept.rfid) == false){
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_RFID_CHARATER_UPPERCASE'), 'warning');
        return;
      }
    }

    if (!this.membershipFormAccept.membershiptype_id) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_DEDUCTON_MBS_TYPE'), 'warning');
      return;
    }

    if(this.membershipFormAccept.charge_limit_prepaid){
      if (!this.numberPatten.test(this.membershipFormAccept.charge_limit_prepaid.toString())) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CHARGE_LIMIT_PREPAID_FORMAT'), 'warning');
        return;
      }
    }

    if(this.chooseTypeExpirationAccept == 0){
      if(this.membershipFormAccept.duration === null){
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_OPT_NUM_DATE_EXP'), 'warning');
        return;
      }
    }

    if (!this.membershipFormAccept.start_expiration_date) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_OPT_DATE_EXP_START'), 'warning');
      return;
    }

    if (!this.membershipFormAccept.expiration_date) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_OPT_DATE_EXP_END'), 'warning');
      return;
    }

    if(this.membershipFormAccept.start_expiration_date && this.membershipFormAccept.expiration_date){
      let start_date = moment(new Date(this.membershipFormAccept.start_expiration_date))
      let end_date = moment(new Date(this.membershipFormAccept.expiration_date))
      let duration = moment.duration(start_date.diff(end_date));
      let checkDuration = Math.round(duration.asDays()*(-1));
      if(checkDuration < 0) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_EXP_END_COMPERA_EXP_START'), 'warning');
        return;
      }
    }

    //handle value property
    let gr_bus_station_id = null;

    if(this.typeCardAccept == 0){

      //handle expiration
      if(this.chooseTypeExpirationAccept == 1){
        this.membershipFormAccept.expiration_date = moment(this.membershipFormAccept.expiration_date).format('YYYY-MM-DD 23:59:59');
        this.membershipFormAccept.start_expiration_date = moment(this.membershipFormAccept.start_expiration_date).format('YYYY-MM-DD HH:mm:ss');
        let start_date = moment(new Date(this.membershipFormAccept.start_expiration_date))
        let end_date = moment(new Date(this.membershipFormAccept.expiration_date))
        let duration = moment.duration(start_date.diff(end_date));
        this.membershipFormAccept.duration = Math.round(duration.asDays()*(-1));
      }

      //handle station data
      if(this.isModuleCardPrepaidKm)
        if(this.selectedGroupBusStationIDAccept > 0) gr_bus_station_id = this.selectedGroupBusStationIDAccept;
    }

    this.spinner.show();
    this.isUpdate = true;
    this.apiMembershipTmp.managerAcceptMembershipsTmp({
      fullname: this.membershipFormAccept.fullname,
      address: this.membershipFormAccept.address,
      phone: this.membershipFormAccept.phone,
      email: this.membershipFormAccept.email,
      birthday: this.membershipFormAccept.birthday ? moment(this.membershipFormAccept.birthday).format('YYYY-MM-DD') : null,
      cmnd: this.membershipFormAccept.cmnd,
      gender: this.membershipFormAccept.gender,
      rfid: this.membershipFormAccept.rfid ? this.membershipFormAccept.rfid.toUpperCase() : null,
      id: this.membershipFormAccept.id,
      membershiptype_id: this.membershipFormAccept.membershiptype_id,
      expiration_date: this.membershipFormAccept.expiration_date,
      start_expiration_date:this.membershipFormAccept.start_expiration_date,
      duration: this.membershipFormAccept.duration,
      avatar: this.strImageBase64,
      gr_bus_station_id: gr_bus_station_id,
      charge_limit_prepaid: this.membershipFormAccept.charge_limit_prepaid ? parseInt(this.membershipFormAccept.charge_limit_prepaid.toString()): null,
      company_id: this.membershipFormAccept.company_id,
    }).subscribe(
      res => {

        this.acceptMembershipsTmpModal.hide();
        this.isUpdate = false;
        this.membershipFormAccept = new MembershipForm();
        this.membershipFormAccept.duration = null;
        this.membershipFormAccept.expiration_date = null;
        this.membershipFormAccept.membershiptype_id = null;
        this.membershipFormAccept.charge_limit_prepaid = null;
        this.selectedGroupBusStationIDAccept = -1;
        this.strImageBase64 = '';

        this.refreshView();

        //refesh notifies
        this.appHeaderComponent.getDataNotifies();

        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
        this.spinner.hide();
      },
      err => {
        this.spinner.hide();
        this.isUpdate = false;
        if (err instanceof HttpErrorResponse) {
          if (err.status === 404) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
          } else if (err.status === 422) {
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
          }
        }
      }
    );
  }

  refreshValueGroupBusStation( value: any):void{
    this.selectedGroupBusStationIDAccept = value['id'];
  }

  selectedGroupBusStation(value:any){

    this.selectedGroupBusStationIDAccept = value['id'];
  }

  removedGroupBusStation(value:any){
    this.selectedGroupBusStationIDAccept = -1;
  }

  isUpperCase(str) { return str === str.toUpperCase(); }
}
