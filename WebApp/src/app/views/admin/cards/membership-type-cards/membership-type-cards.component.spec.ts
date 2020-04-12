import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MembershipTypeCardsComponent } from './membership-type-cards.component';

describe('MembershipTypeCardsComponent', () => {
  let component: MembershipTypeCardsComponent;
  let fixture: ComponentFixture<MembershipTypeCardsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MembershipTypeCardsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MembershipTypeCardsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
