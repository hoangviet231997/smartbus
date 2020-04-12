import { Component, OnInit, ViewChild, AfterViewInit, ViewEncapsulation } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import swal from 'sweetalert2';
import { ModalDirective } from 'ngx-bootstrap/modal';
// import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { NewsForm } from 'src/app/api/models';
import { ManagerNewsService } from "../../../../api/services/manager-news.service";
import { HttpErrorResponse } from '@angular/common/http';
import { ManagerCateNewsService } from "../../../../api/services/manager-cate-news.service";


@Component({
  selector: 'app-news',
  templateUrl: './news.component.html',
  styleUrls: ['./news.component.css'],
  encapsulation: ViewEncapsulation.None
})

export class NewsComponent implements OnInit, AfterViewInit {
  @ViewChild('modalAddNews') public modalAddNews: ModalDirective;
  @ViewChild('modalEditNews') public modalEditNews: ModalDirective;

  // public Editor = ClassicEditor;
  // public content: any;
  public create_news: NewsForm;
  public update_news: NewsForm;

  public news: any;
  public strImageBase64: any;
  public typeImage: any;


  public valueNews: any = {};
  public items: any;
  public valueActive: any = [];


  constructor(
    private translate: TranslateService,
    private apiNews: ManagerNewsService,
    private apiCateNews: ManagerCateNewsService
  ) {
    this.create_news = new NewsForm();
    this.update_news = new NewsForm();
    this.valueActive = [];

  }

  ngOnInit() {
  }

  ngAfterViewInit() {
    this.getDataNews();
    this.getDataCateNews();
  }

  getDataNews() {
    this.apiNews.managerListNews().subscribe((data) => {
      this.news = data;
    });
  }

  getDataCateNews() {
    this.apiCateNews.managerListCategoryNews().subscribe((resp) => {
      this.items = [];
      for (let i = 0; i < resp.length; i++) {
        this.items.push({
          id: resp[i]['id'],
          text: resp[i]['name']
        });
      }
    });
  }

  showModalAddNews() {
    this.modalAddNews.show();
    this.create_news = new NewsForm();
    this.strImageBase64 = '';
    this.valueActive = [];
    // this.content = '';
  }

  eventConvertBase64(inputValue: any): void {
    var file: File = inputValue.files[0];
    var myReader: FileReader = new FileReader();
    myReader.onloadend = (e) => {
      this.strImageBase64 = myReader.result;
      this.typeImage = file.type;
    }
    myReader.readAsDataURL(file);
  }

  onFileImageChange($event) {
    this.eventConvertBase64($event.target);
  }

  createNews($event) {

    if (this.typeImage) {
      if (this.typeImage !== 'image/jpeg' && this.typeImage !== 'image/png' && this.typeImage !== 'image/jpg') {
        swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NEWS_IMAGE_FORMAT'), 'warning');
        return;
      }
    }

    if (!this.create_news.category_id) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NEWS_TYPE'), 'warning');
      return;
    }

    if (!this.create_news.name || this.create_news.name === '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NEWS_NAME'), 'warning');
      return;
    }

    if (!this.strImageBase64 || this.strImageBase64 === '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NEWS_AVATAR'), 'warning');
      return;
    }

    if (!this.create_news.weigth) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NEWS_STT'), 'warning');
      return;
    }

    if (!this.create_news.description || this.create_news.description === '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NEWS_DESCRIPTION'), 'warning');
      return;
    }

    if (this.create_news.description.length >= 256) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NEWS_DESCRIPTION_256'), 'warning');
      return;
    }

    this.apiNews.managerCreateNews({
      category_id: this.create_news.category_id,
      name: this.create_news.name,
      url_img: this.strImageBase64,
      description: this.create_news.description,
      content: this.create_news.content,
      weigth: this.create_news.weigth
    }).subscribe((resp) => {
      this.modalAddNews.hide();
      swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      this.getDataNews();
    },
      (err) => {
        if (err instanceof HttpErrorResponse) {
          if (err.status == 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          }
        }
      }
    );
  }

  showModalEditNews(id: number) {
    this.modalEditNews.show();
    this.strImageBase64 = '';
    this.apiNews.managerGetNewsById(id).subscribe((resp) => {
      this.update_news.category_id = resp.category_id;
      this.update_news.content = resp.content;
      this.update_news.description = resp.description;
      this.update_news.id = resp.id;
      this.update_news.name = resp.name;
      this.update_news.url_img = resp.url_img;
      this.update_news.weigth = resp.weigth;
      this.valueActive = [];
      this.valueActive.push({
          id: resp.category_id,
          text: resp['category_news_name']
      });
    });
  }

  updateNews($event) {

    if (this.update_news.description.length >= 256) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_NEWS_DESCRIPTION_256'), 'warning');
      return;
    }

    this.apiNews.managerUpdateNews({
      id: this.update_news.id,
      category_id: this.update_news.category_id,
      name: this.update_news.name,
      url_img: this.strImageBase64 ? this.strImageBase64 : '',
      description: this.update_news.description,
      content: this.update_news.content,
      weigth: this.update_news.weigth
    }).subscribe((resp) => {
      this.modalEditNews.hide();
      swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      this.getDataNews();
    },
      (err) => {
        if (err instanceof HttpErrorResponse) {
          if (err.status == 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          }
        }
      }
    );
  }

  deleteNews(id: number) {
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
        this.apiNews.managerDeleteNews(id).subscribe((resp) => {
          swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          this.getDataNews();
        });
      }
    });
  }


  public selectedNews(value: any): void {
    this.create_news.category_id = value.id;
    this.update_news.category_id = value.id;
  }

  public removedNews(value: any): void {

  }

  public typedNews(value: any): void {
  }

  public refreshValueNews(value: any): void {
    this.valueNews = value;
  }

}
