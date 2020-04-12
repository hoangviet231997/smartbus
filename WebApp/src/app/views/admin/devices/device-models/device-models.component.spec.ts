import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DeviceModelsComponent } from './device-models.component';

describe('DeviceModelsComponent', () => {
  let component: DeviceModelsComponent;
  let fixture: ComponentFixture<DeviceModelsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DeviceModelsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DeviceModelsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
