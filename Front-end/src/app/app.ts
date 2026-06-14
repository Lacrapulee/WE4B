import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common'; // Important pour le *ngIf
import { RouterModule, Router } from '@angular/router'; // Important pour le routerLink
import { AuthService } from './core/api/auth.service';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './app.html',
  styleUrls: ['./app.css'] // (Vérifie si c'est .css ou .scss chez toi)
})
export class AppComponent implements OnInit, OnDestroy {
  isLoggedIn: boolean = false; 
  userId: string | number | null = null;
  unreadMessages: number = 3;
  private authSub: Subscription | undefined;

  constructor(private authService: AuthService, private router: Router) {}

  ngOnInit() {
    this.authSub = this.authService.currentUser$.subscribe(state => {
      this.isLoggedIn = state.isLoggedIn;
      this.userId = state.user_id || null;
    });
  }

  ngOnDestroy() {
    if (this.authSub) {
      this.authSub.unsubscribe();
    }
  }

  onSearch(event: Event) {
    event.preventDefault();
    console.log('Recherche cliquée');
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => {
        console.log('Déconnexion cliquée');
        this.router.navigate(['/']);
      },
      error: (err) => console.error('Erreur lors de la déconnexion', err)
    });
  }
}