import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CardExemptionComponent } from './card-exemption.component';

describe('CardExemptionComponent', () => {
  let component: CardExemptionComponent;
  let fixture: ComponentFixture<CardExemptionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CardExemptionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CardExemptionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
