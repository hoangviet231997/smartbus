import { Component, OnInit, AfterViewInit} from '@angular/core';
import 'rxjs/add/operator/filter';
import * as io from 'socket.io-client';
import swal from 'sweetalert2';
import { TranslateService } from '@ngx-translate/core';
import { ManagerCompaniesService } from '../../../api/services';

@Component({
  selector: 'app-sub-dashboard',
  templateUrl: './sub-dashboard.component.html',
  styleUrls: ['./sub-dashboard.component.css']
})
export class SubDashboardComponent implements OnInit {

  public company: any = {
    fullname: '',
    address: '',
    phone: ''
  };
  public zoom = 12;
  public latitude = 12.6496222;
  public lngitude = 104.3004339;
  public fullname = ''
  public mapStyles: any = [
    {
      featureType: 'poi.business',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.government',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.attraction',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.medical',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.place_of_worship',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.school',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'poi.sports_complex',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'transit',
      elementType: 'labels.icon',
      stylers: [{ visibility: 'off' }]
    }
  ];
  public infoWindowOpened = null;

  constructor(  private apiCompanies: ManagerCompaniesService) {}

  ngOnInit() {
    this.fullname = JSON.parse(localStorage.getItem('user')).fullname;
    this.apiCompanies.managerGetCompany().subscribe(data => {
      this.company = data;
      this.latitude = data['position'].coordinates[1];
      this.lngitude = data['position'].coordinates[0];
    });
  }

  clickedMarkerCompany(infoWindowCompany){

    if (this.infoWindowOpened === infoWindowCompany) return;

    if (this.infoWindowOpened !== null) this.infoWindowOpened.close();

    this.infoWindowOpened = infoWindowCompany;
  }
}
