import { Component, OnInit } from '@angular/core';
import { AdminGeneralSettingService } from '../../../api/services';
import { deCryptoForm} from '../../../api/models';
import swal from 'sweetalert2';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-decode-onlines',
  templateUrl: './decode-onlines.component.html',
  styleUrls: ['./decode-onlines.component.css']
})
export class DecodeOnlinesComponent implements OnInit {

  public dataResultObj: any;
  public deCryptoForm:  deCryptoForm;
  constructor(
    private apideCrypto: AdminGeneralSettingService,
    private translate: TranslateService
  ) { 
    this.deCryptoForm = new deCryptoForm();
  }

  ngOnInit() {
  }

  deCrypto(){

    this.apideCrypto.deCryptoOnline({
      data: this.deCryptoForm.data,
      key: this.deCryptoForm.key
    }).subscribe(data => {
      this.dataResultObj = JSON.parse(data)
    }, err => {
      swal({type: 'error', title: this.translate.instant('SWAL_ERROR'), text: this.translate.instant('Vui long nhap du lieu')});
    },);
  }
}
