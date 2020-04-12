import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DictatesTransportsComponent } from './dictates-transports.component';

describe('DictatesTransportsComponent', () => {
  let component: DictatesTransportsComponent;
  let fixture: ComponentFixture<DictatesTransportsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DictatesTransportsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DictatesTransportsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
