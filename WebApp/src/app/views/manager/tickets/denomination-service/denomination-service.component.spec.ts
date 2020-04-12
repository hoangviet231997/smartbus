import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DenominationServiceComponent } from './denomination-service.component';

describe('DenominationServiceComponent', () => {
  let component: DenominationServiceComponent;
  let fixture: ComponentFixture<DenominationServiceComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DenominationServiceComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DenominationServiceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
