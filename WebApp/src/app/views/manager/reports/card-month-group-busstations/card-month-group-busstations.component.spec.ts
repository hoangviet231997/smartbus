import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CardMonthGroupBusstationsComponent } from './card-month-group-busstations.component';

describe('CardMonthGroupBusstationsComponent', () => {
  let component: CardMonthGroupBusstationsComponent;
  let fixture: ComponentFixture<CardMonthGroupBusstationsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CardMonthGroupBusstationsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CardMonthGroupBusstationsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
