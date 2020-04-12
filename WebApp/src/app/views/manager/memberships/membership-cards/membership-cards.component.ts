
import { Component, OnInit, ViewChild, AfterViewInit, ViewEncapsulation } from '@angular/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import swal from 'sweetalert2';
import { Router } from '@angular/router';
import { Membership, SubscriptionType, MembershipForm, MembershipDetailSearch } from '../../../../api/models';
import {
  ManagerMembershipsService,
  ManagerMembershiptypesService,
  ManagerModuleCompanyService,
  ManagerTicketTypesService,
  ManagerBusStationsService,
  ManagerRoutesService
} from '../../../../api/services';
import { HttpErrorResponse } from '@angular/common/http';
import { map } from 'rxjs/operators/map';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { transliterate as tr, slugify } from 'transliteration';
import * as moment from 'moment';
import { ReturnStatement } from '@angular/compiler';
import { exists } from 'fs';
import { Alert } from 'selenium-webdriver';
import { ActivatedRoute } from '@angular/router';
import { ActivityLogsService } from '../../../../shared/activity-logs.service';
import { AppHeaderComponent } from '../../../../shared/app-header/app-header.component';

@Component({
  selector: 'app-membership-cards',
  templateUrl: './membership-cards.component.html',
  styleUrls: ['./membership-cards.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class MembershipCardsComponent implements OnInit {

  @ViewChild('detailMembershipModel') public detailMembershipModel: ModalDirective;
  @ViewChild('extendCard') public extendCard: ModalDirective;
  @ViewChild('activedMembershipModal') public activedMembershipModal: ModalDirective;
  @ViewChild('renewedModal') public renewedModal: ModalDirective;
  @ViewChild('changeCardModal') public changeCardModal: ModalDirective;
  @ViewChild('backgroundPrintModal') public backgroundPrintModal: ModalDirective;
  @ViewChild('appHeaderComponent') appHeaderComponent: AppHeaderComponent;

  public emailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
  public numberPatten =  /^[-+]?(\d+|\d+\.\d*|\d*\.\d+)$/;

  public user_down: any= null;
  public maxDate: Date;
  public isCreated = false;
  public isUpdate = false;
  public current = moment(new Date());
  public permissions:any[] = [];
  public rfID: string;
  public maxSize: any = 5;
  public valueDateCurrent = 0;
  public valueMonthCurrent = 0;

  // page membership not actived--------------------------
  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;
  // page membership actived ---------------------
  public limitPageAct = 10;
  public currentPageAct = 1;
  public paginationTotalAct;
  public paginationCurrentAct;
  public paginationLastAct;
  public membershipActs: any = [];

  public memberships: any = [];
  public routes: any = [];
  public ticketTypes:any = [];
  public busStations:any = [];
  public membershipTypesPrepaid: any[] = [];
  public membershiptypeIdPrepaid = null;
  public membershipTypesMonth: any[] = [];
  public membershiptypeIdMonth = null;
  public membershipFormActived: MembershipForm;
  public membershipFormUpdate: MembershipForm;
  public membershipFormChange: MembershipForm;
  public membershipDetailSearch: MembershipDetailSearch;

  //data group bus staion card month
  public groupBusStationCardMonths: any = [];

  //data group bus staion card month
  public groupBusStationCardPrepaids: any = [];

  //module company
  public isModuleCardMonth = false;
  public isModuleCardMonthKm = false;
  public isModuleCardMonthChargeLimit = false;
  public isModuleCardPrepaidKm = false;
  public isModuleCardPrepaidChargeLimit = false;

  // membership extend Card
  public typeCardExtend: any = null;
  public tmpBusStationExtend = [];
  public resultBusStationExtend = [];
  public reponseBusStationExtend = [];
  public checkedShowExpiration =  0;
  public chooseTypeExpirationExtend: any = 0;
  public isCheckExpirationDateExtend = false;
  public selectedRouteIDExtend:any = -1;
  public selectedGroupBusStationIDExtend:any =  -1;
  public selectedGroupBusStationIDExtend_1:any =  [];
  public selectedGroupBusStationIDExtend_active:any =  [];
  public chooseSelectedRouteWayExtend: any = -1;

  //active experition
  public tmpBusStation = [];
  public resultBusStation = [];
  public typeCard: any = 0;
  public chooseTypeExpiration: any = 0;
  public selectedRouteID:any = -1;
  public selectedGroupBusStationID:any =  -1;
  public selectedGroupBusStationID_1:any =  [];
  public chooseSelectedRouteWay: any = -1;

  public membershipDetailSearchPlaceholder: any = 'Tìm kiếm...';

  // detail membership Charges -----------------------
  public membershipsCharges: any = [];
  public membershipsChargetotal: any = 0;
  public limitPageCharges = 10;
  public currentPageCharges = 1;
  public paginationTotalCharges;
  public paginationCurrentCharges;
  public paginationLastCharges;

  // detail membership Deposits------------------------
  public membershipsDeposits: any = [];
  public membershipsDepositTotal: any = 0;
  public limitPageDeposits = 10;
  public currentPageDeposits = 1;
  public paginationTotalDeposits;
  public paginationCurrentDeposits;
  public paginationLastDeposits;

  //card name  print ------------------------------------
  public expirationCard : string = null;
  public qrcode : string = null;
  public seriCard: string = '';
  public selectedGenerateBarcode = 1;
  public searchText: any;
  public searchDate: any;

  public search_activated: any = '';
  public input_activated: any= '';
  public search_not_activated: any = '';
  public input_not_activated: any= '';
  public timeout_search_membership;

  //property image
  public strImageBase64: any = '';
  public typeImage : any = '';
  public urlAvatar : any = '';

  //print layout card
  public background_cards = {};
  public opt_print: any;
  public opt_background: any;
  public backgrounds: any[] = [];
  public data_obj: any;
  public opt_name: any = false;
  public group_station_way = 'Tất cả';

  //property router link params
  public membershipId:any = 0;

  //property tab
  public activeTab:any = 'tab-card-act';

  constructor(
    private apiTicketTypes: ManagerTicketTypesService,
    private translate: TranslateService,
    private apiMembership: ManagerMembershipsService,
    private apiMembershiptype: ManagerMembershiptypesService,
    private apiModuleCompanies: ManagerModuleCompanyService,
    private apiGroupBusStations: ManagerBusStationsService,
    private activityLogs: ActivityLogsService,
    private apiRoutes: ManagerRoutesService,
    private spinner: NgxSpinnerService,
    private route: ActivatedRoute,
    private router: Router)
  {
    this.membershipFormActived = new MembershipForm;
    this.membershipFormUpdate = new MembershipForm;
    this.membershipFormChange = new MembershipForm;
    this.membershipDetailSearch = new MembershipDetailSearch;
    this.maxDate = new Date();

    this.selectedGroupBusStationIDExtend_1 = [];
    this.selectedGroupBusStationID_1 = [];
  }

  ngOnInit() {

    this.user_down = localStorage.getItem('token_shadow');
    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
      let layout = JSON.parse(localStorage.getItem('user')).company.layout_cards;
      if(layout){
        this.background_cards = JSON.parse(layout);
      }
    }

    this.membershipDetailSearch.search_opt = 0;
    this.membershipDetailSearch.search_date = null;

    this.getRouteParams();
  }

  getRouteParams() {

    this.route.queryParams.subscribe(
      params => {
        if (params.subjectId !== undefined) {
          this.membershipId = (params.subjectId !== undefined) ? parseInt(params.subjectId) : 0;
          this.apiMembership.managerListMembershipsByInputAndBySearch({
            key_case: 'activated',
            key_input: this.membershipId,
            key_search: 'id',
          }).subscribe(data => {
            this.activeTab = 'tab-card-act';
            this.spinner.hide();
            this.membershipActs = data;
          });

          this.getListGroupBusStations();
          this.getListRoutes();
          this.getListMembershipTypes();
          this.getListDemininations();
          this.getListModuleCompanies();
        }else{
          this.membershipId = 0;
        }
      }
    );
  }

  ngAfterViewInit() {

    this.route.queryParams.subscribe(
      params => {
        if (params.subjectId !== undefined) {
          this.membershipId = (params.subjectId !== undefined) ? parseInt(params.subjectId) : 0;
          this.apiMembership.managerListMembershipsByInputAndBySearch({
            key_case: 'activated',
            key_input: this.membershipId,
            key_search: 'id',
          }).subscribe(data => {
            this.activeTab = 'tab-card-act';
            this.spinner.hide();
            this.membershipActs = data;
          });
        }else{

          this.refreshViewAct();

          //handle date  value current
          var dd =  new Date();
          this.valueDateCurrent = dd.getTime();
          this.valueMonthCurrent = dd.getMonth() + dd.getFullYear();

          this.getListGroupBusStations();
          this.getListRoutes();
          this.getListMembershipTypes();
          this.getListDemininations();
          this.getListModuleCompanies();
        }
      }
    );
  }

  refreshView() {
    //remove query param
    this.router.navigate([], {queryParams: { subjectId: undefined},queryParamsHandling: 'merge'});
    this.membershipId = 0;

    this.getListMembership();
  }

  refreshViewAct() {

    //remove query param
    this.router.navigate([], {queryParams: { subjectId: undefined},queryParamsHandling: 'merge'});
    this.membershipId = 0;

    this.getListMembershipAct();
  }

  getListGroupBusStations(){
    //get group bus stations
    this.groupBusStationCardMonths = [];
    this.groupBusStationCardPrepaids = [];
    this.apiGroupBusStations.managerlistGroupBusStation({
      page: 1,
      limit: 99999999
    }).subscribe(resp => {
      for (let i = 0; i < resp.length; i++) {
        if(resp[i].type === "month"){
          this.groupBusStationCardMonths.push({
            id: resp[i].id+'_'+resp[i].ticket_price_id,
            text: resp[i].name
          });
        }
        if(resp[i].type === "prepaid"){
          this.groupBusStationCardPrepaids.push({
            id: resp[i].id,
            text: resp[i].name
          });
        }
      }
    });
  }

  getListRoutes(){
    //get routes
    this.apiRoutes.managerlistRoutes({ page: 1, limit: 99999999 }).subscribe(
      resp => { this.routes = resp; }
    );
  }

  getListMembershipTypes(){
    //get MBS type
    this.membershipTypesPrepaid = [];
    this.membershipTypesMonth = [];
    this.apiMembershiptype.managerListMembershipTypes().subscribe( data => {
      data.forEach(element => {
        if(element.code == 0) this.membershipTypesPrepaid.push(element);
        if(element.code == 1) this.membershipTypesMonth.push(element);
      });
    }
  );
  }

  getListDemininations(){
    //get demininations
    this.apiTicketTypes.managerListTicketTypesByTypeParam(1).subscribe(data => { this.ticketTypes = data; });
  }

  getListModuleCompanies(){
    //get module company
    this.apiModuleCompanies.listModuleCompany().subscribe(data => {
      data.forEach(element => {
        if(element['name'] === 'Module_TT_Km' ){
          this.isModuleCardMonth = true;
          this.isModuleCardMonthKm = true;
        }
        if(element['name'] === 'Module_TT_SL_Quet'){
          this.isModuleCardMonth = true;
          this.isModuleCardMonthChargeLimit = true;
        }
        if(element['name'] === 'Module_TTT_Km' ) this.isModuleCardPrepaidKm = true;
        if(element['name'] === 'Module_TTT_SL_Quet') this.isModuleCardPrepaidChargeLimit = true;
      });
    });
  }

  //membership not activated
  getListMembership() {

    this.spinner.show();
    this.apiMembership.managerlistMembershipsResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {

        this.input_not_activated = '';
        this.memberships = resp.body['data'];

        this.paginationTotal = resp.body['total'];
        this.paginationCurrent = resp.body['current_page'];
        this.paginationLast = resp.body['last_page'];

        this.spinner.hide();
      }
    );
  }
  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.refreshView();
  }
  // ----------------Fucntion handle ganaral Activaed + Extend -------------------------------------------//
  onTabChange(event: any) {
    if (event.nextId === 'tab-card-act') {
      this.refreshViewAct();
      this.activeTab = 'tab-card-act';
      this.search_activated = '';
    }
    if (event.nextId === 'tab-card-new') {
      this.refreshView();
      this.activeTab = 'tab-card-new';
      this.search_not_activated = '';
    }
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

  changeSelectedMembershipTypeCard(){

    this.membershipFormActived.charge_limit_prepaid = null;
    this.membershipFormActived.ticket_price_id = null;
    this.selectedGroupBusStationID = -1

    if(this.typeCard == 1){

      if(this.membershipTypesMonth.length == 0){

        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_MONTH'), 'warning');
        this.activedMembershipModal.hide();
        return;
      }
      this.membershipFormActived.membershiptype_id = this.membershiptypeIdMonth;
      this.chooseTypeExpiration = 2;
      this.membershipFormActived.charge_limit = 0;
    }else{
      this.membershipFormActived.membershiptype_id = this.membershiptypeIdPrepaid;
      this.chooseTypeExpiration = 0;
    }
  }

  changeSelectedRoute(type){
    //actived [] busStations
    if(type == 0){
      this.tmpBusStation = [];
      this.resultBusStation = [];
      if(this.selectedRouteID > 0){
        this.apiRoutes.managerGetRouteById(this.selectedRouteID).subscribe( data =>{
          // this.busStations = data.bus_stations;
          data.bus_stations.forEach(element => {
            if(element.direction == 0){
              const object = {
                route_id: this.selectedRouteID,
                id: element.id,
                name: element.name,
                station_order: element.station_order,
                direction: element.direction,
                disable: null,
                lat: element.lat,
                lng: element.lng,
                station_relative: element.station_relative
              };
              this.tmpBusStation.push(object);
            }
          });
        });
      }
    }

    // Extend  [] busStations
    if(type == 1){

      this.tmpBusStationExtend = [];
      this.resultBusStationExtend = [];
      if(this.selectedRouteIDExtend > 0){
        this.apiRoutes.managerGetRouteById(this.selectedRouteIDExtend).subscribe( data =>{
          data.bus_stations.forEach(element => {
            if(element.direction == 0){
              if(this.reponseBusStationExtend.indexOf(element.id) !== -1) {
                const object = {
                  route_id: this.selectedRouteIDExtend,
                  id: element.id,
                  name: element.name,
                  station_order: element.station_order,
                  direction: element.direction,
                  disable: false,
                  lat: element.lat,
                  lng: element.lng,
                  station_relative: element.station_relative
                };
                this.resultBusStationExtend.push(object);
                this.tmpBusStationExtend.push(object);
              }else{

                const object = {
                  route_id: this.selectedRouteIDExtend,
                  id: element.id,
                  name: element.name,
                  station_order: element.station_order,
                  direction: element.direction,
                  disable: true,
                  lat: element.lat,
                  lng: element.lng,
                  station_relative: element.station_relative
                };
                this.tmpBusStationExtend.push(object);
              }
            }
          });

          if(this.resultBusStationExtend.length == 0){
            this.tmpBusStationExtend.map(e => {
              e.disable = false;
            });
          }

          // if(this.selectedRouteIDExtend == this.idRouteGlobalExtend){
          //   this.resultBusStationExtend = this.reponseBusStationExtend;
          //   this.tmpBusStationExtend.map(element => {
          //     if(this.resultBusStationExtend[0] == element.id || this.resultBusStationExtend[1] == element.id){
          //       element.disable = false
          //     }
          //     if(this.resultBusStationExtend[0] != element.id && this.resultBusStationExtend[1] != element.id){
          //       element.disable = true;
          //     }
          //   });
          // }
        });
      }
    }
  }

  changeCheckedBusStation(event, busStation, type: number){

    //actived [] busStations
    if(type === 0){
      if (event.currentTarget.checked){
        this.tmpBusStation.map(element => {
          if(busStation.id === element.id){
            if( this.resultBusStation.length < 2){
              this.resultBusStation.push(element);
              element.disable = false;
              if(this.resultBusStation.length == 2){
                this.tmpBusStation.map(element => {
                  if(element.disable !== false){
                    element.disable = true;
                  }
                });
              }
            }
          }else{
            if(this.resultBusStation.length == 2){
              if(element.disable !== false){
                element.disable = true;
              }
            }
          }
        });
      }else{
        const index: number = this.resultBusStation.indexOf(busStation);
        if (index !== -1) {

          this.tmpBusStation.map(element => {
            if(busStation.id === element.id){
              this.resultBusStation.splice(index, 1);
              element.disable = null;
            }else{
              if(element.disable !== false){
                element.disable = null;
              }
            }
          });
        }
      }
    }

    //Extend [] busStations
    if(type === 1){
      if (event.currentTarget.checked){

        this.resultBusStationExtend.push(busStation);
        if( this.resultBusStationExtend.length < 2){
          this.tmpBusStationExtend.map(element => {
            element.disable = false;
          });
        }else{
          if(this.resultBusStationExtend.length == 2){

            this.tmpBusStationExtend.map(element => {
              const index_1: number = this.resultBusStationExtend.findIndex(i => i.id === element.id);
              if(index_1 !== -1){
                element.disable = false;
              }else{
                element.disable = true;
              }
            });
          }
          if(this.resultBusStationExtend.length > 2){
            const index_2: number = this.resultBusStationExtend.findIndex(i => i.id === busStation.id);
            this.resultBusStationExtend.splice(index_2, 1);
          }
        }
        // this.tmpBusStationExtend.map(element => {
        //   if(busStation.id === element.id){
        //     if( this.resultBusStationExtend.length < 2){
        //       this.resultBusStationExtend.push(element);
        //       element.disable = false;
        //       if(this.resultBusStationExtend.length == 2){
        //         this.tmpBusStationExtend.map(e => {
        //           if(e.disable !== false){
        //             e.disable = true;
        //           }
        //         });
        //       }
        //     }
        //   }else{
        //     if(this.resultBusStationExtend.length == 2){
        //       if(element.disable !== false){
        //         element.disable = true;
        //       }
        //     }
        //   }
        // });
      }else{
        const index: number = this.resultBusStationExtend.findIndex(i => i.id === busStation.id);
        if (index !== -1) {
          this.tmpBusStationExtend.map(element => {
            if(busStation.id === element.id){
              this.resultBusStationExtend.splice(index, 1);
              element.disable = null;
            }
            else{
              if(element.disable == true){
                element.disable = null;
              }
            }
          });
        } else{
          this.tmpBusStationExtend.map(element => {
            if(busStation.id === element.id){
              this.resultBusStationExtend.splice(index, 1);
              element.disable = null;
            }
            else{
              if(element.disable == true){
                element.disable = null;
              }
            }
          });
        }
      }
    }
  }

  changeInputNumberExpirationDate(type){

    if(type == 0){
      if(this.membershipFormActived.duration){

        let exp = moment(this.current, 'YYYY-MM-DD HH:mm:ss').add(this.membershipFormActived.duration, 'days');
        this.membershipFormActived.expiration_date = moment(exp).format('YYYY-MM-DD 23:59:59');
        this.membershipFormActived.start_expiration_date = moment(this.current).format('YYYY-MM-DD HH:mm:ss');
      }else{
        this.membershipFormActived.expiration_date = null;
        this.membershipFormActived.start_expiration_date = moment(this.current).format('YYYY-MM-DD HH:mm:ss');
      }
    }

    if(type == 1){
      if(this.membershipFormUpdate.duration){

        let exp = moment(this.current, 'YYYY-MM-DD HH:mm:ss').add(this.membershipFormUpdate.duration, 'days');
        this.membershipFormUpdate.expiration_date = moment(exp).format('YYYY-MM-DD 23:59:59');
        this.membershipFormUpdate.start_expiration_date = moment(this.current).format('YYYY-MM-DD HH:mm:ss');
      }else{
        this.membershipFormUpdate.expiration_date = null;
        this.membershipFormUpdate.start_expiration_date = moment(this.current).format('YYYY-MM-DD HH:mm:ss');
      }
    }
  }

  changeSelectExpirationDate(type){

    if(type == 0){
      this.membershipFormActived.expiration_date = null;
      this.membershipFormActived.start_expiration_date = moment(this.current).format('YYYY-MM-DD HH:mm:ss');
    }

    if(type == 1){
      this.membershipFormUpdate.expiration_date = this.membershipFormUpdate.expiration_date;
      this.membershipFormUpdate.start_expiration_date = this.membershipFormUpdate.start_expiration_date;
    }
  }

  changeSelectedTicketPrice(type){

    if(type == 0) this.membershipFormActived.charge_limit = 0;
    if(type == 1) this.membershipFormUpdate.charge_limit = 0;
  }

  //-----change selecr ng2 group busstation
  refreshValueGroupBusStation( value: any, typeOpt, type: string):void{

    if(typeOpt == 0){

      if(type === "month"){
        this.selectedGroupBusStationID_1 = value;
        let splitArr = this.selectedGroupBusStationID_1['id'].split("_");
        this.membershipFormActived.ticket_price_id = parseInt(splitArr[1]);
        this.selectedGroupBusStationID = parseInt(splitArr[0]);
      }

      if(type === "prepaid") this.selectedGroupBusStationID = value['id'];
    }

    if(typeOpt == 1){

      if(type === "month"){
        this.selectedGroupBusStationIDExtend_1 = value;
        let splitArr = this.selectedGroupBusStationIDExtend_1['id'].split("_");
        this.membershipFormUpdate.ticket_price_id = parseInt(splitArr[1]);
        this.selectedGroupBusStationIDExtend = parseInt(splitArr[0]);
      }

      if(type === "prepaid") this.selectedGroupBusStationIDExtend = value['id'];
    }
  }

  selectedGroupBusStation(value:any, typeOpt, type: string){

    if(typeOpt == 0){

      if(type === "month"){
        this.selectedGroupBusStationID_1 = value;
        let splitArr = this.selectedGroupBusStationID_1['id'].split("_");
        this.membershipFormActived.ticket_price_id = parseInt(splitArr[1]);
        this.selectedGroupBusStationID = parseInt(splitArr[0]);
      }

      if(type === "prepaid") this.selectedGroupBusStationID = value['id'];
    }

    if(typeOpt == 1){

      if(type === "month"){
        this.selectedGroupBusStationIDExtend_1 = value;
        let splitArr = this.selectedGroupBusStationIDExtend_1['id'].split("_");
        this.membershipFormUpdate.ticket_price_id = parseInt(splitArr[1]);
        this.selectedGroupBusStationIDExtend = parseInt(splitArr[0]);
      }

      if(type === "prepaid") this.selectedGroupBusStationIDExtend = value['id'];
    }
  }

  removedGroupBusStation(value:any, typeOpt, type: string){

    if(typeOpt == 0){

      if(type === "month"){
        if( this.selectedGroupBusStationID_1['id'] == value.id){
          this.selectedGroupBusStationID_1['id'] = null;
          this.membershipFormActived.ticket_price_id = null;
          this.selectedGroupBusStationID = -1;
        }
      }

      if(type === "prepaid") this.selectedGroupBusStationID = -1;
    }

    if(typeOpt == 1){

      if(type === "month"){
        if( this.selectedGroupBusStationIDExtend_1['id'] == value.id){
          this.selectedGroupBusStationIDExtend_1['id'] = null;
          this.membershipFormUpdate.ticket_price_id = null;
          this.selectedGroupBusStationIDExtend = -1;
        }
      }

      if(type === "prepaid") this.selectedGroupBusStationIDExtend = -1;
    }
  }
  //-----end change selecr ng2 group busstation

  // ----------------end Fucntion handle ganaral Actived + Extend --------------------------------------------//


  // ----------------Function for Actived
  showActiveMembershipCardModalById(id){

    this.typeCard = 0;
    this.chooseTypeExpiration = 0;
    this.strImageBase64 = '';
    this.typeImage = '';
    this.chooseSelectedRouteWay = -1;
    this.selectedRouteID = -1;
    this.selectedGroupBusStationID = -1;
    this.membershipFormActived.charge_limit_prepaid = null;
    this.selectedGroupBusStationID_1 = [];
    this.spinner.show();
    this.apiMembership.managerGetMembershipById(id).subscribe(
      data => {
        this.membershipFormActived = data;
        this.membershipFormActived.rfid = data['rfidcard'].rfid;
        this.membershipFormActived.barcode =  data['rfidcard'].barcode;
        this.membershipFormActived.start_expiration_date = moment(this.current).format('YYYY-MM-DD HH:mm:ss');

        //set membership_type_id for card prepaid
        this.membershiptypeIdPrepaid = data.membershiptype_id;
        this.spinner.hide();
        this.activedMembershipModal.show();
      }
    );
  }

  updateActive() {

    // card prepaid
    if( this.typeCard == 0){

      this.membershipFormActived.membershiptype_id = this.membershiptypeIdPrepaid;

      if (this.membershiptypeIdPrepaid == null) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_CARD_TPE'), 'warning');
        return;
      }

      if(this.membershipFormActived.charge_limit_prepaid){
        if (!this.numberPatten.test(this.membershipFormActived.charge_limit_prepaid.toString())) {
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CHARGE_LIMIT_PREPAID_FORMAT'), 'warning');
          return;
        }
      }

      if (!this.membershipFormActived.start_expiration_date) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_OPT_DATE_EXP_START'), 'warning');
        return;
      }

      if (!this.membershipFormActived.expiration_date) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_OPT_DATE_EXP_END'), 'warning');
        return;
      }

      if(this.membershipFormActived.start_expiration_date && this.membershipFormActived.expiration_date){
        let start_date = moment(new Date(this.membershipFormActived.start_expiration_date))
        let end_date = moment(new Date(this.membershipFormActived.expiration_date))
        let duration = moment.duration(start_date.diff(end_date));
        let checkDuration = Math.round(duration.asDays()*(-1));
        if(checkDuration < 0) {
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_EXP_END_COMPERA_EXP_START'), 'warning');
          return;
        }
      }
    }

    // card month
    if(this.typeCard == 1){

      this.membershipFormActived.membershiptype_id = this.membershiptypeIdMonth;

      if (this.membershiptypeIdMonth == null) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_CARD_TPE'), 'warning');
        return;
      }

      if(this.chooseSelectedRouteWay == 1){
        if(this.selectedGroupBusStationID < 0){
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_OPT_GROUP_BUSSTATION'), 'warning');
          return;
        }
      }

      if(this.chooseSelectedRouteWay == 0){

        if(this.selectedRouteID < 0){
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_OPT_ROUTE_BUSSTATION'), 'warning');
          return;
        }else{
          if(this.resultBusStation.length != 2){
            swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_OPT_STATION'), 'warning');
            return;
          }
        }
      }

      if(!this.membershipFormActived.ticket_price_id){
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_OPT_DENOMINATION'), 'warning');
        return;
      }

      if(this.strImageBase64 == ''){
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_AVATAR'), 'warning');
        return;
      }
    }

    if (this.membershipFormActived.fullname === null || this.membershipFormActived.fullname === '' ) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_FULLNAME'), 'warning');
      return;
    }

    if ((this.membershipFormActived.phone === null || this.membershipFormActived.phone === '') && ( this.membershipFormActived.cmnd === null || this.membershipFormActived.cmnd === '') ) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CMND_OR_PHONE'), 'warning');
      return;
    }

    if(this.membershipFormActived.phone){
      if (!this.numberPatten.test(this.membershipFormActived.phone) || (this.membershipFormActived.phone.length < 10 || this.membershipFormActived.phone.length > 11)) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_PHONE'), 'warning');
        return;
      }
    }

    if(this.membershipFormActived.email){
      if (!this.emailPattern.test(this.membershipFormActived.email)) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_EMAIL'), 'warning');
        return;
      }
    }

    if (this.membershipFormActived.cmnd) {
      if (!this.numberPatten.test(this.membershipFormActived.cmnd) || this.membershipFormActived.cmnd.length != 9) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CMND'), 'warning');
        return;
      }
    }

    if(!this.membershipFormActived.cmnd && !this.membershipFormActived.phone) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CMND_OR_PHONE'), 'warning');
      return;
    }

    if (this.typeImage) {
      if (this.typeImage !== 'image/jpeg' && this.typeImage !== 'image/png' && this.typeImage !== 'image/jpg') {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TICKET_DESTROY_IMAGE_FORMAT'), 'warning');
        return;
      }
    }

    //handle value property
    let gr_bus_station_id = null;
    let station_data = null;

    if(this.typeCard == 0){

      //handle expiration
      if(this.chooseTypeExpiration == 1){
        this.membershipFormActived.expiration_date = moment(this.membershipFormActived.expiration_date).format('YYYY-MM-DD 23:59:59');
        this.membershipFormActived.start_expiration_date = moment(this.membershipFormActived.start_expiration_date).format('YYYY-MM-DD HH:mm:ss');
        let start_date = moment(new Date(this.membershipFormActived.start_expiration_date))
        let end_date = moment(new Date(this.membershipFormActived.expiration_date))
        let duration = moment.duration(start_date.diff(end_date));
        this.membershipFormActived.duration = Math.round(duration.asDays()*(-1));
      }

      //handle station data
      if(this.isModuleCardPrepaidKm)
        if(this.selectedGroupBusStationID > 0) gr_bus_station_id = this.selectedGroupBusStationID;
    }

    if(this.typeCard == 1){

      //handle expiration
      this.membershipFormActived.expiration_date =  moment(this.current).format('YYYY-MM') ;
      let month = this.membershipFormActived.expiration_date.substr(5,2);
      let year = this.membershipFormActived.expiration_date.substr(0,4);
      let duration = new Date(parseInt(year), parseInt(month), 0).getDate()
      this.membershipFormActived.duration = duration;

      //handle station data
      if(this.chooseSelectedRouteWay == 0){
        gr_bus_station_id = 0;
        if(this.resultBusStation.length > 0){
          station_data =  this.resultBusStation;
        }else{
          station_data = null;
          gr_bus_station_id = null;
        }
      }

      if(this.chooseSelectedRouteWay > 0){
        if(this.selectedGroupBusStationID > 0){
          gr_bus_station_id = this.selectedGroupBusStationID;
        }else{
          station_data = null;
          gr_bus_station_id = null;
        }
      }
    }

    this.spinner.show();
    this.apiMembership.managerUpdateMembership({
      rfidcard_id: this.membershipFormActived.rfidcard_id,
      rfid: this.membershipFormActived.rfid,
      barcode: '',
      id: this.membershipFormActived.id,
      membershiptype_id: this.membershipFormActived.membershiptype_id,
      fullname: this.membershipFormActived.fullname,
      address: this.membershipFormActived.address,
      phone: this.membershipFormActived.phone,
      email: this.membershipFormActived.email,
      birthday: this.membershipFormActived.birthday ? moment(this.membershipFormActived.birthday).format('YYYY-MM-DD') : null,
      expiration_date: this.membershipFormActived.expiration_date,
      start_expiration_date:this.membershipFormActived.start_expiration_date,
      duration: this.membershipFormActived.duration,
      ticket_price_id: this.membershipFormActived.ticket_price_id,
      actived: 1,
      balance: this.membershipFormActived.balance,
      cmnd: this.membershipFormActived.cmnd,
      gender: this.membershipFormActived.gender,
      avatar: this.strImageBase64,
      gr_bus_station_id: gr_bus_station_id,
      station_data: station_data,
      type_edit: 1,
      charge_limit_prepaid: this.membershipFormActived.charge_limit_prepaid ? parseInt(this.membershipFormActived.charge_limit_prepaid.toString()): null
    }).subscribe(
      res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'active';
        activity_log['subject_type'] = 'membership';
        activity_log['subject_data'] = this.membershipFormActived ? JSON.stringify({
          rfidcard_id: this.membershipFormActived.rfidcard_id,
          rfid: this.membershipFormActived.rfid,
          barcode: '',
          id: this.membershipFormActived.id,
          membershiptype_id: this.membershipFormActived.membershiptype_id,
          fullname: this.membershipFormActived.fullname,
          address: this.membershipFormActived.address,
          phone: this.membershipFormActived.phone,
          email: this.membershipFormActived.email,
          birthday: this.membershipFormActived.birthday,
          expiration_date: this.membershipFormActived.expiration_date,
          start_expiration_date:this.membershipFormActived.start_expiration_date,
          duration: this.membershipFormActived.duration,
          ticket_price_id: this.membershipFormActived.ticket_price_id,
          actived: 1,
          balance: this.membershipFormActived.balance,
          cmnd: this.membershipFormActived.cmnd,
          gender: this.membershipFormActived.gender,
          gr_bus_station_id: gr_bus_station_id,
          station_data: station_data,
          charge_limit_prepaid: this.membershipFormActived.charge_limit_prepaid ? parseInt(this.membershipFormActived.charge_limit_prepaid.toString()) : null
        }) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.activedMembershipModal.hide();
        this.membershipFormActived = new MembershipForm();
        this.membershipFormActived.duration = null;
        this.membershipFormActived.expiration_date = null;
        this.membershipFormActived.membershiptype_id = null;
        this.membershipFormActived.ticket_price_id = null;
        this.membershipFormActived.charge_limit_prepaid = null;
        this.selectedGroupBusStationID = -1;
        this.selectedRouteID = -1;
        this.chooseSelectedRouteWay = -1;
        this.resultBusStation = [];
        this.strImageBase64 = '';

        this.refreshView();

        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
        this.spinner.hide();
      },
      err => {
        this.spinner.hide();
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

  getListMembershipAct() {

    this.spinner.show();
    this.apiMembership.managerlistMembershipActiveResponse({
      page: this.currentPageAct,
      limit: this.limitPageAct
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe(
      resp => {

        this.input_activated = '';
        this.membershipActs = resp.body['data'];
        this.paginationTotalAct = resp.body['total'];
        this.paginationCurrentAct = resp.body['current_page'];
        this.paginationLastAct = resp.body['last_page'];

        this.spinner.hide();
      }
    );
  }

  pageChangedAct(event: any): void {
    this.currentPageAct = event.page;
    this.refreshViewAct();
  }
  // ----------------end Function for Actived------------------------------


  // ----------------Function for Extend --------------------------------
  showExtendCard(cardId){

    this.strImageBase64 = '';
    this.typeImage = '';
    this.isCheckExpirationDateExtend = false;
    this.reponseBusStationExtend = [];
    this.tmpBusStationExtend = [];
    this.selectedRouteIDExtend = -1;
    this.selectedGroupBusStationIDExtend = -1;
    this.chooseSelectedRouteWayExtend = -1;

    this.extendCard.show();
    this.spinner.show();
    this.apiMembership.managerGetMembershipById(cardId).subscribe(
      data => {
        this.membershipFormUpdate.id = data.id,
        this.membershipFormUpdate.rfid = data['rfidcard'] ? data['rfidcard'].rfid : null;
        this.membershipFormUpdate.barcode = data['rfidcard'] ? data['rfidcard'].barcode : null;
        this.membershipFormUpdate.membershiptype_id = data.membershiptype_id ;
        this.membershipFormUpdate.expiration_date = data.expiration_date;
        this.membershipFormUpdate.start_expiration_date = data.start_expiration_date;
        this.membershipFormUpdate.duration = data.duration;
        this.membershipFormUpdate.fullname = data.fullname;
        this.membershipFormUpdate.phone = data.phone;
        this.membershipFormUpdate.email = data.email;
        this.membershipFormUpdate.address = data.address;
        this.membershipFormUpdate.birthday = data.birthday;
        this.membershipFormUpdate.cmnd = data.cmnd;
        this.membershipFormUpdate.gender = data.gender;
        this.membershipFormUpdate.avatar = data.avatar;
        this.membershipFormUpdate.ticket_price_id = data.ticket_price_id;
        this.membershipFormUpdate.charge_limit = data.charge_limit;

        this.urlAvatar = '../img/avatar-membership/'+this.membershipFormUpdate.avatar;

        if(data.membershiptype_id){

          if(data['membership_type'].code == 0 ){

            this.typeCardExtend = 0;
            this.chooseTypeExpirationExtend = 1;
            this.checkedShowExpiration = 0;
            this.membershipFormUpdate.charge_limit_prepaid = data.charge_limit_prepaid;
            this.membershipFormUpdate.gr_bus_station_id = data.gr_bus_station_id;

            this.selectedGroupBusStationIDExtend_active = [];
            if(data.gr_bus_station_id != null && data.gr_bus_station_id > 0){
              this.selectedGroupBusStationIDExtend_active.push({
                id: data.gr_bus_station_id,
                text: data['group_busstion_name']
              });
            }
          }
          else if(data['membership_type'].code == 1 ){

            this.typeCardExtend = 1;
            this.chooseTypeExpirationExtend = 2;
            this.checkedShowExpiration = 1

            if(data.actived == -2) this.isCheckExpirationDateExtend = false;

            if(data.actived == 1){
              if(moment(this.current).format('YYYY-MM') <=  moment(this.membershipFormUpdate.expiration_date).format('YYYY-MM')){
                this.isCheckExpirationDateExtend = true;
              }
            }

            if(data.gr_bus_station_id == null) this.chooseSelectedRouteWayExtend = -1;

            if(data.gr_bus_station_id != null && data.gr_bus_station_id > 0){
              this.chooseSelectedRouteWayExtend = 1;
              this.selectedGroupBusStationIDExtend_1 = [];
              this.selectedGroupBusStationIDExtend_active = [];

              this.selectedGroupBusStationIDExtend = data.gr_bus_station_id;
              this.selectedGroupBusStationIDExtend_1 = {
                id: data.gr_bus_station_id+'_'+data.ticket_price_id,
                text: data['group_busstion_name']
              };
              this.selectedGroupBusStationIDExtend_active.push({
                id: data.gr_bus_station_id+'_'+data.ticket_price_id,
                text: data['group_busstion_name']
              });
            }

            if(data.gr_bus_station_id == 0){
              this.chooseSelectedRouteWayExtend = 0;
              if(data['station_data']){

                let stationArr = JSON.parse(data['station_data']);
                this.selectedRouteIDExtend = stationArr[0].route_id;
                if(stationArr.length > 0){
                  // this.reponseBusStationExtend = [stationArr[0].id, stationArr[1].id];
                  stationArr.forEach(e => {
                    this.reponseBusStationExtend.push(e.id);
                    if(this.resultBusStationExtend.length < 2){
                      const object = {
                        route_id: e.route_id,
                        id: e.id,
                        name: e.name,
                        station_order: e.station_order,
                        direction: e.direction,
                        disable: false,
                        lat: e.lat,
                        lng: e.lng,
                        station_relative: e.station_relative
                      };
                      this.resultBusStationExtend.push(object);
                    }
                  });

                  if(this.selectedRouteIDExtend){
                    this.apiRoutes.managerGetRouteById(this.selectedRouteIDExtend).subscribe( data =>{
                      data.bus_stations.forEach(element => {
                        if(element.direction == 0){
                          var indeof = this.reponseBusStationExtend.indexOf(element.id)
                          var tmp = null;
                          if(indeof >= 0){
                            tmp = false;
                          }else{
                            tmp = true;
                          }
                          const object = {
                            route_id: this.selectedRouteIDExtend,
                            id: element.id,
                            name: element.name,
                            station_order: element.station_order,
                            direction: element.direction,
                            disable: tmp,
                            lat: element.lat,
                            lng: element.lng,
                            station_relative: element.station_relative
                          };
                          this.tmpBusStationExtend.push(object);
                        }
                      });
                    });
                  }
                }
              }
            }
          }
        }

        this.spinner.hide();
      }
    );
  }

  extendCardUp() {

    if (this.membershipFormUpdate.fullname === null || this.membershipFormUpdate.fullname === '' ) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_FULLNAME'), 'warning');
      return;
    }

    if ((this.membershipFormUpdate.phone === null || this.membershipFormUpdate.phone === '') && ( this.membershipFormUpdate.cmnd === null || this.membershipFormUpdate.cmnd === '') ) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CMND_OR_PHONE'), 'warning');
      return;
    }

    if(this.membershipFormUpdate.phone){
      if (!this.numberPatten.test(this.membershipFormUpdate.phone) || (this.membershipFormUpdate.phone.length < 10 || this.membershipFormUpdate.phone.length > 11)) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_PHONE'), 'warning');
        return;
      }
    }

    if(this.membershipFormUpdate.email){
      if (!this.emailPattern.test(this.membershipFormUpdate.email)) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_EMAIL'), 'warning');
        return;
      }
    }
    if (this.membershipFormUpdate.cmnd) {
      if (!this.numberPatten.test(this.membershipFormUpdate.cmnd) || this.membershipFormUpdate.cmnd.length != 9) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CMND'), 'warning');
        return;
      }
    }

    if (this.typeImage) {
      if (this.typeImage !== 'image/jpeg' && this.typeImage !== 'image/png' && this.typeImage !== 'image/jpg') {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_TICKET_DESTROY_IMAGE_FORMAT'), 'warning');
        return;
      }
    }

    if (!this.membershipFormUpdate.membershiptype_id) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_CARD_TPE'), 'warning');
      return;
    }


    if( this.typeCardExtend == 0){
      if (!this.membershipFormUpdate.start_expiration_date) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_OPT_DATE_EXP_START'), 'warning');
        return;
      }
      if (!this.membershipFormUpdate.expiration_date) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_OPT_DATE_EXP_END'), 'warning');
        return;
      }

      if(this.membershipFormUpdate.charge_limit_prepaid){
        if (!this.numberPatten.test(this.membershipFormUpdate.charge_limit_prepaid.toString())) {
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CHARGE_LIMIT_PREPAID_FORMAT'), 'warning');
          return;
        }
      }

      if(this.membershipFormUpdate.start_expiration_date && this.membershipFormUpdate.expiration_date){
        let start_date = moment(new Date(this.membershipFormUpdate.start_expiration_date))
        let end_date = moment(new Date(this.membershipFormUpdate.expiration_date))
        let duration = moment.duration(start_date.diff(end_date));
        let checkDuration = Math.round(duration.asDays()*(-1));
        if(checkDuration < 0) {
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_EXP_END_COMPERA_EXP_START'), 'warning');
          return;
        }
      }
    }

    if(this.typeCardExtend == 1){

      if(this.chooseSelectedRouteWayExtend == 1){
        if(this.selectedGroupBusStationIDExtend < 0){
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_OPT_GROUP_BUSSTATION'), 'warning');
          return;
        }
      }

      if(this.chooseSelectedRouteWayExtend == 0){
        if(this.selectedRouteIDExtend < 0){
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_OPT_ROUTE_BUSSTATION'), 'warning');
          return;
        }else{
          if(this.resultBusStationExtend.length != 2){
            swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_OPT_STATION'), 'warning');
            return;
          }
        }
      }

      if(!this.membershipFormUpdate.ticket_price_id){
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_OPT_DENOMINATION'), 'warning');
        return;
      }

      if(this.membershipFormUpdate.avatar == '' || this.membershipFormUpdate.avatar == null){
        if(this.strImageBase64 == ''){
          swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_AVATAR'), 'warning');
          return;
        }
        // swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_TYPE_AVATAR'), 'warning');
        // return;
      }

      if (this.membershipFormUpdate.fullname === '' || this.membershipFormUpdate.fullname === null) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_FULLNAME'), 'warning');
        return;
      }
    }

    //set value property
    let gr_bus_station_id = null;
    let station_data = null;

    if(this.typeCardExtend == 0){
      //handle expiration
      if(this.chooseTypeExpirationExtend == 1){
        this.membershipFormUpdate.expiration_date = moment(this.membershipFormUpdate.expiration_date).format('YYYY-MM-DD 23:59:59');
        this.membershipFormUpdate.start_expiration_date = moment(this.membershipFormUpdate.start_expiration_date).format('YYYY-MM-DD HH:mm:ss');
        let start_date = moment(new Date(this.membershipFormUpdate.start_expiration_date))
        let end_date = moment(new Date(this.membershipFormUpdate.expiration_date))
        let duration = moment.duration(start_date.diff(end_date));
        this.membershipFormUpdate.duration = Math.round(duration.asDays()*(-1));
      }

      //handle station data
      if(this.isModuleCardPrepaidKm)
        if(this.selectedGroupBusStationIDExtend > 0) gr_bus_station_id = this.selectedGroupBusStationIDExtend;
    }

    if(this.typeCardExtend == 1){

      //handle expiration
      this.membershipFormUpdate.expiration_date = moment(this.current).format('YYYY-MM');
      let month = this.membershipFormUpdate.expiration_date.substr(5,2);
      let year = this.membershipFormUpdate.expiration_date.substr(0,4);
      let duration = new Date(parseInt(year), parseInt(month), 0).getDate()
      this.membershipFormUpdate.duration = duration;

      //handle station data
      if(this.chooseSelectedRouteWayExtend == 0){
        gr_bus_station_id = 0;
        if(this.resultBusStationExtend.length > 0){
          station_data =  this.resultBusStationExtend;
        }else{
          station_data = null;
          gr_bus_station_id = null;
        }
      }

      if(this.chooseSelectedRouteWayExtend > 0){
        if(this.selectedGroupBusStationIDExtend > 0){
          gr_bus_station_id = this.selectedGroupBusStationIDExtend;
        }else{
          station_data = null;
          gr_bus_station_id = null;
        }
      }
    }

    this.spinner.show();
    this.apiMembership.managerUpdateMembership({
      id: this.membershipFormUpdate.id,
      membershiptype_id: this.membershipFormUpdate.membershiptype_id,
      fullname: this.membershipFormUpdate.fullname,
      phone: this.membershipFormUpdate.phone,
      email: this.membershipFormUpdate.email,
      address: this.membershipFormUpdate.address,
      birthday: this.membershipFormUpdate.birthday,
      expiration_date: this.membershipFormUpdate.expiration_date,
      start_expiration_date: this.membershipFormUpdate.start_expiration_date,
      duration: this.membershipFormUpdate.duration,
      actived: 1,
      rfidcard_id: this.membershipFormUpdate.rfidcard_id,
      rfid: this.membershipFormUpdate.rfid,
      barcode: '',
      balance: this.membershipFormUpdate.balance,
      type_edit: 2,
      cmnd: this.membershipFormUpdate.cmnd,
      gender: this.membershipFormUpdate.gender,
      avatar: this.strImageBase64,
      gr_bus_station_id: gr_bus_station_id,
      station_data: station_data,
      ticket_price_id: this.membershipFormUpdate.ticket_price_id,
      charge_limit: this.membershipFormUpdate.charge_limit,
      charge_limit_prepaid: this.membershipFormUpdate.charge_limit_prepaid ? parseInt(this.membershipFormUpdate.charge_limit_prepaid.toString()): null
    }).subscribe(
      res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'extend';
        activity_log['subject_type'] = 'membership';
        activity_log['subject_data'] = this.membershipFormUpdate ? JSON.stringify({
          id: this.membershipFormUpdate.id,
          membershiptype_id: this.membershipFormUpdate.membershiptype_id,
          fullname: this.membershipFormUpdate.fullname,
          phone: this.membershipFormUpdate.phone,
          email: this.membershipFormUpdate.email,
          address: this.membershipFormUpdate.address,
          birthday: this.membershipFormUpdate.birthday,
          expiration_date: this.membershipFormUpdate.expiration_date,
          start_expiration_date: this.membershipFormUpdate.start_expiration_date,
          duration: this.membershipFormUpdate.duration,
          actived: 1,
          rfidcard_id: this.membershipFormUpdate.rfidcard_id,
          rfid: this.membershipFormUpdate.rfid,
          balance: this.membershipFormUpdate.balance,
          type_edit: 2,
          cmnd: this.membershipFormUpdate.cmnd,
          gender: this.membershipFormUpdate.gender,
          gr_bus_station_id: gr_bus_station_id,
          station_data: station_data,
          ticket_price_id: this.membershipFormUpdate.ticket_price_id,
          charge_limit: this.membershipFormUpdate.charge_limit ? this.membershipFormUpdate.charge_limit : null,
          charge_limit_prepaid: this.membershipFormUpdate.charge_limit_prepaid ? parseInt(this.membershipFormUpdate.charge_limit_prepaid.toString()): null
        }) : '';
        // var activityLog = this.activityLogs.createActivityLog(activity_log);

        //refesh notifies
        this.appHeaderComponent.getDataNotifies();

        this.extendCard.hide();
        this.membershipFormUpdate = new MembershipForm();
        this.membershipFormUpdate.duration = null;
        this.membershipFormUpdate.expiration_date = null;
        this.membershipFormUpdate.membershiptype_id = null;
        this.membershipFormUpdate.ticket_price_id = null;
        this.selectedGroupBusStationIDExtend = -1;
        this.selectedRouteIDExtend = -1;
        this.chooseSelectedRouteWayExtend = -1;
        this.membershipFormUpdate.charge_limit_prepaid = null;
        this.strImageBase64 = '';

        if(this.input_activated !== ''){
          this.callApiMembeshipByCaseAnhInputAndSearch('activated',this.input_activated, this.search_activated);
        }else{
          if(this.membershipId > 0){
            this.callApiMembeshipByCaseAnhInputAndSearch('activated',this.membershipId, 'id');
          }else{
            this.refreshViewAct();
          }
        }

        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
        this.spinner.hide();
      },
      err => {
        this.spinner.hide();
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
  // ----------------end Function for Extend ------------------------------


  showDetailMembershipModal(rfid: string){
    this.rfID = rfid;
    this.membershipDetailSearch.search_type = 0;
    this.getListMembershipActTransactionCharge();
    this.detailMembershipModel.show();
  }

  getListMembershipActTransactionCharge() {

    this.spinner.show();
    this.apiMembership.managerTransactionGetMembershipDetailByRfidResponse({
      rfid: this.rfID,
      transactionType:  0,
      page: this.currentPageCharges,
      limit: this.limitPageCharges
    }).subscribe(
      res => {
        this.membershipsCharges = res.body['data'];
        this.paginationTotalCharges = res.body['total'];
        this.paginationCurrentCharges = res.body['current_page'];
        this.paginationLastCharges = res.body['last_page'];
        this.spinner.hide();
      }
    );
  }

  pageChargesChanged(event: any): void {
    this.currentPageCharges = event.page;
    this.getListMembershipActTransactionCharge();
  }

  getListMembershipActTransactionDeposit() {
    this.spinner.show();
    this.apiMembership.managerTransactionGetMembershipDetailByRfidResponse({
      rfid: this.rfID,
      transactionType: 1,
      page: this.currentPageDeposits,
      limit: this.limitPageDeposits
    }).subscribe(
      res => {
        this.membershipsDeposits = res.body['data'];
        this.paginationTotalDeposits = res.body['total'];
        this.paginationCurrentDeposits = res.body['current_page'];
        this.paginationLastDeposits = res.body['last_page'];
        this.spinner.hide();
      }
    );
  }

  pageDepositsChanged(event: any): void {
    this.currentPageDeposits = event.page;
    this.getListMembershipActTransactionDeposit();
  }

  detailOnTabChange(event: any) {

    this.membershipDetailSearch = new MembershipDetailSearch;
    this.membershipDetailSearch.search_opt = 0;
    this.membershipDetailSearch.search_date = null;

    if (event.nextId === 'tab-charge') {
      this.getListMembershipActTransactionCharge();
      this.membershipDetailSearch.search_type = 0;
    }
    if (event.nextId === 'tab-deposit') {
      this.getListMembershipActTransactionDeposit();
      this.membershipDetailSearch.search_type = 1;
    }
  }

  callApiMembeshipByCaseAnhInputAndSearch(key_case: string, key_input: string, key_search: string){
    this.spinner.show();
    this.apiMembership.managerListMembershipsByInputAndBySearch({
      key_case,
      key_input,
      key_search
    }).subscribe(data => {
      this.spinner.hide();
      if(key_case == 'activated') this.membershipActs = data;
      if(key_case == 'not_activated') this.memberships = data
    });
  }

  refreshLoadElse(key_case: string){
    if(key_case == 'activated') this.refreshViewAct();
    if(key_case == 'not_activated') this.refreshView();
  }

  changeSelectTypeSearchMemberhsip(type){
    if(type == 0) this.input_not_activated = '';
    //this.getMembershipCardByInput('not_activated');
    if(type == 1) this.input_activated = '';
    //this.getMembershipCardByInput('activated');
  }

  getMembershipCardByInput(key_case: string){

    clearTimeout(this.timeout_search_membership);

    if(key_case == 'activated'){

      if(this.search_activated == ''){
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_SEARCH_ACTIVED'), 'warning');
        return;
      }

      //remove query param
      this.router.navigate([], {queryParams: { subjectId: undefined},queryParamsHandling: 'merge'});

      if(this.input_activated !== '') {
        this.timeout_search_membership = setTimeout(()=>{
          this.callApiMembeshipByCaseAnhInputAndSearch(key_case,this.input_activated, this.search_activated);
        },500);
      }else{
        this.refreshLoadElse(key_case)
      }
    }

    if(key_case == 'not_activated') {

      if(this.search_not_activated == ''){
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_SEARCH_ACTIVED'), 'warning');
        return;
      }

      //remove query param
      this.router.navigate([], {queryParams: { subjectId: undefined},queryParamsHandling: 'merge'});

      if(this.input_not_activated !== '') {
        this.timeout_search_membership = setTimeout(()=>{
          this.callApiMembeshipByCaseAnhInputAndSearch(key_case,this.input_not_activated, this.search_not_activated);
        },500);
      }else{
        this.refreshLoadElse(key_case)
      }
    }
  }

  // ----------------search membership detail ---------------------
  searchMembershipDetail() {
    if (this.membershipDetailSearch.search_opt == 0) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_SELECT_SEARCH_CRITERIA'), 'warning');
      return;
    }
    this.membershipDetailSearch.rfid = this.rfID;
    //this.membershipDetailSearch.search_key = this.rfID;
    this.apiMembership.searchMembershipsDetail(this.membershipDetailSearch).subscribe(data => {
      if (this.membershipDetailSearch.search_type == 0) {
        this.membershipsCharges = data;
        this.paginationTotalCharges = 0;
      }
      if (this.membershipDetailSearch.search_type == 1) {
        this.membershipsDeposits = data;
        this.paginationTotalDeposits = 0;
      }
    });
  }

  searchDateMembershipDetail() {
    this.membershipDetailSearch.rfid = this.rfID;
    //this.membershipDetailSearch.search_key = this.rfID;
    this.apiMembership.searchMembershipsDetail(this.membershipDetailSearch).subscribe(data => {
      if (this.membershipDetailSearch.search_type == 0) {
        this.membershipsCharges = data;
        this.paginationTotalCharges = 0;
      }
      if (this.membershipDetailSearch.search_type == 1) {
        this.membershipsDeposits = data;
        this.paginationTotalDeposits = 0;
      }
    });
  }

  changeSearchOpt() {
    this.membershipDetailSearch.search_key = null;
    this.membershipDetailSearch.search_date = null;

    if (this.membershipDetailSearch.search_opt == 0) {
      this.membershipDetailSearchPlaceholder = "Tìm kiếm...";
    }
    if (this.membershipDetailSearch.search_opt == 1) {
      this.membershipDetailSearchPlaceholder = this.translate.instant('TABLE_MEMBER_DETAIL_STATION');
    }
    if (this.membershipDetailSearch.search_opt == 2) {
      if (this.membershipDetailSearch.search_type == 0) {
        this.membershipDetailSearchPlaceholder = this.translate.instant('TABLE_MEMBER_DETAIL_SELER');
      }
      if (this.membershipDetailSearch.search_type == 1) {
        this.membershipDetailSearchPlaceholder = this.translate.instant('TABLE_MEMBER_DETAIL_PERSON');
      }
    }
    if (this.membershipDetailSearch.search_opt == 3) {
      this.membershipDetailSearchPlaceholder = this.translate.instant('TABLE_MEMBER_DETAIL_DATE_SELE');
    }
  }
  // ----------------end search membership detail ----------------------


  // ------------ Function orther ------------------------------------
  showPrintCard(data: object){

    this.qrcode = data['rfidcard'].barcode;
    // this.seriCard = this.setSeriCardById(data['id']);
    this.seriCard = data['rfidcard'].barcode;

    setTimeout(()=>{
      let printContents, popupWin;
      printContents = document.getElementById('print-section').innerHTML;
      popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
      popupWin.document.open();
      popupWin.document.write(`
        <html>
          <head>
            <title>Print Card</title>
            <style>
              @page{
                size: 638px 1004px landscape;
                margin: 0;
              }
              .seri-card{
                text-align: center;
                font-family: Times, Times New Roman, Georgia, serif;
                font-weight: bold;
                font-size: 13px;
              }
            </style>
          </head>
          <body onload="window.print();window.close()">
            ${printContents}
          </body>
        </html>`
      );
      popupWin.document.close();
    },100);
  }

  showChangeCard(id: number){

    this.changeCardModal.show();
    this.spinner.show();
    this.selectedGenerateBarcode = 1;

    this.apiMembership.managerGetMembershipById(id).subscribe(
      data => {
        // this.membershipFormChange['barcode'] = data['rfidcard'].barcode;
        this.membershipFormChange['seri'] = this.setSeriCardById(data['id']);
        this.membershipFormChange['id'] = data['id'];
        this.membershipFormChange['rfid'] = data['rfidcard'].rfid;
        this.spinner.hide();
      }
    );
  }

  changeCard(){

    if(!this.membershipFormChange['rfid']) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CHANGE_RFID'), 'warning');
      return;
    }

    if(this.membershipFormChange['rfid']) {
      if(this.membershipFormChange['rfid'].length != 8) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CHANGE_RFID_LIMIT'), 'warning');
        return;
      }
    }

    // if(!this.membershipFormChange['barcode']) {
    //   swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_MEM_CARD_CHANGE_BARCODE'), 'warning');
    //   return;
    // }

    // type_choose = 1: sd ma the cu;
    // type_choose= 2 nhap ma the moi

    this.spinner.show();
    this.apiMembership.managerUpdateRfidMembershipById({
      rfid:this.membershipFormChange['rfid'],
      id:this.membershipFormChange['id'],
      // barcode: this.membershipFormChange['barcode'],
      // type_choose: this.selectedGenerateBarcode
    }).subscribe(data => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'change';
        activity_log['subject_type'] = 'membership';
        activity_log['subject_data'] = JSON.stringify({
          rfid:this.membershipFormChange['rfid'],
          id:this.membershipFormChange['id'],
        });
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.membershipFormChange = new MembershipForm;
        this.selectedGenerateBarcode = 1;
        this.changeCardModal.hide();

        if(this.input_activated !== ''){
          this.callApiMembeshipByCaseAnhInputAndSearch('activated',this.input_activated, this.search_activated);
        }else{
          if (this.membershipId > 0) {
            this.callApiMembeshipByCaseAnhInputAndSearch('activated',this.membershipId, 'id');
          }else{
            this.refreshViewAct();
          }
        }

        this.spinner.hide();

        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');

      },err => {
        this.spinner.hide();
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

  showDisableCard(data: object){

    let text = '';
    let actived = 0;
    if(data['actived'] == 1){
      text = this.translate.instant('SWAL_ERROR_DISABLE')
      actived = -1;
    }
    if(data['actived'] == -1){
      text = this.translate.instant('SWAL_ERROR_ENABLE')
      actived = 1;
    }

    swal({
      title: this.translate.instant('SWAL_ERROR_SURE'),
      text: text,
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: this.translate.instant('SWAL_OK'),
      cancelButtonText: this.translate.instant('SWAL_CANCEL')
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.apiMembership.managerUpdateActivedMembershipById({
          id: data['id'],
          actived: actived
        }).subscribe(
          res => {
            //call service create activity log
            var activity_log: any = [];
            activity_log['user_down'] =  this.user_down ? this.user_down : null;
            activity_log['action'] = data['actived'] == 1 ? 'disable' : 'enable';
            activity_log['subject_type'] = 'membership';
            activity_log['subject_data'] = JSON.stringify({
              id: data['id'],
              actived: actived
            });
            var activityLog = this.activityLogs.createActivityLog(activity_log);

            if(this.input_activated !== ''){
              this.callApiMembeshipByCaseAnhInputAndSearch('activated',this.input_activated, this.search_activated);
            }else{
              if (this.membershipId > 0) {
                this.callApiMembeshipByCaseAnhInputAndSearch('activated',this.membershipId, 'id');
              }else{
                this.refreshViewAct();
              }
            }

            this.spinner.hide();
            swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
          },
          err => {
            this.spinner.hide();
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('SWAL_UPDATE_FAILD')});
          }
        );
      }
    });
  }

  setSeriCardById(id: number){

    let strSeriCard = '';
    let seriID = id.toString();
    let testArr = ['0','0','0','0','0','0','0','0','0','0'];
    let check = false;
    var n =  testArr.length - 1;

    for(var i = seriID.length - 1; i >= 0; i--){
      if(check) {
        for(var j = n; j >= 0; j-- ){
          testArr[j] = seriID.charAt(i);
          check = true;
          n = j - 1;
          break;
        }
      }else {
        for(var j = n; j > 0; j-- ){
          testArr[j] = seriID.charAt(i);
          check = true;
          n = j - 1;
          break;
        }
      }
    }
    for(var m = 0; m < testArr.length; m++){
      strSeriCard += testArr[m];
    }

    return strSeriCard;
  }
  // ------------end Function orther ------------------------------------

  showPrintBackGround(data) {

    this.group_station_way  = 'Tất cả';
    this.data_obj = data;
    if(this.data_obj.gr_bus_station_id > 0){
      this.group_station_way = this.data_obj.group_busstion_name;
    }else{
      if(this.data_obj.station_data != null){

        var station_data = JSON.parse(this.data_obj.station_data);

        if(station_data.length > 0){
          var tmp_station = station_data[0].route_id;

          var i = 0;
          var arrTmp = [];
          station_data.forEach(element => {
            if(tmp_station === element.route_id){
              if(i < 1) arrTmp.push(element.name);
              else{
                arrTmp.push(element.name);
              }
              i++;
              tmp_station = element.route_id;
            }
          });
          this.group_station_way  = arrTmp[1]+' - '+arrTmp[0];
        }
      }
    }

    this.backgroundPrintModal.show();
  }

  optTypePrint () {

    if(!this.background_cards){
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('error_option_background_not_fond'), 'warning');
      return;
    }

    this.backgrounds = this.background_cards[this.opt_print];
    this.opt_background = null;
  }

  printCard() {

    if(!this.opt_print) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('error_option_print'), 'warning');
      return;
    }

    if(this.background_cards){
      if(!this.opt_background) {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('error_option_background'), 'warning');
        return;
      }
    }

    if (this.opt_print == 'before') {
      this.showPrintBackGroundBefore(this.data_obj);
    }

    if (this.opt_print == 'after') {
      this.showPrintBackGroundAfter(this.data_obj);
    }
  }

  // ------------ Function orther ------------------------------------
  showPrintBackGroundBefore(data: object){

    this.qrcode = data['rfidcard'].barcode;
    // this.seriCard = this.setSeriCardById(data['id']);
    this.seriCard = data['rfidcard'].barcode;

    setTimeout(()=>{
      let printContents, popupWin;
      printContents = document.getElementById('print-before').innerHTML;
      popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
      popupWin.document.open();
      popupWin.document.write(`
        <html>
          <head>
            <title>Print Card</title>
            <style>
              @page{
                size: 638px 1004px landscape;
                margin: 0;
              }
              .seri-card{
                text-align: center;
                font-family: Times, Times New Roman, Georgia, serif;
                font-weight: bold;
                font-size: 13px;
              }
              body {
                padding: 0px; margin:0px
              }
            </style>
          </head>
          <body onload="window.print();window.close()">
            ${printContents}
          </body>
        </html>`
      );
      popupWin.document.close();
    },100);
  }

  showPrintBackGroundAfter(data: object){

    this.qrcode = data['rfidcard'].barcode;
    // this.seriCard = this.setSeriCardById(data['id']);
    this.seriCard = data['rfidcard'].barcode;

    setTimeout(()=>{
      let printContents, popupWin;
      printContents = document.getElementById('print-after').innerHTML;
      popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
      popupWin.document.open();
      popupWin.document.write(`
        <html>
          <head>
            <title>Print Card</title>
            <style>
              @page{
                size: 638px 1004px landscape;
                margin: 0;
              }
              .seri-card{
                text-align: center;
                font-family: Times, Times New Roman, Georgia, serif;
                font-weight: bold;
                font-size: 13px;
              }
              body {
                padding: 0px; margin:0px
              }
            </style>
          </head>
          <body onload="window.print();window.close()">
            ${printContents}
          </body>
        </html>`
      );
      popupWin.document.close();
    },100);
  }
}
