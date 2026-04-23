import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { 
  IonHeader, IonToolbar, IonTitle, IonContent, 
  IonButton, IonIcon 
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-coming-soon',
  templateUrl: './coming-soon.page.html',
  styleUrls: ['./coming-soon.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    IonHeader, IonToolbar, IonTitle, IonContent,
    IonButton, IonIcon
  ]
})
export class ComingSoonPage {
  userName: string = '';
  userRole: string = '';

  constructor(
    private router: Router,
    private authService: AuthService
  ) {
    const userData = this.authService.getUserData();
    if (userData) {
      this.userName = userData.name || 'Usuario';
      this.userRole = userData.role || '';
    }
  }

  cerrarSesion() {
    this.authService.logout().subscribe({
      next: () => {
        localStorage.clear();
        this.router.navigate(['/login']);
      },
      error: () => {
        localStorage.clear();
        this.router.navigate(['/login']);
      }
    });
  }
}
