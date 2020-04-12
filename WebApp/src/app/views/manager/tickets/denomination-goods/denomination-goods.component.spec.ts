import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DenominationGoodsComponent } from './denomination-goods.component';

describe('DenominationGoodsComponent', () => {
  let component: DenominationGoodsComponent;
  let fixture: ComponentFixture<DenominationGoodsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DenominationGoodsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DenominationGoodsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
