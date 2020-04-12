import { Component, OnInit, ViewChild, AfterViewInit, Pipe } from '@angular/core';
import { AdminCategoriesService } from '../../../../api/services';
import { TranslateService } from '@ngx-translate/core';
import { NgxSpinnerService } from 'ngx-spinner';
import { from } from 'rxjs';
import swal from 'sweetalert2'
import { map } from 'rxjs/operators/map';
import { Category } from 'src/app/api/models';
import { ModalDirective } from 'ngx-bootstrap/modal';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  selector: 'app-manage-categories',
  templateUrl: './manage-categories.component.html',
  styleUrls: ['./manage-categories.component.css']
})
export class ManageCategoriesComponent implements OnInit, AfterViewInit {
  @ViewChild('addModal') public addModal: ModalDirective;
  @ViewChild('editModal') public editModal: ModalDirective;

  public categoryCreate: Category;
  public categoryUpdate: Category;
  public categories = [];
  public categorieParent = [];

  //pagination
  public limitPage = 10;
  public currentPage = 1;
  public paginationTotal;
  public paginationCurrent;
  public paginationLast;
  public timeoutSearchCategory;

  //search
  public style_search: any = '';
  public key_input: any= '';

  constructor(
    private apiCategories: AdminCategoriesService,
    private translate: TranslateService,
    private spinner: NgxSpinnerService
  ) {
    this.categoryCreate = new Category();
    this.categoryUpdate = new Category();
  }

  ngOnInit() {}

  pageChanged(event: any): void {
    this.currentPage = event.page;
    this.getListCategories();
  }

  getListCategories(){

    this.spinner.show();
    this.apiCategories.listCategoryResponse({
      page: this.currentPage,
      limit: this.limitPage
    }).pipe(
      map(_r => {
        return _r;
      })
    ).subscribe((data) => {

      this.categories = data.body;
      this.spinner.hide();
      this.key_input = '';

      this.paginationTotal = data.headers.get('pagination-total');
      this.paginationCurrent = data.headers.get('pagination-current');
      this.paginationLast = data.headers.get('pagination-last');
    });
  }

  getDataCategoryByInput() {

    clearTimeout(this.timeoutSearchCategory);

    if (this.style_search == '') {
      swal(this.translate.instant('SWAL_ERROR'), this.translate.instant('SWAL_ERROR_CATEGORY_SEARCH_ACTIVED'), 'warning');
      return;
    }

    this.timeoutSearchCategory = setTimeout(() => {
      if (this.key_input !== '') {
        this.spinner.show();
        this.apiCategories.managerListCategoryByInputAndByTypeSearch({
          style_search: this.style_search,
          key_input: this.key_input
        }).pipe(
          map(_r => {
            return _r;
          })
        ).subscribe(data => {
          this.categories = data;
          this.spinner.hide();
        });
      } else {
        this.getListCategories();
      }
    }, 500);
  }

  ngAfterViewInit() { this.refreshView(); }

  refreshView() { this.getListCategories(); }

  showModalAdd() {

    this.categoryCreate.parent_id = 0;
    this.categoryCreate.type = 'manager';
    this.addModal.show();
  }

  addCategory() {

    if (!this.categoryCreate.key) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_KEY_PAGE'), 'warning');
    }

    if (!this.categoryCreate.display_name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_DISPLAY_NAME_PAGE'), 'warning');
    }

    this.apiCategories.createCategory({
      type: this.categoryCreate.type,
      display_name: this.categoryCreate.display_name,
      parent_id: this.categoryCreate.parent_id,
      description: this.categoryCreate.description,
      key: this.categoryCreate.key
    }
    ).subscribe((data) => {
      this.refreshView();
      this.addModal.hide();
      swal(this.translate.instant('SWAL_CREATE_SUCCESS'), '', 'success');
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

  showModalEdit(id: number) {
    this.apiCategories.getCategoryById(id).subscribe(
      (data) => {
        this.categoryUpdate.id = data.id;
        this.categoryUpdate.display_name = data.display_name;
        this.categoryUpdate.description = data.description;
        this.categoryUpdate.key = data.key;
        this.categoryUpdate.parent_id = data.parent_id;
        this.categoryUpdate.type = data.type;
        this.editModal.show();
      }
    );
  }

  editCategory() {

    if (!this.categoryUpdate.display_name) {
      swal(this.translate.instant('SWAL_WARN'), this.translate.instant('SWAL_ERROR_DISPLAY_NAME_PAGE'), 'warning');
    }

    this.apiCategories.updateCategory({
      id: this.categoryUpdate.id,
      display_name: this.categoryUpdate.display_name,
      key: this.categoryUpdate.key,
      type: this.categoryUpdate.type,
      description: this.categoryUpdate.description
    }).subscribe(
      (data) => {
        this.editModal.hide();
        this.refreshView();
        swal(this.translate.instant('SWAL_UPDATE_SUCCESS'), '', 'success');
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

  deleteCategory(id: number) {
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
        this.apiCategories.deleteCategory(id).subscribe(() => {
          this.refreshView();
          swal(this.translate.instant('SWAL_DEL_SUCCESS'), '', 'success');
        });
      }
    });
  }
}
