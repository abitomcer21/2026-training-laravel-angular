import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonIcon } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { cashOutline } from 'ionicons/icons';

@Component({
  selector: 'app-caja',
  templateUrl: './caja.component.html',
  styleUrls: ['./caja.component.scss'],
  standalone: true,
  imports: [CommonModule, IonIcon]
})
export class CajaComponent implements OnInit {
  constructor() {
    addIcons({ cashOutline });
  }

  ngOnInit() {
    console.log('Componente Caja inicializado');
  }
}
