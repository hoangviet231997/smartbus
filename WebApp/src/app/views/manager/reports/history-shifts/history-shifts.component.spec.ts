import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { HistoryShiftsComponent } from './history-shifts.component';

describe('HistoryShiftsComponent', () => {
  let component: HistoryShiftsComponent;
  let fixture: ComponentFixture<HistoryShiftsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ HistoryShiftsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(HistoryShiftsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
