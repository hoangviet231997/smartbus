import { Component, OnInit, ViewChild } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import swal from 'sweetalert2';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { HttpErrorResponse } from '@angular/common/http';
import { ManagerCateNewsService } from "../../../../api/services/manager-cate-news.service";
import { CategoryNewsForm } from 'src/app/api/models';


@Component({
  selector: 'app-category-news',
  templateUrl: './category-news.component.html',
  styleUrls: ['./category-news.component.css']
})
export class CategoryNewsComponent implements OnInit {
  @ViewChild('modalAddCateNews') public modalAddCateNews: ModalDirective;
  @ViewChild('modalEditCateNews') public modalEditCateNews: ModalDirective;

  public createCateNews: CategoryNewsForm;
  public updateCateNews: CategoryNewsForm;

  public cate_news: any;

  constructor(
    private apiCateNews: ManagerCateNewsService,
    private translate: TranslateService
  ) {
    this.createCateNews = new CategoryNewsForm();
    this.updateCateNews = new CategoryNewsForm();
  }

  ngOnInit() {
    this.getDataCateNews();
  }

  getDataCateNews() {
    this.apiCateNews.managerListCategoryNews().subscribe((data) => {
      this.cate_news = data;
    });
  }

  showModalAddCategoryNews() {
    this.modalAddCateNews.show();
    this.createCateNews = new CategoryNewsForm();
  }

  addCateNews() {

    if (!this.createCateNews.name || this.createCateNews.name === '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_CATE_NEWS_NAME'), 'warning');
      return;
    }

    if (!this.createCateNews.weigth) {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_CATE_NEWS_STT'), 'warning');
      return;
    }

    this.apiCateNews.managerCreateCategoryNews({
      name: this.createCateNews.name,
      description: this.createCateNews.description,
      weigth: this.createCateNews.weigth,
    }).subscribe((resp) => {
      this.modalAddCateNews.hide();
      swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
      this.getDataCateNews();
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

  showModalEditCategoryNews(id: number) {
    this.modalEditCateNews.show();
    this.updateCateNews.id = id;
    this.apiCateNews.managerGetCategoryNewsById(id).subscribe((resp) => {
      this.updateCateNews.name = resp.name;
      this.updateCateNews.description = resp.description;
      this.updateCateNews.weigth = resp.weigth;
    });
  }

  editCateNews() {
    this.apiCateNews.managerUpdateCategoryNews({
      id: this.updateCateNews.id,
      name: this.updateCateNews.name,
      description: this.updateCateNews.description,
      weigth: this.updateCateNews.weigth
    }).subscribe((resp) => {
      this.modalEditCateNews.hide();
      swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
      this.getDataCateNews();
    },
      (err) => {
        if (err instanceof HttpErrorResponse) {
          if (err.status == 404) {
            swal({ type: 'error', title: this.translate.instant('SWAL_ERROR'), text: err.error });
          }
        }
      });
  }

  deleteCateNews(id: number) {
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
        this.apiCateNews.managerDeleteCategoryNews(id).subscribe(() => {
          swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
          this.getDataCateNews();
        });
      }
    });
  }

}
