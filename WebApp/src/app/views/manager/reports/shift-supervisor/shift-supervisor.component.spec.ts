import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ShiftSupervisorComponent } from './shift-supervisor.component';

describe('ShiftSupervisorComponent', () => {
  let component: ShiftSupervisorComponent;
  let fixture: ComponentFixture<ShiftSupervisorComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ShiftSupervisorComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ShiftSupervisorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
