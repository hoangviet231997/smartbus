import { Component, OnInit, ViewChild, AfterViewInit, ElementRef, ɵConsole } from '@angular/core';
import { MouseEvent } from '@agm/core';
import * as moment from 'moment';
import swal from 'sweetalert2';
// import { map } from 'rxjs/operators/map';
import { TranslateService } from '@ngx-translate/core';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { Route } from '../../../../api/models/route';
import { ManagerRoutesService, ManagerModuleCompanyService,ManagerBusStationsService, ManagerTicketTypesService } from '../../../../api/services';
import { Router, ActivatedRoute } from '@angular/router';
import { forEach } from '@angular/router/src/utils/collection';
import { NgxSpinnerService } from 'ngx-spinner';
import { ActivityLogsService } from '../../../../shared/activity-logs.service';

@Component({
  selector: 'app-routes-form',
  templateUrl: './routes-form.component.html',
  styleUrls: ['./routes-form.component.css']
})

export class RoutesFormComponent implements OnInit, AfterViewInit {

  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('myInputFile') myInputVariable: ElementRef;
  public route = {
    id: 0,
    name: '',
    number: '',
    start_time: '',
    end_time: '',
    bus_stations:[],
    modules: [],
    tickets: [],
    distance_scan: null,
    timeout_sound: null
  };
  public bus_station = {
    id: 0,
    name: '',
    address: '',
    lat: 0,
    lng: 0,
    station_order: 0,
    url_sound: '',
    str_audio_base64: '',
    direction: 0,
    distance: 0,
    group_key: null,
    station_relative: [],
    isCheck_urlSound: 0
  };
  public updateRoute: Route;
  public start_time = this.setTimes(8, 0);
  public end_time = this.setTimes(17, 0);

  public modules: any = [];
  public tickets: any = [];
  public module_arr: any = [];
  public ticket_arr: any = [];
  // public groupBusStations: any = [];
  // public selectedGroupBusStationKey: any = '';

  public markers = [];
  public index = -1;
  public zoom = 12;
  public lat = 10.854477;
  public lng = 106.626246;

  public isMeridian = false;
  public isEditMarker = false;
  public isEditRoute = false;
  public isShowMarker = false;
  public isCheckDirectionInTurn = true;
  public isCheckDirectionTurnOn = false;
  public statusDistance =  null;
  public isCheckRemoveUrlSound = false;

  public module_companies: any = [];
  public tickeTypes: any = [];
  public selectedModuleApp: any = [];
  public numberPatten = /^-?[\d.]+(?:e-?\d+)?$/;

  public typeAudio: any = '';
  public sizeAudio: any = 0;
  public nameAudio: any = '';
  public isUrlSound = false ;
  public str_audio_base64:any = '' ;

  public routes: any = [];
  public tmpBusStation: any = [];

  public audio = new Audio();
  public isPlaying = 1;

  public user_down: any = null;

  constructor(
    private apiRoutes: ManagerRoutesService,
    private apiModuleCompany: ManagerModuleCompanyService,
    private apiTicketTypes: ManagerTicketTypesService,
    // private apiGroupBusStations: ManagerBusStationsService,
    private activityLogs: ActivityLogsService,
    private router: Router,
    private routeActive: ActivatedRoute,
    private translate: TranslateService,
    private spinner: NgxSpinnerService,
  ) { }

  ngOnInit() {

    this.user_down = localStorage.getItem('token_shadow');
    this.lat = JSON.parse(localStorage.getItem('user')).company['position'].coordinates[1];
    this.lng = JSON.parse(localStorage.getItem('user')).company['position'].coordinates[0];

    this.routeActive.queryParams.subscribe( params => {
      params.idRoute !== undefined && this.apiRoutes.managerGetRouteById(params.idRoute)
        .subscribe(res => {

          this.isEditRoute = true;
          this.updateRoute = res;
          const { id, name, number, start_time, end_time, bus_stations, module_data, ticket_data, distance_scan, timeout_sound} = res;
          this.route.name = name;
          this.route.number = number;
          this.route.start_time = start_time;
          this.route.end_time = end_time;
          if(distance_scan != null) this.route.distance_scan = distance_scan/1000;
          if(timeout_sound != null) this.route.timeout_sound = timeout_sound/1000;
          this.module_arr = JSON.parse(module_data);

          if(ticket_data !== null){
            this.ticket_arr = JSON.parse(ticket_data);
          }

          if(this.module_arr.length > 0){
            this.modules = this.module_arr;
          }

          if(this.ticket_arr.length > 0){
            this.tickets = this.ticket_arr;
          }

          if(bus_stations === undefined) {

            this.route.bus_stations = [];
            this.markers = [];
          }else {

            this.route.bus_stations = bus_stations;
            this.markers = bus_stations;
            this.route.bus_stations.map(e => {
              if(e.distance !== undefined && e.distance !== null){
                e.distance = e.distance/1000;
              }
              if(e.station_relative != null){
                e.station_relative = JSON.parse(e.station_relative);
              }else{
                e.station_relative = [];
              }
            });

            let lat: number = 0;
            let lng: number = 0;

            bus_stations.forEach(marker => {
                lat += marker.lat;
                lng += marker.lng;
            });

            this.lat = lat/bus_stations.length;
            this.lng = lng/bus_stations.length;
            this.zoom = 10;
          }

          this.start_time = this.setTimes(res.start_time.substr(0, 2), res.start_time.substr(3, 2));
          this.end_time = this.setTimes(res.end_time.substr(0, 2), res.end_time.substr(3, 2));

        });
    });
  }

