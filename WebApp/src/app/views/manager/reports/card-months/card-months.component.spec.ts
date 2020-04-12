import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CardMonthsComponent } from './card-months.component';

describe('CardMonthsComponent', () => {
  let component: CardMonthsComponent;
  let fixture: ComponentFixture<CardMonthsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CardMonthsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CardMonthsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
