import { Component } from '@angular/core';
import { QtSocketService } from './shared/qt-socket.service';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  title = 'Smartbus';
  constructor(private qtSocket: QtSocketService, translate: TranslateService) {
    // this language will be used as a fallback when a translation isn't found in the current language
    translate.setDefaultLang('vi');
    // the lang to use, if the lang isn't available, it will use the current loader to get them
    translate.use('vi');
  }
}
