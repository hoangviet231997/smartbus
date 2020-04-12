import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TransactionOnlinesComponent } from './transaction-onlines.component';

describe('TransactionOnlinesComponent', () => {
  let component: TransactionOnlinesComponent;
  let fixture: ComponentFixture<TransactionOnlinesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TransactionOnlinesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TransactionOnlinesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
