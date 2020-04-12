import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NotifyTypesComponent } from './notify-types.component';

describe('NotifyTypesComponent', () => {
  let component: NotifyTypesComponent;
  let fixture: ComponentFixture<NotifyTypesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NotifyTypesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NotifyTypesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
