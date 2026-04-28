import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonIcon } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { listOutline } from 'ionicons/icons';

@Component({
  selector: 'app-pedidos',
  templateUrl: './pedidos.component.html',
  styleUrls: ['./pedidos.component.scss'],
  standalone: true,
  imports: [CommonModule, IonIcon]
})
export class PedidosComponent implements OnInit {
  constructor() {
    addIcons({ listOutline });
  }

  ngOnInit() {
    console.log('Componente Pedidos inicializado');
  }
}