  ngAfterViewInit() {

    this.isPlaying = 1;
    this.getDataModuleApp();
    // this.getGroupBusStations();
    this.getDataTicketType();
    this.getDataRouteBusStation();
  }

  getDataModuleApp(){
    this.spinner.show();
    this.apiModuleCompany.listModuleCompany().subscribe(data => {
      this.module_companies = data;
      this.spinner.hide();
    });
  }

  getDataTicketType(){
    this.spinner.show();
    this.apiTicketTypes.managerListTicketTypesByTypeParam(0).subscribe(data => {
      this.tickeTypes = data;
      this.spinner.hide();
    });
  }

  // getGroupBusStations(){
  //   this.apiGroupBusStations.managerlistGroupBusStation().subscribe(data => {
  //     this.groupBusStations = data;
  //   })
  // }

  getDataRouteBusStation(){
    //get routes
    this.spinner.show();
    this.apiRoutes.managerGetRoutesBusStions().subscribe(
      resp => {
        this.routes = resp;
        this.routes.map(element => {
          var arr = [];
          element.bus_stations.forEach(e => {
            if(e.direction == 0){
              arr.push(e);
            }
          });
          element.bus_stations = arr;
        });
        this.spinner.hide();
      }
    );
  }

  onModuleChange(event, module_id){

    if (event.currentTarget.checked) {

      this.modules.push(module_id);

    }else{

      const index: number = this.modules.indexOf(module_id);

      if (index !== -1) {
        this.modules.splice(index, 1);
      }
    }
  }

  changeCheckedTicketPricew(event, ticket_type_id){

    if (event.currentTarget.checked) {

      this.tickets.push(ticket_type_id);

    }else{

      const index: number = this.tickets.indexOf(ticket_type_id);

      if (index !== -1) {
        this.tickets.splice(index, 1);
      }
    }
  }

