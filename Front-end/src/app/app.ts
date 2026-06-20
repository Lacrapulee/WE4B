import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { AuthService } from './core/api/auth.service';
import { CatalogueApiService } from './core/api/catalogue-api.service';
import { Subscription } from 'rxjs';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './app.html',
  styleUrls: ['./app.css']
})
export class AppComponent implements OnInit, OnDestroy {
  isLoggedIn: boolean = false;
  userId: string | number | null = null;
  isAdmin: boolean = false;
  unreadMessages: number = 3;
  unreadMessages: number = 0; // 👈 plus de valeur en dur
  searchQuery: string = '';
  private authSub: Subscription | undefined;

  constructor(
    private authService: AuthService,
    private router: Router,
    private api: CatalogueApiService // 👈 ajout
  ) { }

  ngOnInit() {
    this.authSub = this.authService.currentUser$.subscribe(state => {
      this.isLoggedIn = state.isLoggedIn;
      this.userId = state.user_id || null;
      this.isAdmin = !!state.is_admin;

      if (this.isLoggedIn) {
        this.loadUnreadCount();
      }
    });
  }

  loadUnreadCount() {
    this.api.getUnreadCount().subscribe({
      next: (count) => {
        this.unreadMessages = count;
      },
      error: (err) => {
        console.error('Erreur lors du chargement des messages non lus:', err);
      }
    });
  }

  ngOnDestroy() {
    if (this.authSub) {
      this.authSub.unsubscribe();
    }
  }

  onSearch(event: Event) {
    event.preventDefault();
    if (this.searchQuery.trim()) {
      this.router.navigate(['/catalogue'], { queryParams: { search: this.searchQuery.trim() } });
    } else {
      this.router.navigate(['/catalogue']);
    }
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => {
        this.router.navigate(['/']);
      },
      error: (err) => console.error('Erreur lors de la déconnexion', err)
    });
  }
}