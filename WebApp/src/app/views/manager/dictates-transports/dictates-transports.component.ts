import { Component, OnInit, ViewChild, Pipe, PipeTransform } from '@angular/core';
import * as moment from 'moment';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { ManagerCompaniesService, ManagerVehiclesService, ManagerRoutesService, ManagerUsersService } from '../../../api/services';
import { User } from 'src/app/api/models';
import { transliterate as tr, slugify } from 'transliteration';

@Pipe({
  name: 'filterUser'
})
export class FilterUserPipe implements PipeTransform {

  public transform(arrayUsers: User[], filter: string): any[] {
    if (!arrayUsers || !arrayUsers.length) {
      return [];
    }
    if (!filter) {
      return arrayUsers;
    }
    return arrayUsers.filter(user => {
      return tr(user.fullname).toLowerCase().indexOf(tr(filter).toLowerCase()) >= 0;
    });
  }

}

@Component({
  selector: 'app-dictates-transports',
  templateUrl: './dictates-transports.component.html',
  styleUrls: ['./dictates-transports.component.css']
})
export class DictatesTransportsComponent implements OnInit {
  @ViewChild('listVehical') public listVehical: ModalDirective;
  @ViewChild('listUserModal') public listUserModal: ModalDirective;

  public company: any;
  public maxDate: Date;
  public bsRangeValue: any;
  public licensePlatesInput: any;

  public daysForm: string;
  public monthForm: string;
  public yearsForm: string;
  public daysTo: string;
  public monthTo: string;
  public yearsTo: string;

  public vehicles: any;
  public vehicle_id: number = 0;
  public routes: any;
  public route_name: string = '';
  public route_id:number = 0;

  public bus_stations: any;
  public searchDriverName: any;
  public searchSubDriverName:any;
  public user_id: number;
  public inputUserName: any;
  public users: any;

  public permissions:any = [];

  constructor(
    private apiCompanies: ManagerCompaniesService,
    private apiVehicles: ManagerVehiclesService,
    private apiRoutes: ManagerRoutesService,
    private apiUsers: ManagerUsersService
  ) {
    this.maxDate = new Date();
  }

  ngOnInit() {

    if (localStorage.getItem('user')) {
      this.permissions = JSON.parse(localStorage.getItem('user')).permissions;
    }

    this.getComapny();
    this.getlistVehicleAll();
    this.getRoutes();
  }

  getComapny() {
    this.apiCompanies.managerGetCompany().subscribe(
      data => {
        this.company = data;
      }
    );
  }

  getData() {
    this.daysForm = moment(this.bsRangeValue).format('DD').toString();
    this.monthForm = moment(this.bsRangeValue).format('MM').toString();
    this.yearsForm = moment(this.bsRangeValue).format('YYYY').toString();

    this.daysTo = moment(this.bsRangeValue).format('DD').toString();
    this.monthTo = moment(this.bsRangeValue).format('MM').toString();
    this.yearsTo = moment(this.bsRangeValue).format('YYYY').toString();
  }

  showListVehicleModal() {
    this.getlistVehicleAll();
    this.listVehical.show();
  }

  getlistVehicleAll() {
    this.apiVehicles.getlistVehicleAllResponse().subscribe(
      res => {
        this.vehicles = res.body;
      }
    );
  }

  chooseVehicle(vehicel_id) {
    if (vehicel_id == 0) {
      this.vehicle_id = vehicel_id;
    } else {
      this.vehicles.map(
        (vehicle) => {
          if (vehicle.id == vehicel_id) {
            this.licensePlatesInput = vehicle.license_plates;
            this.vehicle_id = vehicle.id;
          }
        });
    }
    this.listVehical.hide();
    this.getData();
  }

  getRoutes() {
    this.apiRoutes.managerlistRoutesResponse({
      page: 1,
      limit: 99999
    }).subscribe(
      resp => {
        this.routes = resp.body;
      }
    );
  }

  changeSelectRoute() {
    this.apiRoutes.managerGetRouteById(this.route_id).subscribe( data => {
      this.route_name = data.name;
      this.bus_stations = data.bus_stations;
    });
  }

  showListUserModal() {
    this.listUserModal.show();
    this.getUsers();
  }

  getUsers() {
    this.apiUsers.managerListAllUser()
    .subscribe(
      resp => {
        this.inputUserName = '';
        this.users = resp.filter(
          (user) => {
            if (user.role.name === 'driver' || user.role.name === 'subdriver') {
              return user;
            }
        });
      }
    );
  }

  chooseUser(user_id) {
    if (user_id == 0) {
      this.user_id = user_id;
    } else {
      this.users.map(
        (item) => {
          if (item.id == user_id) {
            switch (item.role_id) {
              case 4:
                this.searchDriverName = item.fullname;
                break;
              case 5:
                this.searchSubDriverName = item.fullname;
                break;
              default:
                break;
            }
          }
        });
    }
    if(this.searchDriverName && this.searchSubDriverName) {
      this.listUserModal.hide();
    }
  }

  showPrintPreview() {
    let printContents, popupWin;
    printContents = document.getElementById('print-section').innerHTML;
    popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
    popupWin.document.open();
    popupWin.document.write(`
      <html>
        <head>
          <title></title>
          <style>
          @page { size: A4; }
          .tx-center{text-align: center}
          .tx-right{text-align: right}
          .tx-left{text-align: left}
          .tx-bold{font-weight: bolder;}
          .tx-10{font-size: 15px; font-family: 'Times New Roman';}
          .tx-11{font-size: 12px; font-family: 'Times New Roman';}
          .tx-12{font-size: 20px; font-family: 'Times New Roman';}
          .w-10{width: 10cm}
          .w-3{width: 3cm;float:left}
          .fl{float:left}
          .fr{float:right}
          .w-4{width: 4cm}
          .w-2{width: 1.5cm}
          .pt-0{margin-top: 0}
          </style>
          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        </head>
        <body style="font-family: 'Times New Roman';font-size: 23px;"
            onload="window.print();window.close()">`+ printContents + `
            <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        </body>
      </html>`
    );
    popupWin.document.close();
  }

}