  saveRoute() {

    if (this.route.name === '') {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_ROU_NAME'), 'warning');
      return;
    }
    if (this.route.number == '') {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_ROU_NUMBER'), 'warning');
      return;
    }
    if( (moment(this.start_time).format('HH:mm:ss').substr(0, 2)) > "23"
      || (moment(this.end_time).format('HH:mm:ss').substr(0, 2)) > "23"
      || (moment(this.start_time).format('HH:mm:ss').substr(0, 2)) < "0"
      || (moment(this.end_time).format('HH:mm:ss').substr(0, 2)) < "0"
      || (moment(this.start_time).format('HH:mm:ss').substr(3, 2)) > "59"
      || (moment(this.end_time).format('HH:mm:ss').substr(3, 2)) > "59"
      || (moment(this.start_time).format('HH:mm:ss').substr(3, 2)) < "0"
      || (moment(this.start_time).format('HH:mm:ss').substr(3, 2)) < "0"){
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_ROU_DATE_FORMAT'), 'warning');
      return;
    }
    if( (moment(this.start_time).format('HH:mm:ss')) === undefined
      || (moment(this.end_time).format('HH:mm:ss')) === undefined){
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_ROU_DATE'), 'warning');
      return;
    }
    if((moment(this.start_time).format('HH:mm:ss').substr(0, 2)) === (moment(this.end_time).format('HH:mm:ss').substr(0, 2))){
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_ROU_DATE_FORMAT_NOT_PRACTICAL'), 'warning');
      return;
    }

    if (this.modules.length <= 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_ROU_MODULE_APP'), 'warning');
      return;
    }

    this.route.start_time = moment(this.start_time).format('HH:mm:ss');
    this.route.end_time = moment(this.end_time).format('HH:mm:ss');
    this.route.modules = this.modules;
    this.route.tickets = this.tickets;

    this.route.bus_stations.sort(function(a, b) { return a.station_order - b.station_order; });

    if(this.isEditRoute) {
      this.route.id = this.updateRoute.id;
      this.spinner.show();
      this.apiRoutes.managerUpdateRoute(this.route).subscribe(() => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'update';
        activity_log['subject_type'] = 'route_bus_station';
        this.route.bus_stations.map(element => {
          delete element.str_audio_base64;
        });
        activity_log['subject_data'] = this.route ? JSON.stringify(this.route) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.router.navigate(['/manager/routes/routes-list']);
        this.spinner.hide();
      },
      err => {
          // console.log(err);
          this.spinner.hide();
          swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
      });
    }else {
      this.spinner.show();
      this.apiRoutes.manmagerCreateRoute(this.route).subscribe(res => {

        //call service create activity log
        var activity_log: any = [];
        activity_log['user_down'] =  this.user_down ? this.user_down : null;
        activity_log['action'] = 'create';
        activity_log['subject_type'] = 'route_bus_station';
        this.route.bus_stations.map(element => {
          delete element.str_audio_base64;
        });
        activity_log['subject_data'] = this.route ? JSON.stringify(this.route) : '';
        var activityLog = this.activityLogs.createActivityLog(activity_log);

        this.router.navigate(['/manager/routes/routes-list']);
        this.spinner.hide();
      }, err => {
          // console.log(err);
            this.spinner.hide();
            swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error});
      });
    }
  }

  mapClicked($event: MouseEvent) {

    this.statusDistance = 0;

    this.bus_station.name = '';
    this.bus_station.address = '';
    this.bus_station.station_order = null;
    // this.selectedGroupBusStationKey = null;
    this.bus_station.url_sound = null;
    this.bus_station.isCheck_urlSound = 0;
    // this.str_audio_base64 = '';
    this.bus_station.direction = 0;
    this.bus_station.distance = null;
    this.bus_station.station_relative = [];
    this.isCheckDirectionInTurn = true;
    this.isCheckDirectionTurnOn = false;
    this.isCheckRemoveUrlSound = false;
    this.index = this.markers.length;
    this.markers.push({lat: $event.coords.lat, lng: $event.coords.lng});
    this.route.bus_stations[this.index] = {
      id: 0,
      name: '',
      address: '',
      station_order: null,
      url_sound: null,
      str_audio_base64: '',
      direction: 0,
      distance: 0,
      group_key: null,
      isCheck_urlSound: 0,
      station_relative: [],
      lat: $event.coords.lat,
      lng: $event.coords.lng
    };
    this.addModal.show();
  }

  changeCheckDirection(value){

    if(value === 0){
      this.isCheckDirectionInTurn = true;
      this.isCheckDirectionTurnOn = false;
    }

    if(value === 1){
      this.isCheckDirectionTurnOn = true;
      this.isCheckDirectionInTurn = false;
    }

    this.bus_station.direction = value;
  }

  clickedMarker(i) {

    this.str_audio_base64 = '';
    this.statusDistance = null;
    this.isUrlSound = false;
    this.isEditMarker = true;
    this.index = i;
    this.bus_station.name = this.route.bus_stations[i].name;
    this.bus_station.address = this.route.bus_stations[i].address;
    this.bus_station.station_order = this.route.bus_stations[i].station_order;
    this.bus_station.url_sound = this.route.bus_stations[i].url_sound;
    this.bus_station.direction = this.route.bus_stations[i].direction;
    this.bus_station.distance = this.route.bus_stations[i].distance;
    this.bus_station.station_relative = this.route.bus_stations[i].station_relative ? this.route.bus_stations[i].station_relative : [];

    this.bus_station.isCheck_urlSound = 0;
    this.isCheckRemoveUrlSound = false;

    // this.selectedGroupBusStationKey = this.route.bus_stations[i].group_key;
    if(this.bus_station.direction === 0){
      this.isCheckDirectionInTurn = true;
      this.isCheckDirectionTurnOn = false;
    }
    if(this.bus_station.direction === 1){
      this.isCheckDirectionTurnOn = true;
      this.isCheckDirectionInTurn = false;
    }
    this.bus_station.lat = this.route.bus_stations[i].lat;
    this.bus_station.lng = this.route.bus_stations[i].lng;

    if(this.bus_station.url_sound !== null){
      this.isUrlSound = true;
    }
    this.addModal.show();
  }

  delMarker(i) {

    if(this.isEditRoute && this.route.bus_stations[i].id > 0) {
      this.markers[i] = {
        id: this.route.bus_stations[i].id*(-1),
        url_sound: this.route.bus_stations[i].url_sound,
        direction: this.route.bus_stations[i].direction,
        station_relative: this.route.bus_stations[i].station_relative,
        distance: this.route.bus_stations[i].distance,
        station_order: this.route.bus_stations[i].station_order,
        statusDistance: null
      };
    }else {
      this.markers.splice(i, 1);
      this.route.bus_stations.splice(i,1)
    }
    this.addModal.hide();
  }

  hideMarket(){
    this.markers.splice(this.index, 1);
    this.route.bus_stations.splice(this.index,1)
    this.addModal.hide();
  }

  onFileAudioChange($event) : void {
    this.eventConvertBase64($event.target);
  }

  eventConvertBase64(inputValue: any): void {

    var file:File = inputValue.files[0];
    var myReader:FileReader = new FileReader();
    myReader.onloadend = (e) => {
      this.str_audio_base64 = myReader.result;
      this.typeAudio =  file.type;
      this.sizeAudio =  file.size;
      this.nameAudio = file.name;
    }
    myReader.readAsDataURL(file);
  }

  addStation() {

    this.route.bus_stations[this.index].name =   this.bus_station.name;
    this.route.bus_stations[this.index].address =   this.bus_station.address;
    this.route.bus_stations[this.index].station_order = this.bus_station.station_order;
    this.route.bus_stations[this.index].str_audio_base64 = this.str_audio_base64 ;
    this.route.bus_stations[this.index].direction = this.bus_station.direction ;
    this.route.bus_stations[this.index].distance = this.bus_station.distance ?  this.bus_station.distance : null;
    this.route.bus_stations[this.index].statusDistance = this.statusDistance;
    this.route.bus_stations[this.index].station_relative = this.bus_station.station_relative;
    this.route.bus_stations[this.index].isCheck_urlSound = this.bus_station.isCheck_urlSound;

    if ( this.route.bus_stations[this.index].name === '') {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_BUS_STATION_NAME'), 'warning');
      return;
    }

    if ( this.route.bus_stations[this.index].station_order === '') {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_BUS_STATION_ORDER'), 'warning');
      return;
    }

    if ( this.route.bus_stations[this.index].station_order <= 0) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_BUS_STATION_ORDER_BIGGER'), 'warning');
      return;
    }

    if (!this.numberPatten.test(this.route.bus_stations[this.index].station_order)) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_BUS_STATION_ORDER_FORMAT'), 'warning');
      return;
    }

    if(this.statusDistance == 1){

      if (this.route.bus_stations[this.index].distance === null) {
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_BUS_DISTANCE'), 'warning');
        return;
      }

      if(this.route.bus_stations[this.index].distance){

        if(parseFloat(this.route.bus_stations[this.index].distance) < 0){
          swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_BUS_DISTANCE_FORMAT'), 'warning');
          return;
        }

        if (!this.numberPatten.test(this.route.bus_stations[this.index].distance)) {
          swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_BUS_DISTANCE_FORMAT'), 'warning');
          return;
        }
      }
    }

    if(this.nameAudio){
      if(this.typeAudio !== 'audio/mp3'){
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_BUS_STATION_FORMAT_MP3'), 'warning');
        return;
      }

      if(this.sizeAudio > 252000 ){
        swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERR_BUS_STATION_FORMAT_SIZE_LIMIT'), 'warning');
        return;
      }
    }

    this.index = -1;
    this.addModal.hide();
    this.myInputVariable.nativeElement.value = "";
    this.str_audio_base64 = '';
  }

  markerDragEnd(i, $event: MouseEvent) {

    const { lat, lng } = $event.coords;
    this.markers[i].lat = lat;
    this.markers[i].lng = lng;
    this.route.bus_stations[i].lat = lat;
    this.route.bus_stations[i].lng = lng;
    // this.markers.splice(i, 1);
  }

  setTimes(h, m) {
    const d = new Date();
    d.setHours(h);
    d.setMinutes(m);
    return d;
  }

  changeCheckedBusStation(event, busStation){
    if (event.currentTarget.checked){
      this.bus_station.station_relative.push(busStation.id);
    }else{
      const index: number = this.bus_station.station_relative.indexOf(busStation.id);
      if (index !== -1) {
        this.bus_station.station_relative.splice(index, 1);
      }
    }
  }

  playAudio(url_sound) {

    this.audio.src = "../audio/bus-stations/"+url_sound;
    this.audio.load();
    if (this.isPlaying % 2 !== 0) {
      this.audio.play();
      this.isPlaying++;
    } else {
      this.audio.pause();
      this.isPlaying++;

    }
  }

  checkRemoveUrlSoundBusStation(event){

    if (event.currentTarget.checked){
      this.bus_station.isCheck_urlSound = 1;
    }else{
      this.bus_station.isCheck_urlSound = 0;
    }
  }
}
